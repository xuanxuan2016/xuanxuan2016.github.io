---
layout:     post
title:      "laravel学习（一）-核心架构"
subtitle:   "Architecture Concepts"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---

> 介绍laravel框架的主要架构，服务是如何绑定到容器，又如何从容器中获取服务。

## 概述

<p>
在laravel中，传统意义上的web网站被称作为应用，应用中的所有服务放在服务容器中，当需要使用服务时，可以从服务容器中解析。服务主要包括框架自带的、通过composer引入的第三方库、为了实现业务功能自行添加的。
</p>

<p>
服务是指为了实现某些特定功能而写的类。在使用类的时候，传统做法是在需要的地方进行实例化，或者通过工厂进行实例化。而在laravel中服务（类）的实例化是通过服务容器统一处理的，包括常规类的解析、标记的一组类的解析、上下文绑定类的解析、类对外部服务依赖的自动解析、接口到具体的实现的解析、类实例的扩展处理，基于服务容器的作用可构建大型、可维护、可扩展的应用网站。
</p>

## 架构图

<p>
此图主要是展示服务容器（Service Container），服务提供者（Service Providers），外观（Facades），契约（Contracts）之间的关系。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-1-laravel-study-architecture/20180621155721.png?raw=true)

## 架构说明

#### [1.服务容器](https://xuanxuan2016.github.io/2018/06/06/2-laravel-study-service-container/)

<p>
用于管理应用中的服务，提供添加服务与解析服务的接口，对于服务构造方法(__construct)中的依赖项，可自动进行解析注入。
</p>

<p>
如果服务不依赖接口，且服务非共享的，可以不绑定到容器中，在使用服务时同样可以通过容器解析。
</p>

#### [2.服务提供者](https://xuanxuan2016.github.io/2018/06/06/3-laravel-study-service-providers/)

<p>
用于向服务容器中绑定服务，laravel框架自带了很多服务提供者，如果自己需要绑定新服务也应该通过服务提供者。
</p>

- 即时加载：应用启动的时候
- 延迟加载：使用到服务的时候
- 事件加载：服务提供者配置了when，当when触发时加载

#### [3.外观](https://xuanxuan2016.github.io/2018/06/06/4-laravel-study-facades/)

<p>
提供访问服务容器可用类中方法的静态接口，在使用应用中通用的功能时比较简单，不需要通过长类名进行进行解析服务。
</p>

#### [4.契约](https://xuanxuan2016.github.io/2018/06/06/5-laravel-study-contracts/)

<p>
提供了服务要实现功能的接口，需要借助于服务提供者将接口与实现进行绑定，在通过容器解析接口时，实际是解析的接口的实现。通过接口定义的服务，可以方便的知道服务提供的功能，如果接口的实现变了可以很容易的进行修改，降低应用的耦合性。
</p>
