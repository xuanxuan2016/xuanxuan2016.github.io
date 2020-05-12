---
layout:     post
title:      "swoole的curl"
subtitle:   "swoole curl"
date:       2020-05-07 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - swoole
---

> 介绍swoole一键化协程下的curl实现及tcp的keep-alive。

## curl实现

#### swoole library

<p>
此类库用于实现一些第三方的功能，如curl。如果需要使用最新版本(未编译进swoole.so)的的library，可使用composer安装，然后在项目中使用。有如下一些步骤：
</p>

```
#composer安装最新版本
composer require swoole/library:dev-master
```

```
#设置不要使用swoole.so的library
#/etc/php.d/40-swoole.ini
swoole.enable_library = Off
```

```
#将代码复制到项目中，并配置psr4与psr4files
#psr4
return [
    'Swoole\\' => [
        0 => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/core'
    ],
];

#psr4files
return [
    '1352d79f309fb0ce5110f973736efe4c' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/constants.php',
    '13166588d5fd1ea7393bbaa4dcd57d4e' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/core/Coroutine/functions.php',
    '0059c50b1e50b395050e13cfed3c20fc' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/std/exec.php',
    '14ed4c120aa176bdf1729ce95bb05d5f' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/ext/curl.php',
    'e7a3ca0480b5d06810fa37c4b1414724' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/functions.php',
    '55b71bcfd58af22544c577eb0df5b9be' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/alias.php',
    '9014ab75675af477eb5f639cc3b56167' => BASE_PATH . '/framework/Service/Lib/SwooleLibrary/src/alias_ns.php',
];
```

<p>
核心代码为<code>\src\core\Curl\Handler.php</code>
</p>

#### HttpClient

<p>
curl的一键化协程是使用HttpClient实现的，默认为长连接。
</p>

```
#将curl配置为短连接
protected $arrConnectionParam = [
        'curl' => [
            CURLOPT_FORBID_REUSE => 1
        ]
    ]
];
```

<p>
当调用<code>curl_reset</code>时，底层重新实例化HttpClient，在用于长连接时需要注意，不能使用此方法，否则就会新建tcp连接了。在复用连接时，同时需要确保后面的请求不会被前面请求的参数影响，需要清理option。
</p>

## 项目使用

#### php-elastic

<p>
此库是es官方提供的用于php与es进行交互的库，在swoole项目中使用的时候，为了能利用到curl的长连接功能，在使用时需要注意一些事情。
</p>

##### ClientBuilder实例化

```
/**
 * 设置句柄
 * 1.为了能利用到curl的长连接，selector使用StickyRoundRobinSelector(持续使用某个连接，直到连接失效)，要不然connect会变换，导致curl底层重新实例化HttpClient
 * 2.为了能均衡的使用所有服务器，在传入服务器可用连接时，打乱顺序
 */
protected function setHander() {
    //1.是否能连接服务器
    $arrServer = Config::get('elasticsearch.server');
    shuffle($arrServer);
    try {
        $objClient = ClientBuilder::create()
                ->setHosts($arrServer)
                ->setConnectionParams($this->getConnectionParam())
                ->setRetries(Config::get('elasticsearch.try_count'))
                ->setHandler($this->objCurlHandler = ClientBuilder::singleHandler())
                ->setConnectionPool(SimpleConnectionPool::class)
                ->setSelector(new StickyRoundRobinSelector())
                ->build();

        //发送【head /test】
        if ($objClient->ping()) {
            $this->objHandler = $objClient;
        }
    } catch (Throwable $e) {
        $strLog = sprintf('es连接失败:%s,error:%s', json_encode($arrServer), $e->getMessage());
        Log::log($strLog, Config::get('const.Log.LOG_ESERR'));
    }

    //2.检查是否能连接到写服务器
    if (is_null($this->objHandler)) {
        Log::log(sprintf('es句柄初始化失败'), Config::get('const.Log.LOG_ESERR'));
    }
}
```

##### CurlHandler类

