---
layout:     post
title:      "RabbitMQ学习-虚拟主机"
subtitle:   "virtual hosts"
date:       2019-03-12 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍RabbitMQ中虚拟主机的作用及使用方法。

## 概要

<p>
Rabbit的vhost类似于物理服务器上的虚拟机，每个vhost本质上是一个mini版的Rabbit服务器，拥有自己的队列、交换器、绑定等。不同的vhost有独立的权限机制，在逻辑上是完全分离的，可以安全保密的为不同的应用提供服务。当在Rabbit内新建用户时，用户可被指派给多个vhost，且只能访问指派vhost内的队列、交换器、队列。
</p>

<p>
当在集群的某个节点上创建vhost时，整个集群都会创建该vhost，解决了多应用的维护成本。
</p>

## vhost的维护

##### 1.新建虚拟主机

```linux
[root@DEV-HROEx64 mnesia]# rabbitmqctl add_vhost vhost_test
Creating vhost "vhost_test"
```

##### 2.删除虚拟主机

```linux
[root@DEV-HROEx64 mnesia]# rabbitmqctl delete_vhost vhost_test
Deleting vhost "vhost_test"
```

##### 3.显示所有虚拟主机

```linux
[root@DEV-HROEx64 mnesia]# rabbitmqctl list_vhosts
Listing vhosts
/
vhost_test
```

## vhost与用户绑定

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

[Virtual Hosts](https://www.rabbitmq.com/vhosts.html)
