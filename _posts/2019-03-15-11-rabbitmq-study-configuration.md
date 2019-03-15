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

> 介绍RabbitMQ中怎么来实现自定义的配置。

## 概要

<p>
rabbitmq在启动时会使用很多默认的配置，这些配置一般在开发或测试环境是可以的。当在线上环境时，某些配置可能需要根据实际情况进行调整。
</p>

## 环境变量

<p>
rabbitmq的环境变量可用于配置一些常规的参数，对于节点上更深的配置，还是需要在配置文件（rabbitmq.conf）中进行。rabbitmq的环境变量除了内置的，还可通过shell或rabbitmq-env.conf进行设置。
</p>

<p style="color:red;">
Tips：变量优先级为，shell>rabbitmq-env.conf>内置
</p>

#### 1.shell环境变量

```linux
#直接设置
[root@DEV-HROEx64 mnesia]# export RABBITMQ_NODE_PORT=5674
#移除设置
[root@DEV-HROEx64 mnesia]# export -n RABBITMQ_NODE_PORT
```

```linux
#/etc/profile
[root@DEV-HROEx64 mnesia]# vim /etc/profile
[root@DEV-HROEx64 mnesia]# 写入配置保存
[root@DEV-HROEx64 mnesia]# source /etc/profile
```

<p style="color:red;">
Tips：变量名需要携带<code>RABBITMQ_</code>前缀。
</p>

#### 2.rabbitmq-env配置文件

<p>
配置文件的位置一般固定为<code>${install-prefix}/etc/rabbitmq/rabbitmq-env.conf</code>，如果文件不存在，可手动创建。
</p>

<p style="color:red;">
Tips：区别于shell的设置，文件中的变量不需要携带<code>RABBITMQ_</code>前缀。
</p>

#### 3.可设置变量

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

名称 | 默认值 | 说明
---|---|---
RABBITMQ_CONFIG_FILE | $RABBITMQ_HOME/etc/rabbitmq/rabbitmq | 配置文件路径
RABBITMQ_MNESIA_BASE | $RABBITMQ_HOME/var/lib/rabbitmq/mnesia |
RABBITMQ_LOG_BASE||
RABBITMQ_NODENAME||
RABBITMQ_NODE_IP_ADDRESS||
RABBITMQ_NODE_PORT||
||
||
||



## 配置

##### 1.新建用户

```linux
[root@DEV-HROEx64 mnesia]# rabbitmqctl add_user user_test 123456
```

##### 2.关联用户到虚拟主机

```linux
[root@DEV-HROEx64 mnesia]# rabbitmqctl set_permissions -p vhost-test user-test ".*" ".* ".*"
```

##### 3.设置用户角色

<p>
如果希望用户可以使用web端进行集群的管理，可通过如下设置实现。
</p>

```linux
#授予用户管理员权限
[root@DEV-HROEx64 mnesia]# rabbitmqctl set_user_tags user_test administrator
```

## 参考资料

[配置](https://www.rabbitmq.com/configure.html)

[环境变量配置](https://www.rabbitmq.com/rabbitmq-env.conf.5.html)

[文件及目录配置](https://www.rabbitmq.com/relocate.html)