```
/**
 * 释放一个curl句柄
 * @param $handle
 */
private function releaseEasyHandle($handle) {
    if (getCid() > 0) {
        $id = $handle->id;
    } else {
        $id = (int)$handle;
    }
    if (count($this->ownedHandles) > $this->maxHandles) {
        //缓存句柄超过最大数量，释放句柄
        curl_close($this->handles[$id]);
        unset($this->handles[$id], $this->ownedHandles[$id]);
    } else {
        //curl_reset doesn't clear these out for some reason
        //重置句柄选项，可用于之后使用
        static $unsetValues = [
            CURLOPT_HEADERFUNCTION => null,
            CURLOPT_WRITEFUNCTION => null,
            CURLOPT_READFUNCTION => null,
            CURLOPT_PROGRESSFUNCTION => null,
        ];
        curl_setopt_array($handle, $unsetValues);
        $this->optreset($handle);
        //curl_reset($handle);
        $this->ownedHandles[$id] = false;
    }
}

/**
 * 用于curl长连接的选项重置(如果使用curl_reset会导致HttpClient重新创建)
 * 注意：这里需要确保不同的es方法在HttpClient中的requestMethod,requestHeaders,requestBody属性是预期的
 */
private function optreset($handle) {
    curl_setopt($handle, CURLOPT_POSTFIELDS, null);
}
```

## TCP Keep-Alive

#### linux keepalive设置

<p>
当客户端与服务器建立tcp连接后，可以通过keepalive控制客户端与服务端交互结束后不立即关闭连接。
</p>

<p>
当超过<code>tcp_keepalive_time</code>无交互后，服务器每隔<code>tcp_keepalive_intvl</code>会给客户端发送信号，如果超过<code>tcp_keepalive_probes</code>次客户端无回应则服务端关闭连接。
</p>

<p>
如果客户端有回应，则服务端会一直检测，当客户端与服务端再次发生交互后，又会重新回到上步的操作。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2020-05-07-swoole-curl/tu_1.png?raw=true)

```
#/etc/sysctl.conf

#如果在120秒内没有任何数据交互,则进行探测(缺省值:7200s)
net.ipv4.tcp_keepalive_time = 120
#探测时发探测包的时间间隔为20秒(缺省值:75s)
net.ipv4.tcp_keepalive_intvl = 20
#探测重试的次数. 全部超时则认定连接失效(缺省值:9次)
net.ipv4.tcp_keepalive_probes = 3

#刷新配置
sysctl -p
```

#### socket keepalive设置

<p>
实际使用时，可针对不同的socket进行单独设置。
</p>

```
#c语言
int keepAlive = 1;   // 开启keepalive属性. 缺省值: 0(关闭)    
int keepIdle = 60;   // 如果在60秒内没有任何数据交互,则进行探测. 缺省值:7200(s)
int keepInterval = 5;   // 探测时发探测包的时间间隔为5秒. 缺省值:75(s)    
int keepCount = 2;   // 探测重试的次数. 全部超时则认定连接失效..缺省值:9(次)    

setsockopt(s, SOL_SOCKET, SO_KEEPALIVE, (void*)&keepAlive, sizeof(keepAlive));    
setsockopt(s, SOL_TCP, TCP_KEEPIDLE, (void*)&keepIdle, sizeof(keepIdle));    
setsockopt(s, SOL_TCP, TCP_KEEPINTVL, (void*)&keepInterval, sizeof(keepInterval));    
setsockopt(s, SOL_TCP, TCP_KEEPCNT, (void*)&keepCount, sizeof(keepCount));  
```

#### redis设置tcp-keepalive

<p>
如果要修改redis服务器的tcp-keepalive值，可在配置中修改
</p>

```
CONFIG set tcp-keepalive 500
```


## 参考资料

[swoole library](https://github.com/swoole/library)

[elasticsearch-php](https://github.com/elastic/elasticsearch-php)

[linux下netstat --timers / -o详解及keepalive相关](https://blog.51cto.com/zhengmingjing/1887920)

[协程 HTTP/WebSocket 客户端](https://wiki.swoole.com/#/coroutine_client/http_client)