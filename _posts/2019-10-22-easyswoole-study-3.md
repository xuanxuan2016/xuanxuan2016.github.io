---
layout:     post
title:      "EasySwoole学习（三）"
subtitle:   "协程redis"
date:       2019-10-22 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - easy-swoole
---

## 概述

<p>
easyswoole的redis主要使用了<code>Swoole\Coroutine\Client（协程客户端）</code>通过tcp请求来实现与redis服务器的交互。
</p>

## 类关系

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-10-22-easyswoole-study-3/tu_1.png?raw=true)

## Client解析

#### 命令格式

<p>
通过<code>strace</code>监控redis的服务请求可获取到如下的交互信息。
</p>

```
#redis客户端执行命令
127.0.0.1:6379> get test
"1571640930"

#跟踪的日志
epoll_wait(5, {{EPOLLIN, {u32=13, u64=13}}}, 10128, 9) = 1
read(13, "*2\r\n$3\r\nget\r\n$4\r\ntest\r\n", 16384) = 23
read(3, 0x7ffdc282e28f, 1)              = -1 EAGAIN (Resource temporarily unavailable)
write(13, "$10\r\n1571640930\r\n", 17)  = 17
epoll_wait(5, {}, 10128, 8)             = 0
```

- \r\n：数据包分隔符
- *：命令的参数数量
- $：单个参数的长度

<p>
消息回复说明：
</p>

- +：单行回复
- -：错误消息
- $：批量回复，需要根据长度持续resv
- *：多个批量回复，循环进行【$】的操作

#### 代码示例

<p>
通过Client来模拟redis客户端请求。
</p>

>需要使用swoole4.4.0之后的版本，要不然可能会有问题。

```
go(function() {
    $client = new Swoole\Coroutine\Client(SWOOLE_TCP);
    $client->set([
        'open_eof_check' => true,
        'package_eof' => "\r\n",
    ]);
    if (!$client->connect('10.100.3.106', 6379, 3)) {
        echo $client->errMsg;
    } else {
        //命令需要为双引号，根据相应的规则拼接成字符串
        //命令需要根据规则
        var_dump($client->send("*1\r\n$4\r\nPING\r\n"));
        //+PONG\r\n
        $str = $client->recv(3);
        if (empty($str)) {
            var_dump($client->errCode);
            var_dump($client->errMsg);
        } else {
            echo strlen($str) ."\n";
            echo $str;
        }
    }
});
```

## 调用流程

<p>
如下流程图描述了<code>get</code>命令的主要调用过程。
</p>


![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-10-22-easyswoole-study-3/tu_2.png?raw=true)

## 参考资料

[easy swoole redis](http://www.easyswoole.com/Components/Redis/introduction.html)

[Swoole\Coroutine\Client](https://wiki.swoole.com/wiki/page/p-coroutine_client.html)

[注释版redis代码](https://github.com/xuanxuan2016/easyswoole1/tree/master/vendor/easyswoole/redis)