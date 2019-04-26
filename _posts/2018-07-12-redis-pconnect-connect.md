---
layout:     post
title:      "redis中pconnect与connect"
subtitle:   "Pconnect Connect"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - redis
---

> 介绍phpredis模块中，pconnect与connect连接的区别。

## 概述

#### 1.connect

<p>
每次connect都会新建一个tcp连接；脚本运行结束之后自动断开连接。
</p>

#### 2.pconnect

<p>
持久连接，标识id：<br>
1.host + port + timeout<br>
2.host + persistent_id<br>
3.unix socket + timeout
</p>

<p>
每次pconnect会根据<code>标识id</code>在当前运行的httpd（指通过mpm模块创建出来的进程，web请求会被发送到这些进行进程处理，这些进程可处理多次web请求）或php-fpm进程中查找已存在的连接，如果存在则复用连接，否则新建连接；脚本运行结束之后不会自动断开连接，连接会保留在httpd或php-fpm进程中，除非进程关闭或连接空闲时间达到redis设置的timeout（timeout=0为永不超时）。
</p>

<p>
此特性在线程版本里是无效的，pconnect等同于connect。
</p>

## 测试准备

<p>
为了在测试时可以比较方便的观察连接，将apache的并发处理数调整为1。
</p>

```
httpd.conf
#启用httpd-mpm模块
Include conf/extra/httpd-mpm.conf
```

```
httpd-mpm.conf
<IfModule mpm_prefork_module>
    StartServers             1
    MinSpareServers          1
    MaxSpareServers         1
    MaxRequestWorkers      1
    MaxConnectionsPerChild   0
</IfModule>
```


## 单次执行多连接测试

#### 1.connect

```
$objRedis = new Redis();

$bln = $objRedis->connect('10.100.3.106', 6379, 3);
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->connect('10.100.3.106', 6379, 3);
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->connect('10.100.3.106', 6379, 3);
var_dump($objRedis->ping());
sleep(10);
```

<p>
redis客户端连接信息如下，可以发现3次连接的端口都是不同的，当重新连接时原来的连接会立即断开。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712172317.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712172356.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712172449.png?raw=true)

#### 2.pconnect

##### 1.相同的标识id

```
$objRedis = new Redis();
$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha');
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha');
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha');
var_dump($objRedis->ping());
sleep(10);
```

<p>
redis客户端连接信息如下，可以发现3次连接的端口都是相同的，脚本执行过程中，age在一直增长，idle会在使用后重新从0开始计算。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712173124.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712173540.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712173712.png?raw=true)

##### 2.不同的标识id

```
$objRedis = new Redis();
$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha1');
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha2');
var_dump($objRedis->ping());
sleep(10);

$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha3');
var_dump($objRedis->ping());
sleep(10);
```

<p>
redis客户端连接信息如下，可以发现3次连接的端口都是不同的，每次连接时原来的连接不会立即断开，而是要等到timeout时间。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712174540.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712174643.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180712174734.png?raw=true)

## 多次执行单连接测试

#### 1.connect

```
$objRedis = new Redis();

$bln = $objRedis->connect('10.100.3.106', 6379, 3);
var_dump($objRedis->ping());
```

<p>
redis客户端连接信息如下，可以发现3次执行脚本端口都是不同的，每次都会重新创建连接。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713172954.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713173006.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713173018.png?raw=true)

#### 2.pconnect

```
$objRedis = new Redis();
$bln = $objRedis->pconnect('10.100.3.106', 6379, 3, 'haha');
var_dump($objRedis->ping());
```

<p>
redis客户端连接信息如下，可以发现3次执行脚本端口都是相同的，每次执行后idle会更新为0，age（连接创建的时间）则会一直增长。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713155322.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713155417.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-12-redis-pconnect-connect/20180713155524.png?raw=true)

## 总结

#### 1.connect

<p>优点：</p>

- 脚本执行结束会自动关闭连接，无需关心是否会持续占用连接
- 每次连接都是新的连接，连接之间不会互相影响

<p>缺点：</p>

- 在大量请求时会频繁的建立与关闭连接，占用资源，出现很多TIME_WAIT



<p>适用场景：</p>



#### 2.pconnect

<p>优点：</p>

- 连接不会随着脚本的结束而关闭，会保留在httpd或php-fpm进程中，直到超时时间或进程关闭
- 每次连接时，会优先查找是否有可用连接，如果有则复用以前的连接，减少IO开销

<p>缺点：</p>

- 如果存在很多长连接，当超过redis的最大可用连接，会导致之后的连接连接不上
- 如果不同的web应用都使用长连接，且没有根据应用配置<code>标识id</code>，会导致2个web应用共享连接，当出现如切换db时就会产生问题


<p>适用场景：</p>

- 高并发的http请求，可设置<code>timeout</code>来及时关闭空闲的连接


## 参考资料

- [高并发下PHP请求Redis异常处理](https://blog.csdn.net/u013474436/article/details/53117463)
- [Apache的三种MPM模式比较：prefork，worker，event](http://blog.jobbole.com/91920/)
- [Apache优化：修改最大并发连接数](https://www.cnblogs.com/fazo/p/5588644.html)
