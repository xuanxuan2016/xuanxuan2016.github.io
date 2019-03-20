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
RabbitMQ节点一般指RabbitMQ应用程序和其所在的Erlang节点，当运行在Erlang节点上的应用程序崩溃时，Erlang会自动尝试重启应用程序（如果Erlang本身没有崩溃）。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-3-rabbitmq-study-server-command/20190318103109.png?raw=true)

#### 启动节点

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

#### 关闭

```
#只关闭应用程序，可用于加入及退出集群的操作
[root@vagrant rabbitmq]# rabbitmqctl stop_app
Stopping rabbit application on node rabbit@vagrant ...
```

```
#关闭整个节点
[root@vagrant rabbitmq]# rabbitmqctl stop
Stopping and halting node rabbit@vagrant ...
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
- conf：用户可配置（交换器与队列的新建或删除）的资源名称，使用正则表达式匹配
- write：用户可写（发布消息，需要绑定成功）的资源名称，使用正则表达式匹配
- read：用户可读（消息的操作，需要绑定成功）的资源名称，使用正则表达式匹配

<p>
如下为不同AMQP命令对应的权限
</p>


AMQP命令 | 配置 | 写 | 读
---|---|---|---
exchange.declare | exchange |  | 
exchange.delete | exchange |  | 
queue.declare | queue |  | 
queue.delete | queue |  | 
queue.bind |  | queue | exchange
basic.publish |  | exchange | 
basic.get |  |  | queue
basic.consume |  |  | queue
queue.purge |  |  | queue


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

#### 使用统计

##### list_queues

<p>
显示队列信息，默认主机为【/】
</p>

- --offline：主节点不可用的持久化队列
- --online：主节点可用的队列
- --local：主节点在当前进程上的队列

<p>
queueinfoitem可选参数
</p>

- name：队列名称
- durable：队列是否持久的，服务器重启后仍存在
- auto_delete：当队列不使用时，是否自动删除
- arguments：队列参数
- policy：有效的队列策略
- pid：队列的Erlang进程id
- owner_pid：当队列为独占时，连接的Erlang进程id
- exclusive：队列是否独占
- exclusive_consumer_pid：订阅队列的独占消费者的信道的Erlang进程id
- exclusive_consumer_tag：订阅队列的独占消费者的标签
- messages_ready：可被消费者消费的消息数
- messages_unacknowledged：已被消费者获取，但还未确认的消息数
- messages：messages_ready+messages_unacknowledged
- messages_ready_ram：内存中，可被消费者消费的消息数
- messages_unacknowledged_ram：内存中，已被消费者获取，但还未确认的消息数
- messages_ram：内存中，messages_ready+messages_unacknowledged
- messages_persistent：队列中的持久化消息数
- message_bytes：队列中消息的大小(仅算正文)
- message_bytes_ready：队列中消息的大小(仅算正文)，可被消费者消费的消息数
- message_bytes_unacknowledged：队列中消息的大小(仅算正文)，已被消费者获取，但还未确认的消息数
- message_bytes_ram：队列中在内存消息的大小(仅算正文)
- message_bytes_persistent：队列中持久化消息的大小(仅算正文)
- head_message_timestamp：队列中第一条消息的timestamp
- disk_reads：队列启动后从磁盘读取消息的总次数
- disk_writes：队列启动后向磁盘写入消息的总次数
- consumers：消费者数量
- consumer_utilisation：队列能够立即向消费者传递消息的时间分数（介于0.0和1.0之间）。如果消费者受到网络拥塞或预取计数的限制，则可以小于1.0
- memory：队列运行时分配的内存字节数，包括堆栈，堆和内部结构
- slave_pids：如果队列是镜像的，则列出镜像的ID(跟随者副本)
- synchronised_slave_pids：如果镜像是队列，则列出主（领导者）同步的镜像（跟随者副本）的ID
- state：队列状态，一般为running

```
#命令格式
list_queues [-p vhost] [--offline | --online | --local] [queueinfoitem ...]

