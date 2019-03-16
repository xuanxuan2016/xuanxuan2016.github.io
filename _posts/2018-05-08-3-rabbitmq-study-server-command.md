---
layout:     post
title:      "RabbitMQ学习（三）-服务器命令"
subtitle:   "server-command"
date:       2018-05-08 19:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍RabbitMQ服务端命令的使用方法与作用。

## rabbitmq-server

<p>
RabbitMQ节点一般指RabbitMQ应用程序和其所在的Erlang节点，当运行在Erlang节点上的应用程序崩溃时，Erlang会自动尝试重启应用程序（前提是Erlang本身没有崩溃）。
</p>

#### 1.启动节点


```
#前台启动
rabbitmq-server
```

```
#后台启动（守护进程）
rabbitmq-server -detached
```

```
#增加启动变量
RABBITMQ_NODE_PORT=5673 rabbitmq-server -detached
```

## rabbitmqctl

<p>
此命令用于管理RabbitMQ节点。
</p>

```
#可在指定节点上执行命令
rabbitmqctl -n nodename commond
```

<p style="color:red;">
Tips：如下有些命令，在低版本里会没有。
</p>

#### 用户管理

##### add_user

<p>
添加用户并设置初始密码。
</p>

```
#命令格式
add_user username password

#示例
rabbitmqctl add_user user_test 123456
```

##### delete_user

<p>
删除用户。
</p>

```
#命令格式
delete_user username

#示例
rabbitmqctl delete_user user_test
```

##### change_password

<p>
修改用户的密码。
</p>

```
#命令格式
change_password username newpassword

#示例
rabbitmqctl change_password user_test 654321
```

##### clear_password 

<p>
删除用户的密码。此操作会导致用户使用密码不能登录(除非使用其他配置登录，如SASL EXTERNAL)。
</p>

```
#命令格式
clear_password username

#示例
rabbitmqctl clear_password user_test
```

##### authenticate_user  

<p>
验证用户名与密码是否匹配。
</p>

```
#命令格式
authenticate_user username password

#示例
rabbitmqctl authenticate_user user_test 123456
```

##### set_user_tags  

<p>
设置用户角色，同时会清除现有的所有设置。
</p>

- management：普通管理者，仅可登陆管理控制台(启用management plugin的情况下)，无法看到节点信息，也无法对策略进行管理
- policymaker：策略制定者，可登陆管理控制台(启用management plugin的情况下), 同时可以对policy进行管理
- monitoring：监控者，可登陆管理控制台(启用management plugin的情况下)，同时可以查看rabbitmq节点的相关信息(进程数，内存使用情况，磁盘使用情况等) 
- administrator：超级管理员，可登陆管理控制台(启用management plugin的情况下)，可查看所有的信息，并且可以对用户，策略(policy)进行操作

```
#命令格式
set_user_tags username [tag ...]

#示例
rabbitmqctl set_user_tags user_test administrator
```

##### list_users  

<p>
显示所有用户及用户的角色。
</p>

```
#命令格式
rabbitmqctl list_users

#示例
rabbitmqctl list_users
```

#### 访问控制

##### add_vhost

<p>
创建新的虚拟主机
</p>

```
#命令格式
add_vhost vhost

#示例
rabbitmqctl add_vhost test
```

##### delete_vhost

<p>
删除已有的虚拟主机，同时会删除虚拟主机中的交换器、队列、绑定、用户权限、参数和策略。
</p>

```
#命令格式
delete_vhost vhost

#示例
rabbitmqctl delete_vhost test
```

##### list_vhosts

<p>
显示所有主机信息，vhostinfoitem可为{name,tracing}
</p>

```
#命令格式
list_vhosts [vhostinfoitem ...]

#示例
rabbitmqctl list_vhosts name tracing
```

##### set_permissions

<p>
将虚拟主机指派给用户，同时设置相应权限。资源一般可理解为队列，交换器等。
</p>

- [-p vhost]：需要指派的主机名称，默认为“/”
- user：需要指派的用户
- conf：用户可配置的资源名称，使用正则表达式匹配
- write：用户可写的资源名称，使用正则表达式匹配
- read：用户可读的资源名称，使用正则表达式匹配


