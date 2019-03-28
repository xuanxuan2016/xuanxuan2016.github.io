---
layout:     post
title:      "RabbitMQ学习-交换器"
subtitle:   "exchange"
date:       2018-05-12 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
    - php
---

> 介绍RabbitMQ中，不同交换器的特性与常用使用方法。

## rabbit消息处理结构

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/message_deal_struct.jpg?raw=true)

## fanout

[官方文档](https://www.rabbitmq.com/tutorials/tutorial-three-php.html)

#### 1.概述

<p>
将发送到此交换器的消息，推送给所有与它绑定的队列中。可实现生产者发送一条消息，多个消费者都可进行消费的架构。
</p>

<p>
此交换器，在使用queue_bind方法时会忽视传入的$routing_key参数。
</p>

#### 2.Publish/Subscribe(发布/订阅)

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/fanout_publish_subscribe.png?raw=true)

##### 2.注意事项


```
生产者：
1.消息推送到定义的交换器
2.不主动将消息推送到队列(通过绑定)，而是等待消费者定义队列与此交换器绑定
3.在有队列绑定到交换器前，产生的消息都将会丢弃
4.如果想消息持久化，需要配置消息参数['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
```

```
消费者：
1.队列可以手动进行指定，配合队列持久化，消费者程序停止运行后，队列不删除，存在绑定关系，队列能继续接受到消息，等待下次有消费者访问时使用
2.队列可以自动生成，队列不需要持久化，消费者程序停止运行后，队列自动删除，不存在绑定关系，队列不能继续接受消息
3.消息一般只接受从绑定到交换器之后的消息，除非绑定是已存在且有消息的队列
```

##### 3.代码示例

