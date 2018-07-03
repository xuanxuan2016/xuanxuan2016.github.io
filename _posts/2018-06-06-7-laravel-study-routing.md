---
layout:     post
title:      "laravel学习（七）-路由"
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

> 介绍laravel框架中路由的创建与使用。

## 文档

[英文文档](https://laravel.com/docs/5.6/routing)

[中文文档](https://laravel-china.org/docs/laravel/5.6/routing/1363)

## 路由创建

#### 1.文件引入

<p>
框架中的路由配置主要在<code>routes\web.php</code>与<code>routes\api.php</code>文件中，那么框架是如何加载文件里的路由的呢?
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-7-laravel-study-routing/20180702151924.png?raw=true)

#### 2.路由组

<p>
如果需要注册拥有相同属性的路由的话，可通过group，在group之前定义共享属性（as,domain,middleware,name,namespace,prefix），最后调用group将共享属性应用到路由中。
</p>

```
Route::prefix('api')
    ->middleware('api')
    ->namespace($this->namespace)
    ->group(function(){
        /*路由注册*/
    });
```

<p>
如果在group中套group的话，则里面的group会引入上层group的属性，也就是最里面一层的group会包含所有上层group的属性。
</p>

#### 3.路由注册

<p>
当框架加载了路由配置文件，就需要将路由注册到应用了，以便之后能使用路由。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-7-laravel-study-routing/20180702164659.png?raw=true)

## 路由使用

<p>
当应用接收到http请求时，需要经过路由将请求分发给闭包函数或控制器中的方法进行处理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-7-laravel-study-routing/2018-07-03_082026.png?raw=true)

