---
layout:     post
title:      "RabbitMQ学习-队列数据同步"
subtitle:   "queue data sync"
date:       2019-04-01 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍RabbitMQ镜像集群中，镜像队列的数据同步规则。

## 概要

#### 镜像队列

<p>
集群的队列创建后默认只在一个节点上，当集群配置为镜像集群时，队列会被镜像到所有节点上。消息发布到信道后，会被投递到主队列及镜像队列中。一个镜像队列包含一个主队列（master）和多个从队列（slave）。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190403151814.png?raw=true)

#### 非同步队列

<p>
rabbitmq中同步（synchronised）是用来表示master和slave之间的数据状态是否一致的。如果slave包含master中的所有message，则这个slave是synchronised；如果这个slave并没有包含所有master中的message，则这个slave是unsynchronised。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401134813.png?raw=true)

#### 何时出现非同步队列

##### 新节点加入

<p>
当新slave加入到镜像队列时，此时新slave是空的，而master中这时可能已有消息。假设这时master包含了10条消息，当第11条消息被添加到这个镜像队列中，新slave会从第11条消息开始接收。这时新slave就是unsynchronised状态。如果前10条消息从镜像队列中被消费掉了, 新slave会自动变成synchronised状态。
</p>

##### 旧节点重新加入

<p>
当节点由于异常关闭或其他情况重启加入集群后，如果节点内有消息，那么节点就是unsynchronised状态。当出现如下情况（同样也适用于新节点加入），slave会变成synchronised状态：
</p>

- master消息数为0
- master中旧有的消息被消费完

<p style='color:red;'>
疑问：旧节点重新加入后，可用消息会显示为master的，此时旧节点状态可能还是未同步，内部处理机制是否是未同步的节点，是不提供服务的吗？
</p>

##### 选主模式

<p>
理论上越早加入的slave节点，状态越有机会是同步的，所以rabbitmq通过这种方式选主。当master出现异常因消失时，最老的slave（状态需要为已同步）被提升成master。
</p>

## 同步模式

<p>
在设置集群策略时，可使用【ha-sync-mode】参数来控制使用手动同步（manual）还是自动同步（automatic），默认为手动同步。
</p>

#### 手动

<p>
如果镜像队列被设置成manual模式，当一个slave加入或重新加入队列时的行为，队列会根据消息情况来决定是否为同步状态，否则就为未同步状态。当然也可通过命令来进行同步。
</p>

```linux
#手动同步队列
[root@DEV-mHRO64 redis]# rabbitmqctl sync_queue queue_name
```

#### 自动

<p>
如果镜像队列被设置成automatic模式，当一个新slave加入或已有slave重新加入时，slave会自动同步master中的所有消息，在所有消息被同步完成之前，队列的所有操作都会被阻塞（blocking）。
</p>

#### 区别

- manual：不保证数据可靠性，在某些情况下可能会丢失消息，但是保证了队列的可用性
- automatic：提高了数据的可靠性，但是当有新slave加入时，可能会出现队列的暂时不可用

## 同步测试

<p>
针对上面不同的同步场景，进行相应的测试。
</p>

#### 新节点加入

##### 手动模式

<p>
原有节点情况，队列中包含2条消息
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401165408.png?raw=true)

<p>
新加入一个节点，因为原先队列有消息，所以新slave为未同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401170808.png?raw=true)

<p>
队列中还在不断的增加消息
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401174309.png?raw=true)

<p>
当队列中旧消息消费后，新slave变为同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401174409.png?raw=true)

##### 自动模式

<p>
原有节点情况，队列中包含2条消息
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401165408.png?raw=true)

<p>
新加入一个节点，rabbitmq执行自动同步（阻塞队列使用），所以新slave为同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401175116.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401174409.png?raw=true)

#### 旧节点重新加入

##### 手动模式

<p>
原有节点情况，队列中包含2条消息，总共有3个队列
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401180216.png?raw=true)

<p>
某个节点由于各种原因停止服务了，只有2个队列了
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401180451.png?raw=true)

<p>
队列中还在不断的消费消息
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401181316.png?raw=true)

<p>
当节点重新加入到集群后，为未同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401184201.png?raw=true)

<p>
队列中还在不断的消费消息与增加消息，当队列中旧消息消费后，节点变为同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401184537.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401185432.png?raw=true)


##### 自动模式

<p>
原有节点情况，队列中包含2条消息，总共有3个队列
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401180216.png?raw=true)

<p>
某个节点由于各种原因停止服务了，只有2个队列了
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401180451.png?raw=true)

<p>
队列中还在不断的消费消息
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401181316.png?raw=true)

<p>
当节点重新加入到集群后，自动进行同步，为同步状态
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190401193449.png?raw=true)

## 选主测试

<p>
原有节点情况，<code>rabbit@DEV-HROEx64</code>为master节点。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190402111953.png?raw=true)

<p>
将原有master节点关闭，<code>rabbit@DEV-mHRO64</code>切换为master节点。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190402112043.png?raw=true)

<p>
在启动<code>rabbit@DEV-HROEx64</code>节点，此节点变成了slave节点。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-04-01-14-rabbitmq-study-queue-data-sync/20190402112230.png?raw=true)

<p style='color:red;'>
Tips：未同步的slave是不能选为master的，如果不存在已同步的slave，则队列将不能使用。
</p>

## 参考资料

