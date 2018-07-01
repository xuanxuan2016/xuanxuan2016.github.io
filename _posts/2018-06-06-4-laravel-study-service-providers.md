---
layout:     post
title:      "laravel学习（四）-服务提供者"
subtitle:   "Service Providers"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---

> 介绍laravel框架中服务提供者的主要使用方法。

## 文档

[英文文档](https://laravel.com/docs/5.6/providers)

[中文文档](https://laravel-china.org/docs/laravel/5.6/providers/1360)

## 创建服务提供者

#### 1.常规使用

<p>
此种使用方法，能满足大部分情况下对服务提供者的使用。
</p>

```
class DemoServiceProvider extends ServiceProvider {

    /**
     * 注册方法，会在容器内被调用
     */
    public function register() {
        //向容器中绑定服务
        $this->app->singleton(Connection::class, function ($app) {
            return new Connection(config('demo'));
        });
    }

}
```

#### 2.快捷绑定

<p>
如果只是向容器中注册简单的绑定，可通过如下方法，而不需要手动注册每个服务绑定。
</p>

```
class DemoServiceProvider extends ServiceProvider {

    /**
     * 普通绑定对应关系
     */
    public $bindings = [
        ServerProvider::class => DigitalOceanServerProvider::class,
    ];

    /**
     * 单例绑定对应关系
     */
    public $singletons = [
        DowntimeNotifier::class => PingdomDowntimeNotifier::class,
    ];

}
```

#### 3.延迟加载

<p>
对于业务类的服务提供者，一般不需要在应用启动时就对其进行加载处理，只有在使用到它的时候才需要。
</p>

```
class DemoServiceProvider extends ServiceProvider {

    /**
     * 标记着服务提供者是延迟加载的
     */
    protected $defer = true;

    /**
     * 注册方法，会在容器内被调用
     */
    public function register() {
        $this->app->singleton(Connection::class, function ($app) {
            return new Connection($app['config']['riak']);
        });
    }

    /**
     * 获取服务提供者提供的服务
     */
    public function provides() {
        return [Connection::class];
    }

}
```

#### 4.事件加载

<p>
事件加载时对延迟加载的扩展处理，加载延迟服务器的时机不一定是使用了它，而可能是某个事件触发了我们需要对其进行加载，以实现功能需求。
</p>

```
class DemoServiceProvider extends ServiceProvider {

    /**
     * 标记着服务提供者是延迟加载的
     */
    protected $defer = true;

    /**
     * 注册方法，会在容器内被调用
     */
    public function register() {
        $this->app->singleton(Connection::class, function ($app) {
            return new Connection($app['config']['riak']);
        });
    }

    /**
     * 获取服务提供者提供的服务
     */
    public function provides() {
        return [Connection::class];
    }

    /**
     * 获取触发加载的事件
     * @return array
     */
    public function when() {
        return [];
    }

}
```

#### 5.启动方法

<p>
有时我们希望在服务提供者加载后，可以执行某些额外的处理，可以借助于boot方法。
</p>

```
class DemoServiceProvider extends ServiceProvider {

    /**
     * 服务提供者加载后的处理方法
     * 此时应用已标记启动，所有的服务提供者都已加载
     */
    public function boot() {
        //额外处理方法
        view()->composer('view', function () {
            //
        });
    }

}
```

## 配置服务提供者

<p>
当我们有了服务提供者，需要怎么配置到程序里呢，从而可以让框架对其进行操作。
</p>

<p>
应用中的服务提供者都是在配置文件config/app.php的providers中。
</p>

```
'providers' => [
    //自定义的服务提供者
    App\Providers\ComposerServiceProvider::class,
],
```

## 即时加载流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/2018-06-24_190501.png?raw=true)

## 延迟加载流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/2018-06-24_192339.png?raw=true)
