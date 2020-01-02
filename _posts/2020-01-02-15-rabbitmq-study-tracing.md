---
layout:     post
title:      "RabbitMQ学习-消息追踪"
subtitle:   "tracing"
date:       2020-01-02 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍使用trace插件来跟踪消息的生产与消费。

## 准备工作

#### 配置文件

<p>
<code>rabbitmq_tracing</code>默认使用<code>guest</code>用户来进行操作，但是此用户一般都会被删除，所以需要重新配置用户。
</p>

```
#rabbitmq.config
{rabbitmq_tracing,
     [{directory,"/var/tmp/rabbitmq-tracing"},
      {password,<<"admin">>},
      {username,<<"admin">>}]}
```

<p style='color:red;'>
Tips：如果是集群环境的话，需要每个节点都开启跟踪插件，且都要配置跟踪。
</p>

## 使用

#### 启用组件

```
rabbitmq-plugins enable rabbitmq_tracing
```

#### 建立跟踪

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2020-01-02-15-rabbitmq-study-tracing/tu_1.png?raw=true)

##### 参数说明

- Virtual host：虚拟主机
- Name：跟踪名称
- Format：日志记录类型（Text/JSON）
- Max payload bytes：消息体大小限制（空：不限制大小 0：消息内容都不记录）
- Pattern：匹配模式（#：匹配所有 deliver.#：匹配队列的消费 publish.#：匹配交换器的生产）

<p>
好像不能对某个队列配置生产监控。
</p>

```
#text格式
2020-01-02 8:10:06:312: Message received

Node:         rabbit@DEV-mHRO64
Connection:   <rabbit@DEV-mHRO64.1.1176.0>
Virtual host: interview
User:         admin
Channel:      1
Exchange:     common_invite
Routing keys: [<<"common.interview">>]
Queue:        common_interview
Properties:   [{<<"delivery_mode">>,signedint,2}]
Payload: 
{"id":17,"time":1577947773,"message_guid":"9f4fdfa2-f431-56e0-a847-694538157310"}
```

```
#json格式
{"timestamp":"2020-01-02 6:42:12:548","type":"published","node":"rabbit@DEV-HROEx64","connection":"10.100.50.115:5745 -> 10.100.3.106:5672","vhost":"interview","user":"interview","channel":1,"exchange":"common_invite","queue":"none","routed_queues":["common_interview"],"routing_keys":["common.interview"],"properties":{"delivery_mode":2},"payload":"eyJpZCI6MTEsInRpbWUiOjE1Nzc5NDc2MTAsIm1lc3NhZ2VfZ3VpZCI6IjhjMzM4YWMzLTk4ZjEtYThiZi02YmZkLTA5YzI2MzExNTM0MCJ9"}
```

## 参考资料

[Firehose Tracer](https://www.rabbitmq.com/firehose.html)

[RabbitMQ消息追踪插件rabbitmq_tracing](https://blog.csdn.net/xuangey/article/details/91563727)