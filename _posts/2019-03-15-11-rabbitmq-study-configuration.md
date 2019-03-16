---
layout:     post
title:      "RabbitMQ学习-配置"
subtitle:   "prepare-environment"
date:       2019-03-15 15:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍RabbitMQ中的参数配置。

## 概要

<p>
rabbitmq在启动时会使用很多默认的配置，这些配置一般在开发或测试环境是可以的。当在线上环境时，某些配置可能需要根据实际情况进行调整。
</p>

## 环境变量

<p>
rabbitmq的环境变量可用于配置某些服务器参数：节点名称，rabbitmq配置文件位置，节点间通信端口，ErlangVM标志等。rabbitmq的环境变量除了内置的，还可通过shell或rabbitmq-env.conf进行设置。
</p>

<p style="color:red;">
Tips：变量优先级为，shell>rabbitmq-env.conf>内置
</p>

#### shell环境变量

```linux
#局部设置
[root@DEV-HROEx64 mnesia]# RABBITMQ_NODE_PORT=5674 rabbitmq-server -detached
```

```linux
#全局直接设置
[root@DEV-HROEx64 mnesia]# export RABBITMQ_NODE_PORT=5674
#移除设置
[root@DEV-HROEx64 mnesia]# export -n RABBITMQ_NODE_PORT
```

```linux
#全局文件设置
[root@DEV-HROEx64 mnesia]# vim /etc/profile
[root@DEV-HROEx64 mnesia]# 写入配置保存
[root@DEV-HROEx64 mnesia]# source /etc/profile
```

<p style="color:red;">
Tips：变量名需要携带<code>RABBITMQ_</code>前缀。
</p>

#### rabbitmq-env配置文件

<p>
配置文件的位置一般固定为<code>${install-prefix}/etc/rabbitmq/rabbitmq-env.conf</code>，如果文件不存在，可手动创建。
</p>

<p style="color:red;">
Tips：区别于shell的设置，文件中的变量不需要携带<code>RABBITMQ_</code>前缀。
</p>

#### 可设置变量

<p>
文件及目录相关的配置，<code>$RABBITMQ_HOME</code>为解压的rabbitmq目录。
</p>

名称 | 默认值/说明 
---|---
RABBITMQ_CONFIG_FILE | $RABBITMQ_HOME/etc/rabbitmq/rabbitmq.conf <br> 配置文件路径 
RABBITMQ_MNESIA_BASE | $RABBITMQ_HOME/var/lib/rabbitmq/mnesia <br> mnesia数据库路径 
RABBITMQ_MNESIA_DIR | $RABBITMQ_MNESIA_BASE/$RABBITMQ_NODENAME <br> 节点数据路径
RABBITMQ_LOG_BASE | $RABBITMQ_HOME/var/log/rabbitmq <br> 日志路径
RABBITMQ_LOGS | $RABBITMQ_LOG_BASE/$RABBITMQ_NODENAME.log <br> 节点日志路径
RABBITMQ_SASL_LOGS | $RABBITMQ_LOG_BASE/$RABBITMQ_NODENAME-sasl.log 
RABBITMQ_PLUGINS_DIR | $RABBITMQ_HOME/plugins <br> 可用组件路径
RABBITMQ_ENABLED_PLUGINS_FILE | $RABBITMQ_HOME/etc/rabbitmq/enabled_plugins <br> 已启用组件
RABBITMQ_PID_FILE | $RABBITMQ_MNESIA_DIR.pid <br> pid文件

<p>
其他配置
</p>

名称 | 默认值/说明 
---|---
RABBITMQ_NODE_IP_ADDRESS | 空，绑定到所有网络接口 <br> 需要绑定到的网络接口 
RABBITMQ_NODE_PORT | 5672 <br> 服务端口 
RABBITMQ_DIST_PORT | RABBITMQ_NODE_PORT + 20000 <br> 节点间和CLI工具通信的端口
RABBITMQ_DISTRIBUTION_BUFFER_SIZE | 128000 <br> 节点间通信连接的传出数据缓冲区大小(千字节)限制，不要低于64M
RABBITMQ_IO_THREAD_POOL_SIZE | 128 <br> 用于I / O的线程数，不要使用低于32
RABBITMQ_NODENAME | rabbit@$HOSTNAME <br> 节点名称，不同节点名称需唯一
RABBITMQ_CONFIG_FILE | $RABBITMQ_HOME/etc/rabbitmq/rabbitmq <br> 主配置文件路径
RABBITMQ_ADVANCED_CONFIG_FILE | $RABBITMQ_HOME/etc/rabbitmq/advanced <br> "高级"配置文件路径
RABBITMQ_CONF_ENV_FILE | $RABBITMQ_HOME/etc/rabbitmq/rabbitmq-env.conf <br> 环境变量配置路径

