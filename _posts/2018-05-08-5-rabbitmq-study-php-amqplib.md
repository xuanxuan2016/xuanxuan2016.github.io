---
layout:     post
title:      "RabbitMQ学习（五）-php-amqplib"
subtitle:   "php-amqplib"
date:       2018-05-08 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
    - php
---

> 介绍php-amqplib中的类，类之间的关系，常见的使用方法。

## 概述

[项目git地址。](https://github.com/php-amqplib/php-amqplib)

此库是使用纯php实现的AMQP 0-9-1协议，通过它可以让我们很方便的来使用rabbitmq的功能。

## UML

## 使用方法

这里以一个简单的exchange=redirect的例子，来通过代码演示怎么实现生产者与消费者。

#### 1.生产者

[戳这里看完整producer代码](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_simple_producer.php)

```linux
use Lib\PhpAmqpLib\Connection\AMQPStreamConnection;
use Lib\PhpAmqpLib\Message\AMQPMessage;

$strExchange = 'exchange_direct_simple';
$strQueue = 'queue_direct_simple';

$objConnection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', 'admin');
$objChannel = $objConnection->channel();

$objChannel->queue_declare($strQueue, false, true, false, false);
$objChannel->exchange_declare($strExchange, 'direct', false, true, false);
$objChannel->queue_bind($strQueue, $strExchange);

$objMessage = new AMQPMessage('hello world!');
$objChannel->basic_publish($objMessage, $strExchange);

$objChannel->close();
$objConnection->close();
```

#### 2.消费者

[戳这里看完整consumer代码](https://github.com/beautymyth/rabbitmq-study/blob/master/direct_simple_consumer.php)

```linux
use Lib\PhpAmqpLib\Connection\AMQPStreamConnection;
use Lib\PhpAmqpLib\Message\AMQPMessage;

$strExchange = 'exchange_direct_simple';
$strQueue = 'queue_direct_simple';
$strConsumerTag = 'consumer_direct_simple';

$objConnection = new AMQPStreamConnection('127.0.0.1', 5672, 'admin', 'admin');
$objChannel = $objConnection->channel();

$objChannel->queue_declare($strQueue, false, true, false, false);
$objChannel->exchange_declare($strExchange, 'direct', false, true, false);
$objChannel->queue_bind($strQueue, $strExchange);

//定义回调函数
function callback_func($objMessage) {
    echo " [x] Received ", $objMessage->body, "\n";
}

//php中止时执行的函数
function shutdown($objChannel, $objConnection) {
    //关闭信道与断开连接
    $objChannel->close();
    $objConnection->close();
}
register_shutdown_function('shutdown', $objChannel, $objConnection);

$objChannel->basic_consume($strQueue, $strConsumerTag, false, true, false, false, 'callback_func');


//阻塞等待服务器推送消息
while (count($objChannel->callbacks)) {
    $objChannel->wait();
}

```

## 代码解析

对类库中的一些代码进行跟踪分析，以便能更好的理解使用。

#### 1.AMQPChannel->AbstractChannel->wait()

阻塞等待rabbit服务器推送消息过来，如果接受到消息则调用回调方法处理，处理好后继续阻塞等待。
![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/AMQPChannel-_AbstractChannel-_wait.png?raw=true)
