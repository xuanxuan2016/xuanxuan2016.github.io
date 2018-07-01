---
layout:     post
title:      "laravel学习（六）-契约"
subtitle:   "Contracts"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---

> 介绍laravel框架中契约的主要使用方法。

## 文档

[英文文档](https://laravel.com/docs/5.6/contracts)

[中文文档](https://laravel-china.org/docs/laravel/5.6/contracts/1362)

## 自我理解

<p>
契约将服务所要提供的功能与如果实现这些功能进行了分离，在需要使用服务时通过契约进行解析而不是具体的实现，当我们需要修改具体实现时，不需要修改使用的地方，降低代码的耦合性。同时在我们想了解服务所提供的功能时，通过查看契约即可，契约就好比是服务的说明文档了。
</p>

## 使用依赖

<p>
为了能使框架解析契约，需要在服务提供者中将接口与实现进行绑定。
</p>

```
$this->app->bind(
    'App\Contracts\EventPusher',
    'App\Services\RedisEventPusher'
);
```

## 使用场景

##### 1.类构造函数的自动注入
##### 2.make对接口的解析
##### 3.facade中使用接口