## 配置文件

<p>
虽然RabbitMQ中的某些设置可以使用环境变量进行配置，但大多数设置都是使用配置文件配置的，通常名为rabbitmq.conf，包括核心服务器和插件的配置。
</p>

#### 配置文件位置

<p>
根据不同的安装方式，配置文件一般在<code>/etc/rabbitmq/</code>或<code>{rabbit_install_dir}/etc/rabbitmq/</code>，如果不存在的话可手动创建。
</p>

<p>
在rabbitmq启动时，可在日志文件的顶部查看加载的配置文件路径。
</p>

```linux
Starting RabbitMQ 3.7.13 on Erlang 20.3
 Copyright (C) 2007-2019 Pivotal Software, Inc.
 Licensed under the MPL.  See http://www.rabbitmq.com/
2019-03-16 13:57:13.007 [info] <0.256.0> 
 node           : rabbit@vagrant
 home dir       : /root
 config file(s) : /usr/local/rabbitmq/etc/rabbitmq/rabbitmq.conf
 cookie hash    : OIhbODu2Q0A6XyOqVBfFrA==
 log(s)         : /usr/local/rabbitmq/var/log/rabbitmq/rabbit@vagrant.log
                : /usr/local/rabbitmq/var/log/rabbitmq/rabbit@vagrant_upgrade.log
 database dir   : /usr/local/rabbitmq/var/lib/rabbitmq/mnesia/rabbit@vagrant
```

<p>
如果开启web管理页面的话，也可以在节点的信息中查看配置文件路径。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-03-15-11-rabbitmq-study-configuration/20190316140308.png?raw=true)

#### 配置文件格式

<p>
在RabbitMQ 3.7.0之前，RabbitMQ配置文件使用<a href="http://erlang.org/doc/man/config.html" traget="_blank">Erlang术语</a>配置格式，新版本仍然支持该格式以实现向后兼容性。不过建议运行3.7.0或更高版本的用户考虑新的sysctl格式。
</p>

<p>
老格式配置示例
</p>

```linux
[
  {rabbit, [{ssl_options, [{cacertfile,           "/path/to/testca/cacert.pem"},
                           {certfile,             "/path/to/server_certificate.pem"},
                           {keyfile,              "/path/to/server_key.pem"},
                           {verify,               verify_peer},
                           {fail_if_no_peer_cert, true}]}]}
]
```

<p>
新格式配置示例
</p>

```linux
ssl_options.cacertfile           = /path/to/testca/cacert.pem
ssl_options.certfile             = /path/to/server_certificate.pem
ssl_options.keyfile              = /path/to/server_key.pem
ssl_options.verify               = verify_peer
ssl_options.fail_if_no_peer_cert = true
```

<p>
新格式虽然易于理解与编辑，但是如果需要使用深层嵌套的数据结构来表达配置时，还是需要使用老格式的方式，如<a href="https://www.rabbitmq.com/ldap.html" traget="_blank">LDAP功能</a>。
</p>

#### 配置查看

<p>
可使用<code>rabbitmqctl environment </code>命令显示当前的有效配置，配置为用户设置的与系统默认配置的合并结果。
</p>

#### 可设置变量

[rabbitmq.conf配置示例](https://github.com/rabbitmq/rabbitmq-server/blob/master/docs/rabbitmq.conf.example)

[advanced.config配置示例](https://github.com/rabbitmq/rabbitmq-server/blob/master/docs/advanced.config.example)

## 参考资料

[配置](https://www.rabbitmq.com/configure.html)

[环境变量配置](https://www.rabbitmq.com/rabbitmq-env.conf.5.html)

[文件及目录配置](https://www.rabbitmq.com/relocate.html)
