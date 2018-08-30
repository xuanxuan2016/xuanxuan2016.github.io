---
layout:     post
title:      "mysql连接池"
subtitle:   "Mysql Connection Pool"
date:       2018-08-30 10:00
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - mysql
    - php
---

> 介绍在php中怎么实现连接池。

## 前言

<p>
php作为脚本语言在每次运行结束后会销毁所有状态，不能将状态常驻在内存中，从而就不能像java等常驻内存的语言一样，可以实现全功能的连接池。
</p>

<p>
这里利用swoole这种可以常驻内存的扩展来实现php的连接池。
</p>

## 处理流程

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-30-mysql-connection-pool/20180830183231.png?raw=true)

## 实现

#### 1.服务端

<p>
创建常驻内存运行的swoole服务，用于接收客户端的请求来执行数据操作。
</p>

- worker_num：接受外部数据操作请求的并发数
- task_worker_num：连接池中的可用连接数

```
/**
 * 准备服务
 */
protected function prepare() {
    //实例化对象
    //swoole_get_local_ip()获取本机ip
    $this->objServer = new swoole_server(Config::get('database.devmanager.connect_pool.host'), Config::get('database.devmanager.connect_pool.port'));
    //设置运行参数
    $this->objServer->set(array(
        'daemonize' => 1, //以守护进程执行
        'max_request' => 10000, //worker进程在处理完n次请求后结束运行
        'worker_num' => Config::get('database.devmanager.connect_pool.worker_num'),
        'task_worker_num' => Config::get('database.devmanager.connect_pool.task_num'),
        "task_ipc_mode " => 3, //使用消息队列通信，并设置为争抢模式,
        'heartbeat_check_interval' => 5, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
        'heartbeat_idle_time' => 10, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过则关闭
        'open_eof_split' => true,
        'package_eof' => "\r\n",
        "log_file" => $this->objApp->make('path.storage') . "\log\\" . Config::get('database.devmanager.connect_pool.log_file')
    ));
    //设置事件回调
    $this->objServer->on('Connect', array($this, 'onConnect'));
    $this->objServer->on('Receive', array($this, 'onReceive'));
    $this->objServer->on('Finish', array($this, 'onFinish'));
    $this->objServer->on('Task', array($this, 'onTask'));
    $this->objServer->on('WorkerStart', array($this, 'onWorkerStart'));
}

/**
 * 接收到数据时
 */
public function onReceive($objServer, $fd, $reactor_id, $strData) {
    //1.接受到业务数据操作，分配给空闲连接执行
    $mixResult = $objServer->taskwait($strData, 3);
    if ($mixResult === false) {
        $mixResult = json_encode(['success' => 0, 'result' => [], 'err_msg' => 'task timeout']);
    }
    $blnFlag = $objServer->send($fd, $mixResult);
    if (!$blnFlag) {
        //记录日志
    }
}

/**
 * 处理投递的任务
 */
public function onTask($objServer, $task_id, $src_worker_id, $strData) {
    //1.参数解析
    $strData = preg_replace('/\r\n/', '', $strData);
    $arrData = json_decode($strData, true);
    //2.执行数据操作
    $arrReturn = [];
    switch ($arrData['type']) {
        case 'select':
            $this->objDB->setMainTable($arrData['main_table']);
            $blnException = false;
            $arrTmp = $this->objDB->select($arrData['sql'], $arrData['param'], $arrData['sql'], $blnException);
            $arrReturn = ['success' => $blnException ? 0 : 1, 'result' => $arrTmp, 'err_msg' => ''];
            break;
        default:
            $arrReturn = ['success' => 0, 'result' => [], 'err_msg' => 'type类型错误'];
            break;
    }
    //数据返回
    return json_encode($arrReturn) . "\r\n";
}
```

#### 2.客户端

<p>
连接swoole服务进行数据操作的请求。
</p>

```
$objClient = new swoole_client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
//设置eof检测
$objClient->set([
    'open_eof_check' => true,
    'package_eof' => "\r\n"
]);
if ($objClient->connect('127.0.0.1', 9602) !== false) {
    $strtmp = json_encode(['main_table' => 'test', 'type' => 'select', 'sql' => "select * from test where 1=1", 'param' => []]);
    $arrTmp = $objClient->send($strtmp . "\r\n");
    var_dump(json_decode($objClient->recv(), true));
}
```

#### 3.结果

##### 数据库进程

<p>
此类中开启的连接池个数为2，所以可以看到进程始终保持在2个。
</p>

```
mysql> show full processlist;
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id  | User            | Host            | db         | Command | Time   | State                  | Info                  |
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|   4 | event_scheduler | localhost       | NULL       | Daemon  | 529101 | Waiting on empty queue | NULL                  |
|  83 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 309 | test            | 127.0.0.1:45384 | devmanager | Sleep   |     71 |                        | NULL                  |
| 310 | test            | 127.0.0.1:45386 | devmanager | Sleep   |     71 |                        | NULL                  |
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
4 rows in set (0.00 sec)
```

<p>
又执行了几次web请求，都是复用的现有连接。
</p>

```
mysql> show full processlist;
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id  | User            | Host            | db         | Command | Time   | State                  | Info                  |
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|   4 | event_scheduler | localhost       | NULL       | Daemon  | 529128 | Waiting on empty queue | NULL                  |
|  83 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 309 | test            | 127.0.0.1:45384 | devmanager | Sleep   |      3 |                        | NULL                  |
| 310 | test            | 127.0.0.1:45386 | devmanager | Sleep   |      3 |                        | NULL                  |
+-----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
4 rows in set (0.00 sec)
```

##### sql日志

<p>
当连接池中连接还没建立时，第一次建立连接，所花的时间为10几毫秒
</p>

```
Date:[2018-08-30 18:22:28]
ClientIP:[10.0.2.15]
ServerIP:[10.0.2.15]
Url:[mysql_connect_pool]
UserID:[]
Memo:[
 sql:select * from test where 1=1 
 param:[] 
 startdate:2018-08-30 18:22:28.089 
 enddate:2018-08-30 18:22:28.139 
]
```

<p>
当连接池中连接已经建立后，之后的sql操作可以直接使用连接，而不需要重新建立连接，所花的时间为几毫秒
</p>

```
----------------------------------------------------------------------
Date:[2018-08-30 18:24:22]
ClientIP:[10.0.2.15]
ServerIP:[10.0.2.15]
Url:[mysql_connect_pool]
UserID:[]
Memo:[
 sql:select * from test where 1=1 
 param:[] 
 startdate:2018-08-30 18:24:22.268 
 enddate:2018-08-30 18:24:22.268 
]
----------------------------------------------------------------------
Date:[2018-08-30 18:25:14]
ClientIP:[10.0.2.15]
ServerIP:[10.0.2.15]
Url:[mysql_connect_pool]
UserID:[]
Memo:[
 sql:select * from test where 1=1 
 param:[] 
 startdate:2018-08-30 18:25:14.338 
 enddate:2018-08-30 18:25:14.339 
]
```

## 结语

<p>
数据库的连接池在网站并发量超级大的时候（连接数万级以上），此时数据库就会有压力，可以考虑使用，少于这个量级一般长连接或者单例就可以满足业务需求了。
</p>

<p>
从直连数据库转变为通过swoole连接数据库，由于与swoole之间的通信也需要时间，所以总时间上可能会比直连消耗的多。
</p>


## 参考资料

[基于swoole扩展实现PHP数据库连接池](http://rango.swoole.com/archives/265)

[Swoole文档](https://wiki.swoole.com/wiki/)
