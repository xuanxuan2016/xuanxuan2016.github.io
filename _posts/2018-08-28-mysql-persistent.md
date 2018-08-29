---
layout:     post
title:      "mysql持久化连接"
subtitle:   "Mysql Persistent"
date:       2018-08-28 16:20
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - mysql
    - php
---

> 介绍在持久化连接的特性。

## 概述

<p>
短连接：每次web请求需要与数据库进行交互时，都重新建立数据库连接，从而需要进行3次握手，请求结束关闭连接时也会3/4次的网络通信。可能会增加一定的延时与额外的IO消耗。
</p>

<p>
长连接：每次web请求需要与数据库进行交互时，如果进程可以复用已存在的连接则直接使用，避免重新建立新的连接，节省IO的消耗。
</p>

## 实现与使用

#### 1.实现方式

<p>
php的长连接是搭载在apache这样的带有mpm模块的webserver，linux下apache会维护一个进程池，开启了apache mpm功能之后，apache会默认维持一个进程池，mysql长连接之后的连接，并没有作为socet连接关闭，而是作为一个不释放的东西，放进了进程池/线程池里面去，等需要连接的时，apache从它维护的进程池/线程池里面取出mysql  socket connnection, 然后就可以复用此连接了。
</p>

#### 2.适用场景

<p>
不适用：
</p>

- cli：脚本执行完，连接就会直接释放
- apache+mpm（不开启）：请求结束，连接就会直接释放
- nginx+php-fpm：大部分情况不支持（没测试过）

<p>
适用：
</p>

- apache+mpm（开启）：请求结束，连接不会直接释放，除非到超时时间
- apache+mpm（不开启）：请求结束，连接就会直接释放
- 常驻内存进程（如swoole）：因为是常驻服务，只要建立连接就会存在（除非超时被关闭），变相的实现了长连接


## web请求测试

#### 1.测试准备

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

#### 2.长连接

##### 连接代码

```
$this->objPdoRead = new PDO($strDsn, 'username', 'password', [PDO::ATTR_TIMEOUT => 3, PDO::ATTR_PERSISTENT => true]);
```

##### 端口查看

```
[root@vagrant ~]# netstat -anp|grep 3306
```

<p>
当执行多次web请求时，端口始终为一个。
</p>

```
[root@vagrant ~]# netstat -anp|grep 3306
tcp        1      0 127.0.0.1:40840             127.0.0.1:3306              CLOSE_WAIT  5539/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40844             127.0.0.1:3306              ESTABLISHED 5539/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40844      ESTABLISHED 2714/mysqld         
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40844             127.0.0.1:3306              ESTABLISHED 5539/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40844      ESTABLISHED 2714/mysqld         
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40844             127.0.0.1:3306              ESTABLISHED 5539/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40844      ESTABLISHED 2714/mysqld    
```

##### 进程查看

<p>
当执行多次web请求时，连接进程为同一个，进程空闲时间会进行刷新。
</p>

```
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 354374 | Waiting on empty queue | NULL                  |
| 41 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 57 | test            | 127.0.0.1:40844 | devmanager | Sleep   |      4 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)

mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 354384 | Waiting on empty queue | NULL                  |
| 41 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 57 | test            | 127.0.0.1:40844 | devmanager | Sleep   |      2 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)

mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 354388 | Waiting on empty queue | NULL                  |
| 41 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 57 | test            | 127.0.0.1:40844 | devmanager | Sleep   |      1 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
```

##### tcp包查看

```
[root@vagrant ~]# tcpdump -s 0 -l -w - dst 127.0.0.1 and port 3306 -i any -w /www/htdocs/Interview/logs/mysql.cap
```

<p>
当执行多次web请求时，只有第一次进行了3次tcp握手，之后不需要。
</p>

[长连接.cap](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-28-mysql-persistent/%E9%95%BF%E8%BF%9E%E6%8E%A5.cap)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-28-mysql-persistent/20180828183529.png?raw=true)


#### 3.短连接

##### 连接代码

```
$this->objPdoRead = new PDO($strDsn, 'username', 'password', [PDO::ATTR_TIMEOUT => 3]);
```

##### 端口查看

```
[root@vagrant ~]# netstat -anp|grep 3306
```

<p>
当执行多次web请求时，端口每次都是不同的。
</p>

