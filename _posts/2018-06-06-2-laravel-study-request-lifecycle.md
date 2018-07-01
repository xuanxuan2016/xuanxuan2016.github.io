---
layout:     post
title:      "laravel学习（二）-请求周期"
subtitle:   "Request Lifecycle"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---
> 介绍laravel框架的请求周期，应用是如果处理请求，并返回响应的。

## 文档

[英文文档](https://laravel.com/docs/5.6/lifecycle)

[中文文档](https://laravel-china.org/docs/laravel/5.6/lifecycle/1358)

## 入口文件

<p>
应用程序对外部请求的响应，都是通过<code>public/index.php</code>文件，这需要在web服务器（apache/nginx）上配置将所有的请求都引导到此文件。
</p>


![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/2018-07-01_114410.png?raw=true)

## 应用创建

<p>
应用创建在<code>bootstrap/app.php</code>文件中，包括Application的实例化与重要接口的共享绑定。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/20180701154421.png?raw=true)

## 内核解析

<p>
解析获取内核的实例，同时将应用实例与路由器实例注入到内核实例中。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/20180701160411.png?raw=true)

## 处理http请求

<p>
获取到内核实例后，就需要调用内核中的handle方法来处理http请求。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/2018-07-01_163905.png?raw=true)

## 发送响应

<p>
请求经过内核处理后，我们会获取到响应的实例，这时需要将响应发送到客户端。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/20180701164414.png?raw=true)

## 应用结束

<p>
当响应发送给客户端后，我们的请求周期也算是到结束了，最后在做一个收尾的动作。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-request-lifecycle/20180701165015.png?raw=true)
