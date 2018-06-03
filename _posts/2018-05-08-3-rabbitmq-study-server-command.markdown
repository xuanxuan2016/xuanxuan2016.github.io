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

[官方文档](https://www.rabbitmq.com/rabbitmq-server.8.html)

#### 1.启动服务

```
#前台启动
rabbitmq-server start
```

```
#后台启动（守护进程）
rabbitmq-server -detached
```

## rabbitmqctl

## rabbitmq-plugins