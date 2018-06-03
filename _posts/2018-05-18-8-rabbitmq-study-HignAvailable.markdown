---
layout:     post
title:      "RabbitMQ学习（七）-高可用"
subtitle:   "exchange"
date:       2018-05-19 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
    - php
---

> 介绍RabbitMQ中，如何来实现高可用。

## 架构图



## 服务器

<p>
为了保证RabbitMQ服务器的可用性，线上环境一般都使用镜像集群，当集群中某些节点不可用时，集群还是可以工作的。
</p>

[镜像集群搭建方法]()

## 生产者

#### 1.队列持久化

<p>
正常的业务队列定义好后一般都会一直使用，即使服务器重启也不会消失。
</p>

```
#在定义队列时(一般在消费者处)，控制如下2个参数
durable：true
auto_delete：false
```

#### 2.消息持久化

<p>
为了确保消息在服务器出问题的时候也不会丢失，需要将消息持久到磁盘。
</p>

```
#在定义消息时增加属性
properties：['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
```

#### 3.备用交换器

[官方文档](https://www.rabbitmq.com/ae.html)

<p>
为了确保能跟踪到所有发送给交换器的消息，在我们预想接收的消息外，可以设置备用交换器，来捕获其他未被路由的消息。
</p>

```
//备用交换器参数
$arrArgument = new Wire\AMQPTable([
    'alternate-exchange' => $strAeExchangeName
]);
//创建交换器，增加备用交换器设置
$this->getChannel()->exchange_declare($strExchangeName, $strExchangeType, false, true, false, false, false, $arrArgument);
```


#### 4.生产者确认

[官方文档](http://www.rabbitmq.com/confirms.html#publisher-confirms)

<p>
为了确保生产者能成功的将消息推送给RabbitMQ服务器，需要使用生产者确认。
</p>

```
//开启信道确认模式
$this->getChannel()->confirm_select();
//设置信道回调方法
$this->getChannel()->set_ack_handler(function(AMQPMessage $objMessage) {
    $this->ackHandler($objMessage);
});
$this->getChannel()->set_nack_handler(function(AMQPMessage $objMessage) {
    $this->nackHandler($objMessage);
});
```

#### 5.代码示例

[生产者](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_ha_producer.php)

## 消费者

#### 1.消费者不下线

<p>
在大部分业务中，消费者都是启动之后就不停止的，但是如果RabbitMQ服务器异常导致连接不上，就不能正常消费队列中的消息，这时消费者需要能够自动切换其他可连接的服务器。
</p>

```
while (1) {
    try {
        while (1) {
            $this->getChannel()->wait();
        }
    } catch (\Exception $e) {
        //日志记录
        //重建
        $this->reset();
        if (!$this->build($this->arrInitParam)) {
            //日志记录
            break;
        }
    }
}
```

#### 2.单条信息获取

<p>
为了确保消费者在消费消息时能够进行确认成功消费，每次只能队列中获取一条消息。
</p>

```
//每次只接受一条信息
$this->getChannel()->basic_qos(null, 1, null);
```

#### 3.死信队列

[官方文档](https://www.rabbitmq.com/dlx.html)

<p>
当消费者在消费某个消息失败后，如果将消息重新投入队列，则此消息还会被当前消费者接收到，如果一直不能成功消费，则会阻碍其他消息的消费。
</p>

<p>
可以将消费失败的消息，投入到死信队列，通过其他逻辑对它们进行处理，从而不影响正常的功能。
</p>

```
//死信交换器参数
$arrArgument = new Wire\AMQPTable([
    'x-dead-letter-exchange' => $strDqExchangeName,
    'x-dead-letter-routing-key' => $strDqRouteKey
]);
//创建队列，增加死信队列设置
$this->getChannel()->queue_declare($strQueueName, false, true, false, false, false, $arrArgument);
```

#### 4.消费者确认

[官方文档](http://www.rabbitmq.com/confirms.html#consumer-acknowledgements)

<p>
为了确保消费者成功的消费的消息，从而从队列中删除此消息，需要使用生产者确认模式。
</p>

```
//接收消息
$this->getChannel()->basic_consume($arrQueue['queue_name'], '', false, false, false, false, function(AMQPMessage $objMessage) {
    $this->dealMessage($objMessage);
});
//业务确认是否成功
$blnAck = $this->receiveMessage($objMessage->body);
if ($blnAck) {
    $objMessage->delivery_info['channel']->basic_ack($objMessage->delivery_info['delivery_tag']);
} else {
    $objMessage->delivery_info['channel']->basic_reject($objMessage->delivery_info['delivery_tag'], $blnIsRequeue);
}
```

#### 5.代码示例

[消费者](https://github.com/beautymyth/rabbitmq-study/blob/master/topic_ha_consumer.php)