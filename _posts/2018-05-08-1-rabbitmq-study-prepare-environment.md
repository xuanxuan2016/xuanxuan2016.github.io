---
layout:     post
title:      "RabbitMQ学习-环境准备"
subtitle:   "prepare-environment"
date:       2018-05-08 13:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍RabbitMQ及其依赖erlang的安装。

## 下载并安装erlang

#### 1.下载安装文件
可去[erlang官网](http://www.erlang.org/downloads)查看可用版本，这里安装最新版本。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ /]# cd /bmsource
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# wget http://erlang.org/download/otp_src_20.3.tar.gz
```

解压安装文件
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -xvf otp_src_20.3.tar.gz
```

#### 2.完成安装
将erlang安装到<code>/usr/local/erlang</code>目录。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cd otp_src_20.3
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# ./configure --prefix=/usr/local/erlang 
configure: error: No curses library functions found
configure: error: /bin/sh '/bmsource/otp_src_20.3/erts/configure' failed for erts
```

出现error信息，提示没有找到<code>curses</code>类，这里需要安装一下。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# yum install ncurses-devel
```

继续安装。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# ./configure --prefix=/usr/local/erlang 
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# make && make install
```

等待一段时间后，安装完成，可以在<code>/usr/local/erlang/</code>看到相关信息。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ otp_src_20.3]# ll /usr/local/erlang/
total 8
drwxr-xr-x. 2 root root 4096 May  8 07:05 bin
drwxr-xr-x. 3 root root 4096 May  8 07:04 lib
```

测试erlang是否可正常使用。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ otp_src_20.3]# /usr/local/erlang/bin/erl
Erlang/OTP 20 [erts-9.3] [source] [64-bit] [smp:1:1] [ds:1:1:10] [async-threads:10] [hipe] [kernel-poll:false]

Eshell V9.3  (abort with ^G)
1> 
```

#### 3.环境变量

为了以后可以方便使用，将erlang路径配置到环境变量中。修改<code>/etc/profile</code>文件，在最后加入如下配置，然后使配置生效。
<br>
<code>export ERLANG_HOME=/usr/local/erlang</code>
<br>
<code>export PATH=$ERLANG_HOME/bin:$PATH</code>

```linux
[root@iZwz9i8fd8lio2yh3oerizZ otp_src_20.3]# vim /etc/profile
[root@iZwz9i8fd8lio2yh3oerizZ otp_src_20.3]# source /etc/profile
```

可以直接通过<code>erl</code>进行使用。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ otp_src_20.3]# erl
erl   erlc  
[root@vagrant otp_src_20.3]# erl
Erlang/OTP 20 [erts-9.3] [source] [64-bit] [smp:1:1] [ds:1:1:10] [async-threads:10] [hipe] [kernel-poll:false]

Eshell V9.3  (abort with ^G)
1> 
```

## 下载并安装RabbitMQ

这里使用绿色包的方式来安装，如果需要通过rpm方式，可去[RabbitMQ官网](http://www.rabbitmq.com/install-rpm.html)查看。

#### 1.下载安装文件

可去[RabbitMQ的Git](https://github.com/rabbitmq/rabbitmq-server/releases)查询可用版本，这里使用最新版本。注意使用带<code>generic-unix</code>标识的。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# wget http://www.rabbitmq.com/releases/rabbitmq-server/v3.6.15/rabbitmq-server-generic-unix-3.6.15.tar.xz
```

解压文件，需要先使用<code>xz</code>，如果没有安装一下。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# yum install xz
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# xz -d rabbitmq-server-generic-unix-3.6.15.tar.xz
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -xvf rabbitmq-server-generic-unix-3.6.15.tar
```

#### 2.完成安装

将解压好的文件复制到<code>/usr/local</code>即可。

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cp -R rabbitmq_server-3.6.15/ /usr/local/rabbitmq
```

#### 3.环境变量

为了以后可以方便使用，将rabbitmq路径配置到环境变量中。修改<code>/etc/profile</code>文件，在最后加入如下配置，然后使配置生效。
<code>export PATH=/usr/local/rabbitmq/sbin:$PATH</code>

```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# vim /etc/profile
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# source /etc/profile
```

#### 4.工作文件夹

rabbitmq正常工作使用如下2个文件夹，通常在运行rabbitmq服务时，会自动创建出来，不需要手动创建。

##### 日志文件夹

用于存储rabbitmq的运行日志。

```linux
/usr/local/rabbitmq/var/log/rabbitmq
```

##### 数据文件夹

rabbitmq使用mnesia数据库存储服务器信息，如队列元数据，虚拟主机等。

```linux
/usr/local/rabbitmq/var/lib/rabbitmq/mnesia/节点名称
```
