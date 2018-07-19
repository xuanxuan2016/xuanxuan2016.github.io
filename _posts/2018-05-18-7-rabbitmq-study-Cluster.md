---
layout:     post
title:      "RabbitMQ学习（七）-集群"
subtitle:   "cluster"
date:       2018-05-18 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
    - php
---

> 介绍RabbitMQ中，如何搭建普通集群与镜像集群。

## 机器准备

<p>
这里我们建立一个3台机器组成的集群，事先先在3台机器上安装好RabbitMQ服务。
</p>

[安装方法戳这里](https://xuanxuan2016.github.io/2018/05/08/rabbitmq-study-prepare-environment/)

<p>
机器IP：<br>
10.100.3.106<br>
10.100.2.234<br>
10.100.2.235<br>
</p>

## 普通集群

[官方文档](https://www.rabbitmq.com/clustering.html)

#### 1.概要

<p>
每台物理机是一个节点，消息实体只保存在一个节点上。
</p>

<p>
当节点出现故障时：<br>

- 消息持久化：当节点恢复时，可获取此节点未消费的消息<br>
- 消息未持久化：则会丢失此节点上未消费的消息<br>
</p>

<p>
当从A节点消费B节点上消息时，会通过消费者消费的队列找到该队列所在的节点，从此节点获取获取后，返回给消费者。
</p>

<p>
节点类型：<br>

- 内存节点（ram）：队列、交换机、绑定、用户、权限、vhost的元数据都存储在内存中
- 磁盘节点（disk）：数据存放在磁盘上，磁盘节点需要保存集群的配置信息
</p>

<p>
正常使用时都使用磁盘节点，除非需要改进有高队列、交换或绑定的性能集群。RAM节点并不提供更高的消息速率。
</p>

#### 2.hosts配置

<p>
在每个主机的<code>/etc/hosts</code>文件中加入如下配置，便于节点间访问：
</p>

```
#rabbitmq_node1
10.100.3.106 DEV-HROEx64
#rabbitmq_node2
10.100.2.234 DEV-mHRO
#rabbitmq_node3
10.100.2.235 DEV-mHRO64
```

<p>
如果需要主机名比较一致，可以修改主机名。
</p>

```
[root@DEV-HROEx64 ~]# vim /etc/sysconfig/network

NETWORKING=yes
HOSTNAME=DEV-HROEx64
#HOSTNAME=rabbitmq_node1

[root@DEV-HROEx64 ~]# reboot
```

#### 3.Erlang Cookie设置

<p>
RabbitMQ节点之间和命令行工具（如rabbitmqctl）是使用Cookie来确认是否允许互相通信的。对于多个能够通信的节点必须要有相同的Erlang Cookie。Cookie是一组随机的数字+字母的字符串，最大为255字节。
</p>

<p>
当Erlang Cookie文件不存在时，Erlang VM将尝试在RabbitMQ服务器启动时创建一个随机生成的值。
</p>

<p>
<code>.erlang.cookie</code>文件一般在如下2个地方：<br>

1. /var/lib/rabbitmq/.erlang.cookie
2. $HOME/.erlang.cookie
</p>

```
#这里我们将rabbitmq_node2与rabbitmq_node3上的cookie，都使用rabbitmq_node1上的cookie
[root@DEV-mHRO otp_src_20.3]# scp -r root@rabbitmq_node1:/root/.erlang.cookie /root/.erlang.cookie 
[root@DEV-mHRO64 bmsource]# scp -r root@rabbitmq_node1:/root/.erlang.cookie /root/.erlang.cookie 
```

```
#确认一下3台机器上的cookie是否一致
#rabbitmq_node1
# cat /root/.erlang.cookie 
ISIHBJETBSGTLNHVJLTQ

#rabbitmq_node2
# cat /root/.erlang.cookie 
ISIHBJETBSGTLNHVJLTQ

#rabbitmq_node3
# cat /root/.erlang.cookie 
ISIHBJETBSGTLNHVJLTQ
```

#### 4.重启RabbitMQ服务

```
#启动RabbitMQ服务

#rabbitmq_node1
[root@DEV-HROEx64 ~]# rabbitmq-server -detached

#rabbitmq_node2
[root@DEV-HROEx64 ~]# rabbitmq-server -detached

#rabbitmq_node3
[root@DEV-HROEx64 ~]# rabbitmq-server -detached
```

```
#查看每个节点集群状态

#rabbitmq_node1
[root@DEV-HROEx64 ~]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-HROEx64'
[{nodes,[{disc,['rabbit@DEV-HROEx64']}]},
 {running_nodes,['rabbit@DEV-HROEx64']},
 {cluster_name,<<"rabbit@DEV-HROEx64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-HROEx64',[]}]}]

#rabbitmq_node2
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-mHRO'
[{nodes,[{disc,['rabbit@DEV-mHRO']}]},
 {running_nodes,['rabbit@DEV-mHRO']},
 {cluster_name,<<"rabbit@DEV-mHRO">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-mHRO',[]}]}]

#rabbitmq_node3
[root@DEV-mHRO64 bmsource]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-mHRO64'
[{nodes,[{disc,['rabbit@DEV-mHRO64']}]},
 {running_nodes,['rabbit@DEV-mHRO64']},
 {cluster_name,<<"rabbit@DEV-mHRO64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-mHRO64',[]}]}]
```

#### 5.创建集群

<p>
通过将rabbitmq_node2与rabbitmq_node3加入到rabbitmq_node1中，我们可以搭建一个集群。
</p>

<p>
在rabbitmq-server启动时，会一起启动节点和应用，它预先设置RabbitMQ应用为standalone模式。要将一个节点加入到现有的集群中，需要停止这个应用并将节点设置为原始状态，然后就为加入集群准备好了。如果使用<code>rabbitmqctl stop</code>，应用和节点都将被关闭，而使用<code>rabbitmqctl stop_app</code>仅仅关闭应用。
</p>

```
#rabbitmq_node2加入到rabbitmq_node1
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl stop_app
Stopping rabbit application on node 'rabbit@DEV-mHRO'
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl join_cluster rabbit@DEV-HROEx64
Clustering node 'rabbit@DEV-mHRO' with 'rabbit@DEV-HROEx64'
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl start_app
Starting node 'rabbit@DEV-mHRO'

#rabbitmq_node3加入到rabbitmq_node1
[root@DEV-mHRO64 bmsource]# rabbitmqctl stop_app
Stopping rabbit application on node 'rabbit@DEV-mHRO64'
You have mail in /var/spool/mail/root
[root@DEV-mHRO64 bmsource]# rabbitmqctl join_cluster rabbit@DEV-HROEx64
Clustering node 'rabbit@DEV-mHRO64' with 'rabbit@DEV-HROEx64'
[root@DEV-mHRO64 bmsource]# rabbitmqctl start_app
Starting node 'rabbit@DEV-mHRO64'
```

```
#查看每个节点集群状态

#rabbitmq_node1
[root@DEV-HROEx64 ~]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-HROEx64'
[{nodes,[{disc,['rabbit@DEV-HROEx64','rabbit@DEV-mHRO','rabbit@DEV-mHRO64']}]},
 {running_nodes,['rabbit@DEV-mHRO64','rabbit@DEV-mHRO','rabbit@DEV-HROEx64']},
 {cluster_name,<<"rabbit@DEV-HROEx64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-mHRO64',[]},
          {'rabbit@DEV-mHRO',[]},
          {'rabbit@DEV-HROEx64',[]}]}]

#rabbitmq_node2
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-mHRO'
[{nodes,[{disc,['rabbit@DEV-HROEx64','rabbit@DEV-mHRO','rabbit@DEV-mHRO64']}]},
 {running_nodes,['rabbit@DEV-mHRO64','rabbit@DEV-HROEx64','rabbit@DEV-mHRO']},
 {cluster_name,<<"rabbit@DEV-HROEx64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-mHRO64',[]},
          {'rabbit@DEV-HROEx64',[]},
          {'rabbit@DEV-mHRO',[]}]}]

#rabbitmq_node3
[root@DEV-mHRO64 bmsource]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-mHRO64'
[{nodes,[{disc,['rabbit@DEV-HROEx64','rabbit@DEV-mHRO','rabbit@DEV-mHRO64']}]},
 {running_nodes,['rabbit@DEV-HROEx64','rabbit@DEV-mHRO','rabbit@DEV-mHRO64']},
 {cluster_name,<<"rabbit@DEV-HROEx64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-HROEx64',[]},
          {'rabbit@DEV-mHRO',[]},
          {'rabbit@DEV-mHRO64',[]}]}]
```

<p>
上面集群中各节点都是磁盘节点，如果希望节点是内存节点，可以参考如下设置方法。
</p>

```
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl stop_app
Stopping rabbit application on node 'rabbit@DEV-mHRO'
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl join_cluster --ram rabbit@DEV-HROEx64
Clustering node 'rabbit@DEV-mHRO' with 'rabbit@DEV-HROEx64'
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl start_app
Starting node 'rabbit@DEV-mHRO'
```

```
#查看集群状态

[root@DEV-HROEx64 ~]# rabbitmqctl cluster_status
Cluster status of node 'rabbit@DEV-HROEx64'
[{nodes,[{disc,['rabbit@DEV-HROEx64','rabbit@DEV-mHRO64']},
         {ram,['rabbit@DEV-mHRO']}]},
 {running_nodes,['rabbit@DEV-mHRO','rabbit@DEV-mHRO64','rabbit@DEV-HROEx64']},
 {cluster_name,<<"rabbit@DEV-HROEx64">>},
 {partitions,[]},
 {alarms,[{'rabbit@DEV-mHRO',[]},
          {'rabbit@DEV-mHRO64',[]},
          {'rabbit@DEV-HROEx64',[]}]}]

```

<p>
如果原先集群的挂载的节点(rabbit@DEV-HROEx64)从集群(rabbit@DEV-HROEx64)脱离了，在重新挂载到集群时，会报如下错误。
</p>

```
[root@DEV-HROEx64 /]# rabbitmqctl join_cluster rabbit@DEV-HROEx64
Clustering node 'rabbit@DEV-HROEx64' with 'rabbit@DEV-HROEx64'
Error: cannot_cluster_node_with_itself
```

<p>
可将节点挂载到集群中的任意有效节点(rabbit@DEV-mHRO)，则节点也会进入到集群中。
</p>

```
[root@DEV-HROEx64 /]# rabbitmqctl join_cluster rabbit@DEV-mHRO
```

#### 6.从集群中移除节点

##### 6.1.在要脱离的节点机器上

```
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl stop_app
Stopping rabbit application on node 'rabbit@DEV-mHRO'
You have mail in /var/spool/mail/root
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl reset
Resetting node 'rabbit@DEV-mHRO'
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl start_app
Starting node 'rabbit@DEV-mHRO'
```

##### 6.2.在其他节点机器上

```
#模拟rabbitmq_node2没服务了
[root@DEV-mHRO otp_src_20.3]# rabbitmqctl stop_app
Stopping rabbit application on node 'rabbit@DEV-mHRO'

#在rabbitmq_node1上移除rabbitmq_node2
[root@DEV-HROEx64 ~]# rabbitmqctl forget_cluster_node rabbit@DEV-mHRO
Removing node 'rabbit@DEV-mHRO' from cluster
```

#### 7.web管理端查看集群状态

##### 7.1.新增集群账号

<p>
在集群中任意一个节点上，新增一个可用账号。
</p>

```
#rabbitmq_node1添加账号
[root@DEV-HROEx64 ~]# rabbitmqctl add_user admin admin
[root@DEV-HROEx64 ~]# rabbitmqctl set_permissions -p / admin ".*" ".*" ".*"
Setting permissions for user "admin" in vhost "/"
[root@DEV-HROEx64 ~]# rabbitmqctl set_user_tags admin administrator
Setting tags for user "admin" to [administrator]
```

##### 7.2.开启rabbitmq_management插件

<p>
为了监控数据的准确性需要开启每台服务器的rabbitmq_management插件。
</p>

```
#rabbitmq_node1
[root@DEV-HROEx64 ~]# rabbitmq-plugins enable rabbitmq_management

#rabbitmq_node2
[root@DEV-mHRO ~]# rabbitmq-plugins enable rabbitmq_management

#rabbitmq_node3
[root@DEV-mHRO64 ~]# rabbitmq-plugins enable rabbitmq_management
```

##### 7.3.登录web管理端查看集群状态

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526161717.png?raw=true)

#### 8.集群使用测试

<p>
为了验证集群的可用性，我们将消息发送到<code>rabbitmq_node1</code>上的队列，在<code>rabbitmq_node2</code>与<code>rabbitmq_node3</code>获取队列里的消息。
</p>

##### 8.1.测试代码

[生产-node1](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_cluster_producer.php)

[消费-node2](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_cluster_consumer1.php)

[消费-node3](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_cluster_consumer2.php)

##### 8.2.web管理端信息

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526170231.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526170706.png?raw=true)