```
#命令格式
set_permissions [-p vhost] user conf write read

#示例
rabbitmqctl set_permissions -p vhost-test user-test ".*" ".* ".*"
```

##### clear_permissions

<p>
移除用户的在某个虚拟主机上的权限。
</p>

```
#命令格式
clear_permissions [-p vhost] username

#示例
rabbitmqctl clear_permissions -p vhost_test user_test
```

##### list_permissions

<p>
显示某虚拟主机下的用户及用户的权限。
</p>

```
#命令格式
list_permissions [-p vhost]

#示例
rabbitmqctl list_permissions -p vhost_test
```

##### list_user_permissions

<p>
显示用户所关联的虚拟主机及相应的权限。
</p>

```
#命令格式
list_user_permissions username

#示例
rabbitmqctl list_user_permissions user_test
```

##### set_topic_permissions

<p>
设置用户在虚拟主机中对于某个主题交换器，可发布或获取消息的routing key。
</p>

- [-p vhost]：需要指派的主机名称，默认为“/”
- user：需要指派的用户
- exchange：用户可配置的资源名称，使用正则表达式匹配
- write：用户发布消息的routing key，使用正则表达式匹配
- read：用户获取消息的routing key，使用正则表达式匹配


```
#命令格式
set_topic_permissions [-p vhost] user exchange write read

#示例
rabbitmqctl set_topic_permissions -p vhost_test user_test amq.topic "^xpz-.*" "^xpz-.*"
```

##### clear_topic_permissions

<p>
清除用户在主题交换器上的权限限制。
</p>

```
#命令格式
clear_topic_permissions [-p vhost] username [exchange]

#示例
rabbitmqctl clear_topic_permissions -p vhost_test user_test amq.topic
```

##### list_topic_permissions 

<p>
显示虚拟主机上所有用户的主题权限限制。
</p>

```
#命令格式
list_topic_permissions [-p  vhost]

#示例
rabbitmqctl list_topic_permissions -p vhost_test
```

##### list_topic_user_permissions 

<p>
显示用户所关联的虚拟主机及相应的主题权限限制。
</p>

```
#命令格式
list_user_topic_permissions username

#示例
rabbitmqctl list_topic_user_permissions user_test
```

#### 虚拟主机限制

##### set_vhost_limits

<p>
对虚拟主机设置某些限制。
</p>

<p>
definition为json格式的字符串，当值为负数时表示不做任何限制。
</p>

- max-connections：设置最大连接数
- max-queues：设置最多队列数

```
#命令格式
set_vhost_limits [-p vhost] definition

#示例
#设置最大连接数为64
rabbitmqctl set_vhost_limits -p vhost_test '{"max-connections": 64}'

#设置最多队列数为256
rabbitmqctl set_vhost_limits -p vhost_test '{"max-queues": 256}'

#设置最大连接数为不受限
rabbitmqctl set_vhost_limits -p vhost_test '{"max-connections": -1}'

#设置最大连接数为0，不允许任何连接进来
rabbitmqctl set_vhost_limits -p vhost_test '{"max-connections": 0}'
```

##### clear_vhost_limits

<p>
清除虚拟主机上的限制。
</p>

```
#命令格式
clear_vhost_limits [-p vhost]

#示例
rabbitmqctl clear_vhost_limits -p vhost_test
```

##### list_vhost_limits

<p>
清除虚拟主机上的限制。
</p>

```
#命令格式
list_vhost_limits [-p vhost] [--global]

#示例
#显示指定虚拟主机
rabbitmqctl list_vhost_limits -p vhost_test

#显示所有虚拟主机
rabbitmqctl list_vhost_limits --global
```

## rabbitmq-plugins


## 参考资料

[rabbitmq-server](https://www.rabbitmq.com/rabbitmq-server.8.html)

[rabbitmqctl](https://www.rabbitmq.com/rabbitmqctl.8.html)

[rabbitmq-plugins](https://www.rabbitmq.com/rabbitmq-plugins.8.html)