```
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40858             127.0.0.1:3306              ESTABLISHED 5833/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40858      ESTABLISHED 2714/mysqld         
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40859             127.0.0.1:3306              ESTABLISHED 5833/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40858      TIME_WAIT   -                   
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40859      ESTABLISHED 2714/mysqld         
[root@vagrant ~]# netstat -anp|grep 3306
tcp        0      0 127.0.0.1:40860             127.0.0.1:3306              ESTABLISHED 5833/httpd          
tcp        0      0 :::33060                    :::*                        LISTEN      2714/mysqld         
tcp        0      0 :::3306                     :::*                        LISTEN      2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40860      ESTABLISHED 2714/mysqld         
tcp        0      0 ::ffff:127.0.0.1:3306       ::ffff:127.0.0.1:40859      TIME_WAIT   -                   
[root@vagrant ~]# 
```

##### 进程查看

<p>
当执行多次web请求时，连接进程每次都不相同。
</p>

```
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 412839 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 62 | test            | 127.0.0.1:40855 | devmanager | Sleep   |      3 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 412856 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 63 | test            | 127.0.0.1:40856 | devmanager | Sleep   |      1 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 412876 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 64 | test            | 127.0.0.1:40857 | devmanager | Sleep   |      2 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
```

##### tcp包查看

```
[root@vagrant ~]# tcpdump -s 0 -l -w - dst 127.0.0.1 and port 3306 -i any -w /www/htdocs/Interview/logs/mysql.cap
```

<p>
当执行多次web请求时，每次都进行了3次tcp握手。
</p>

[短连接.cap](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-28-mysql-persistent/%E7%9F%AD%E8%BF%9E%E6%8E%A5.cap)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-28-mysql-persistent/20180828183644.png?raw=true)

#### 4.长连接与短连接混用

<p>
1.使用长连接连接一次，查看进程状态，id=68为长连接。
</p>

```
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 413692 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 68 | test            | 127.0.0.1:40861 | devmanager | Sleep   |     27 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
```

<p>
2.使用短连接连接一次，查看进程状态，id=69为短连接，<code>没有能够复用长连接</code>。
</p>

```
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 413701 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 68 | test            | 127.0.0.1:40861 | devmanager | Sleep   |     36 |                        | NULL                  |
| 69 | test            | 127.0.0.1:40862 | devmanager | Sleep   |      7 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
4 rows in set (0.00 sec)
```

<p>
3.使用长连接连接一次，查看进程状态，id=68为短连接，<code>复用了长连接</code>。
</p>

```
mysql> show full processlist;
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
| Id | User            | Host            | db         | Command | Time   | State                  | Info                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
|  4 | event_scheduler | localhost       | NULL       | Daemon  | 413720 | Waiting on empty queue | NULL                  |
| 58 | root            | localhost       | NULL       | Query   |      0 | starting               | show full processlist |
| 68 | test            | 127.0.0.1:40861 | devmanager | Sleep   |      5 |                        | NULL                  |
+----+-----------------+-----------------+------------+---------+--------+------------------------+-----------------------+
3 rows in set (0.00 sec)
```

<p>
综上，如果项目中长连接与短连接混合用，短连接不能复用长连接建立的连接，而长连接可以。
</p>

## 总结

<p>
长连接的优缺点：
</p>

- **优点：**
- 1.复用连接，减少了连接阶段的IO消耗
- 2.减少了TIME_WAIT数量
- **缺点：**
- 1.当长连接占满服务器的最大连接时，会导致新连接连接不上
- 2.长连接的维护需要依赖web服务器，在使用前需要确保环境支持

<p>
短连接的优缺点：
</p>

- **优点：**
- 1.每次连接使用后会关闭连接，不会一直占用
- 2.使用场景不依赖于服务器环境
- **缺点：**
- 1.每次都是新建连接，额外消耗一些IO与时间
- 2.频繁的连接会产生较多的TIME_WAIT

<p>
<font color="red">Tips：以下只是参考情况，实际应用需要综合多方面的考虑。</font>
</p>

<p>
一般应用（日pv=百万级，连接数不多）：短连接+数据库单例
</p>

<p>
中型应用（日pv=千万级，连接数较多）：长连接+数据库单例
</p>

<p>
超大应用（日pv>千万级，连接数超多）：连接池+数据库单例
</p>

## 参考资料

[连接与连接管理](http://php.net/manual/zh/pdo.connections.php)

[MySQL的连接池、异步、断线重连](https://wiki.swoole.com/wiki/page/350.html)

[mysql 关于php mysql长连接、连接池的一些探索](https://blog.csdn.net/cominglately/article/details/77879599)