## 镜像集群

[官方文档](https://www.rabbitmq.com/ha.html)

#### 1.概要

<p>
与普通集群不同的是，镜像集群会把队列结构与消息存放在多个节点，消息会在镜像节点间同步，从而实现高可用的架构。
</p>

<p>
由于需要进行节点间的同步，所以镜像集群在性能方面会降低；如果镜像队列数量过多，加之大量的消息进入，集群内部的网络带宽将会被这种同步通讯大量消耗。所以这种模式应用于可靠性要求较高的场合中。
</p>

#### 2.如何配置

<p>
镜像功能，需要通过<a href='https://www.rabbitmq.com/parameters.html#policies' target='_blank'>RabbitMQ策略</a>来实现。策略可以控制一个集群内某个vhost中的队列与交换器的镜像行为。在集群的任意节点创建策略，策略都会同步到其他节点。
</p>

<p>

- Pattern：正则表达式，【\^】代表所有队列，【\^test】代表test开头的队列
- HA mode：可选【all,exactly,nodes】，正常都使用【all】，如果使用【exactly,nodes】还需要配置HA params
</p>

#### 3.环境准备

<p>
镜像集群是普通集群的扩展使用，所以可以参照普通集群的搭建方式事先进行搭建。
</p>

#### 4.创建镜像策略

##### 4.1.web管理端

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526183111.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526183151.png?raw=true)