#示例
rabbitmqctl list_queues name pid
```

##### list_exchanges

<p>
显示交换器信息，默认主机为【/】
</p>

<p>
exchangeinfoitem可选参数
</p>

- name：交换器名称
- type：交换器类型(fanout,direct,headers,topic)
- durable：交换器是否持久的，服务器重启后仍存在
- auto_delete：当交换器不使用时，是否自动删除
- internal：是否内部交换器，
- arguments：交换器参数
- policy：应用在交换器上的策略名称

```
#命令格式
list_exchanges [-p vhost] [exchangeinfoitem ...]

#示例
rabbitmqctl list_exchanges name type
```

##### list_bindings 

<p>
显示绑定信息，默认主机为【/】
</p>

<p>
bindinginfoitem可选参数
</p>

- source_name：交换器名称
- source_kind：exchange
- destination_name：队列名称
- destination_kind：queue
- routing_key：绑定的路由键
- arguments：绑定参数

```
#命令格式
list_bindings [-p vhost] [bindinginfoitem ...]

#示例
rabbitmqctl list_bindings
```

##### list_connections

<p>
显示连接信息
</p>

<p>
connectioninfoitem可选参数
</p>

- pid：与连接关联的Erlang进程id
- name：连接的可读名称
- port：服务器端口
- host：服务器ip
- peer_port：客户端端口
- peer_host：客户端ip
- ssl：是否开启ssl
- ssl_protocol：ssl协议
- ssl_key_exchange：SSL密钥交换算法（例如“rsa”）
- ssl_cipher：SSL密码算法（例如“aes_256_cbc”）
- ssl_hash：SSL散列函数（例如“sha”）
- peer_cert_subject：对等方SSL证书的主题，RFC4514格式
- peer_cert_issuer：对等方SSL证书的颁发者，采用RFC4514格式
- peer_cert_validity：对等方SSL证书有效的时间段
- state：连接状态（starting,tuning,opening,running,flow,blocking,blocked,closing,closed）
- channels：使用连接的信道数量
- protocol：AMQP协议版本（0.9.1,0.8.0）
- auth_mechanism：使用SASL身份验证机制，例如“PLAIN”
- user：连接使用的用户名
- vhost：连接的虚拟主机
- timeout：连接的超时时间/心跳间隔
- frame_max：最大帧大小
- channel_max：连接的可用信道数
- client_properties：连接的客户端属性
- recv_oct：Octets received
- recv_cnt：Packets received
- send_oct：Octets send
- send_cnt：Packets sent
- send_pend ：Send queue size
- connected_at：连接建立时间


```
#命令格式
list_connections [connectioninfoitem ...]

#示例
rabbitmqctl list_connections
```

##### list_channels

<p>
显示信道信息
</p>

<p>
channelinfoitem可选参数
</p>

- pid：与连接关联的Erlang进程id
- connection：信道所在的连接
- name：信道名称
- number：信道编号
- user：连接使用的用户名
- vhost：连接的虚拟主机
- confirm：信道是否处于确认模式
- consumer_count：信道的消费者数量
- messages_unacknowledged：未被确认的已发布的消息数
- messages_unconfirmed：未被消费者确认的消息数
- prefetch_count：新消费者的预取限制
- global_prefetch_count：整个信道的预取限制

```
#命令格式
list_channels [channelinfoitem ...]

#示例
rabbitmqctl list_channels
```

##### list_consumers

<p>
显示消费者信息
</p>

```
#命令格式
list_consumers [-p vhost]

#示例
rabbitmqctl list_consumers
```

## rabbitmq-plugins


## 参考资料

[rabbitmq-server](https://www.rabbitmq.com/rabbitmq-server.8.html)

[rabbitmqctl](https://www.rabbitmq.com/rabbitmqctl.8.html)

[rabbitmq-plugins](https://www.rabbitmq.com/rabbitmq-plugins.8.html)