[生产者](https://github.com/beautymyth/rabbitmq-study/blob/master/fanout_publish_subscribe_producer.php)

[消费者-持久化队列](https://github.com/beautymyth/rabbitmq-study/blob/master/fanout_publish_subscribe_consumer1.php)

[消费者-非持久化队列](https://github.com/beautymyth/rabbitmq-study/blob/master/fanout_publish_subscribe_consumer2.php)

## direct

[官方文档](https://www.rabbitmq.com/tutorials/tutorial-four-php.html)

#### 1.概述

<p>
将发送到此交换器的消息，推送给binding key(exchange)与routing key(queue)完全匹配的队列中。可实现生产者发送一条消息，消费者根据情况，进行选择消费的架构。
</p>

<p>
当推送消息的routekey没有队列与其进行绑定时，则消息会被丢弃。
</p>

#### 2.Routing(路由)

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/direct_routing1.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/direct_routing2.png?raw=true)

##### 2.注意事项


```
生产者：
1.根据需求对交换器发送不同routekey的消息
```

```
消费者：
1.同一队列可以绑定多个routekey
2.不同队列可以绑定同个routekey，类似于fanout功能
```

##### 3.代码示例

[生产者](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_routing_producer.php)

[消费者-同一队列可以绑定多个routekey-info,warning](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_routing_consumer1.php)

[消费者-同一队列可以绑定多个routekey-error](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_routing_consumer2.php)

[消费者-不同队列可以绑定同个routekey-info1](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_routing_consumer3.php)

[消费者-不同队列可以绑定同个routekey-info2](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_routing_consumer4.php)

## topic

#### 1.概述

[官方文档](https://www.rabbitmq.com/tutorials/tutorial-five-php.html)

<p>
将发送到此交换器的消息，推送给binding key(exchange)与routing key(queue)模糊匹配的队列中。可实现生产者发送一条消息，消费者根据情况，进行选择消费的架构。
</p>

<p>
当推送消息的routekey没有队列与其进行绑定时，则消息会被丢弃。
</p>

<p>
routekey规则：使用【.】连接的标识符【[a-z0-9]+】，上限为255字节。如【mobile.android.miaomi】
<br>
*：可以匹配一个标识符，如【mobile.*.*】,【*.android.*】
<br>
#：可匹配0个或多个标识符，如【mobile.#】，注意【#】前面的【.】不能少
</p>

#### 2.Topics(主题)

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/topic_topics1.jpg?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/topic_topics2.jpg?raw=true)

##### 2.注意事项


```
生产者：
1.根据需求对交换器发送不同routekey的消息
```

```
消费者：
1.同一队列可以绑定多个routekey
2.不同队列可以绑定同个routekey，类似于fanout功能
3.当队列绑定多个routekey时，即使消息满足多个routekey也只会进一次队列
4.当队列绑定【#】时，会接受所有消息
```

##### 3.代码示例

[生产者](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_topics_producer.php)

[消费者-\*.android.\*](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_topics_consumer1.php)

[消费者-\*.\*.iphone-computer.#](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_topics_consumer2.php)

[消费者-#](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_topics_consumer3.php)

## headers

## RPC

#### 1.概述

[官方文档](https://www.rabbitmq.com/tutorials/tutorial-six-php.html)

<p>
此节内容并不是exchange的一种类型，而是通过RabbitMQ实现Remote Procedure Call（远程过程调用）。
</p>

<p>
客户端可以发送请求给远程服务器，远程服务器来实现具体的逻辑运算，再将运算结果返回给客户端，从而可搭建运算中心，运算中心根据需求可进行扩展。
</p>


#### 2.rpc demo

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/rpc_fibonacci.jpg?raw=true)


##### 2.注意事项


```
客户端：
1.在请求服务器时，需要预定义响应队列（随机队列），用于服务器完成逻辑处理时，可以把返回信息推送到此队列中，便于客户端接收
2.对于发送的每条消息需要增加唯一的correlation_id属性，当客户端接收响应时判断是否为响应自己的请求
```

```
服务器：
1.为了请求的更好处理与多服务的分配，设置一次只接受一个请求
```

##### 3.代码示例

[客户端](https://github.com/beautymyth/rabbitmq-study/blob/master/rpc_fibonacci_client.php)

[服务器](https://github.com/beautymyth/rabbitmq-study/blob/master/rpc_fibonacci_server.php)

## Deal Letter

#### 1.概述

<p>
此节内容并不是exchange的一种类型，而是介绍RabbitMQ中的死信。
</p>

<p>
当消息出现如下情况时，会变成一条死信：<br>
1.消息被拒绝（业务处理失败）（basic.reject or basic.nack）并且requeue=false <br>
2.消息TTL过期 <br>
3.队列达到最大长度
</p>

<p>
对于有些死信，在业务上可能并希望永远丢掉，而是希望有别的处理，这时就需要对死信进行收集。
</p>

<p>
可以对收集死信的队列，增加如下参数：<br>
1.x-dead-letter-exchange(交换器)：将死信消息推送给哪个交换器<br>
2.x-dead-letter-routing-key(路由键)：推送消息时，附加的路由键（参见direct-routing文档），如果不设置则使用原消息的routekey，用于交换器进行消息路由
</p>

#### 2.rpc demo

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/rpc_fibonacci_dead.jpg?raw=true)


##### 2.注意事项

```
1.为了保证死信消息能正常接收，需要有一个queue与死信exchange通过routekey进行绑定
```

##### 3.代码示例

[客户端](https://github.com/beautymyth/rabbitmq-study/blob/master/rpc_fibonacci_client.php)

[服务器-line 43](https://github.com/beautymyth/rabbitmq-study/blob/master/rpc_fibonacci_server.php)

## Alternate Exchange

#### 1.概述

<p>
此节内容并不是exchange的一种类型，而是介绍RabbitMQ中的备用交换器。
</p>


<p>
对于有些未被路由的消息（业务垃圾消息，攻击消息），在业务上可能并不希望永远丢掉，而是希望有别的处理，这时就需要对这些消息进行收集。
</p>

<p>
可以对收集未路由消息的交换器，增加如下参数：<br>
1.alternate-exchange(交换器)：将未路由的消息推送给哪个交换器<br>
</p>

#### 2.demo

##### 1.结构图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-12-6-rabbitmq-study-exchange/alternate_exchange.jpg?raw=true)


##### 2.注意事项

```
1.为了保证未被路由的消息能正常接收，需要设置备用交换器的类别为fanout，这样任意与此交换器绑定的队列都能接受到消息，而不需要考虑routing key
```

##### 3.代码示例

[生产者](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_ha_producer.php)


