---
layout:     post
title:      "RabbitMQ学习-php-amqplib"
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

## 代码结构

#### 类的归类

[戳这里看详细说明](http://naotu.baidu.com/file/31363a3566b1fdffedb4693cb3cc679b?token=6679f43790e2f5dc)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/class_relation.png?raw=true)

#### UML

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/uml_combine_1.png?raw=true)

## 代码流程

#### 创建connection

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/connection_flow.png?raw=true)

#### 创建channel

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/channel_flow.png?raw=true)

#### wait处理

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/wait_flow.png?raw=true)

#### 发送消息(producer)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/producer_flow.png?raw=true)

#### 接收消息(consumer)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-6-rabbitmq-study-php-amqplib/consumer_flow.png?raw=true)

## 代码注释

[戳这里看代码注释](https://github.com/beautymyth/rabbitmq-study/tree/master/Lib/PhpAmqpLib)

## 参考资料

[消费者应答和发送者确认](https://www.jianshu.com/p/c0bfe198739e)



