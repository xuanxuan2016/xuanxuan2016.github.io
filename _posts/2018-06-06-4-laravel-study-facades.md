---
layout:     post
title:      "laravel学习（四）-外观"
subtitle:   "Facades"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---

> 介绍laravel框架中外观的主要使用方法。

## 文档

[英文文档](https://laravel.com/docs/5.6/facades)

[中文文档](https://laravel-china.org/docs/laravel/5.6/facades/1361)

## 创建外观

#### 1.新建外观

<p>
外观的新建需要继承于Facades类，并重写getFacadeAccessor用于提供容器解析时所需要的服务别名。
</p>

```
namespace Illuminate\Support\Facades;
class Cache extends Facade
{
    /**
     * 获取服务的注册名
     */
    protected static function getFacadeAccessor()
    {
        return 'cache';
    }
}
```

#### 2.引入外观

<p>
当我们有了facade，需要怎么配置到程序里呢，从而可以让框架对其进行操作。
</p>

<p>
应用中的facade都是在配置文件config/app.php的aliases中。
</p>

```
'aliases' => [
    //自定义的facade
    'Cache' => Illuminate\Support\Facades\Cache::class,
],
```

#### 3.加载流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-4-laravel-study-facades/20180625145952.png?raw=true)

## 使用外观

#### 1.使用方法

<p>
在使用外观时，通过类似于类中静态方法的调用方式，即可使用facade中的功能。
</p>

```
$user = Cache::get('user:'.$id);
```

#### 2.使用流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-4-laravel-study-facades/QQ20180625171351.png?raw=true)

## 外观的优缺点

#### 1.优点

<p>
可以很方便，随心所欲的使用facade类提供的功能。
</p>

#### 2.缺点

<p>
可能会在单个类中使用很多的facade，导致类的膨胀；而使用依赖注入会随着使用类的增多，构造函数会变长，在感官上引起我们的注意，可能类需要进行功能的重构了。因此在使用facade时，我们需要主观控制类的大小，当类变的比较大时，就需要进行重构了。
</p>