##### 4.2.cli命令行

```
[root@DEV-HROEx64 ~]# rabbitmqctl set_policy -p / test_policy2 '^' '{"ha-mode":"all"}'
Setting policy "test_policy2" for pattern "^" to "{\"ha-mode\":\"all\"}" with priority "0"
```

##### 4.3.策略查看

<p>
检查3台机器上的策略已经同步为一致了。
</p>

```
#rabbitmq_node1
[root@DEV-HROEx64 ~]# rabbitmqctl list_policies
Listing policies
/	test_policy2	all	^	{"ha-mode":"all"}	0
/	test_policy	    all	^	{"ha-mode":"all"}	0

#rabbitmq_node2
[root@DEV-mHRO ~]# rabbitmqctl list_policies
Listing policies
/	test_policy2	all	^	{"ha-mode":"all"}	0
/	test_policy	    all	^	{"ha-mode":"all"}	0

#rabbitmq_node3
[root@DEV-mHRO64 ~]# rabbitmqctl list_policies
Listing policies
/	test_policy2	all	^	{"ha-mode":"all"}	0
/	test_policy	    all	^	{"ha-mode":"all"}	0
```

#### 5.镜像集群使用测试

<p>
测试代码同上面普通集群的测试代码。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526190556.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-18-7-rabbitmq-study-Cluster/20180526190945.png?raw=true)
