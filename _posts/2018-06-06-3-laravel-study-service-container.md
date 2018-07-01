---
layout:     post
title:      "laravel学习（三）-服务容器"
subtitle:   "Service Container"
date:       2018-06-06 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - laravel
---

> 介绍laravel框架中服务容器的主要使用方法。

## 文档

[英文文档](https://laravel.com/docs/5.6/container)

[中文文档](https://laravel-china.org/docs/laravel/5.6/container/1359)

## 绑定

#### 1.bind

<p>
此方法是最常用的服务绑定方法。
</p>

##### 函数说明

```
/**
 * Register a binding with the container.
 * 绑定服务到容器
 * @param  string  $abstract 类别名，实际类名，接口类名
 * @param  \Closure|string|null  $concrete 类的构建闭包，实际类名，null=>$concrete=$abstract
 * @param  bool  $shared 是否为共享服务
 * @return void
 */
function bind($abstract, $concrete = null, $shared = false) {
    
}
```

##### 典型应用

```
//类别名绑定到构建闭包
$this->app->bind('cache', function ($app) {
    return new CacheManager($app);
});
```

```
//类别名绑定到实际类名
$this->app->bind('cache', 'Illuminate\Cache\CacheManager');
```

```
//接口类名绑定到实际类名
$this->app->bind('App\Contracts\EventPusher', 'App\Services\RedisEventPusher');
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/2018-06-23_083158.png?raw=true)

#### 2.singleton

<p>
此方法可将服务绑定为共享服务。
</p>

##### 函数说明

```
/**
 * Register a shared binding in the container.
 * 绑定单例服务到容器
 * @param  string  $abstract 类别名，实际类名，接口类名
 * @param  \Closure|string|null  $concrete 类的构建闭包，实际类名，null=>$concrete=$abstract
 * @return void
 */
function singleton($abstract, $concrete = null) {
    
}
```

##### 典型应用

```
//类别名绑定到构建闭包，服务为共享的
$this->app->singleton('cache', function ($app) {
    return new CacheManager($app);
});
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180623073829.png?raw=true)

#### 3.instance

<p>
此方法可将服务的实例直接绑定到容器的共享服务中。
</p>

##### 函数说明

```
/**
 * Register an existing instance as shared in the container.
 * 绑定抽象类型的实例为共享实例
 * @param  string  $abstract 类别名，实际类名，接口类名
 * @param  mixed   $instance 实例
 * @return mixed
 */
function instance($abstract, $instance) {
    
}
```

##### 典型应用

```
//类实例化
$api = new HelpSpot\API(new HttpClient);
//实例绑定
$this->app->instance('HelpSpot\API', $api);
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180622182322.png?raw=true)

#### 4.when

<p>
此方法可进行服务的上下文绑定，需要配合ContextualBindingBuilder中的needs与give使用，在解析时使用give代替needs。
</p>

##### 函数说明

```
/**
 * Define a contextual binding.
 * 创建上下文绑定
 * @param  string  $concrete 实际类名
 * @return \Illuminate\Contracts\Container\ContextualBindingBuilder
 */
function when($concrete) {
}

/**
 * Define the abstract target that depends on the context.
 * 上下文依赖的抽象类型
 * @param  string  $abstract 类别名，实际类名，接口类名，变量名
 * @return $this
 */
function needs($abstract) {
    
}

/**
 * Define the implementation for the contextual binding.
 * 抽象类型的实现
 * @param  \Closure|string  $implementation 实现闭包，字符串
 * @return void
 */
function give($implementation) {
    
}
```


##### 典型应用

```
//绑定构造函数中简单参数的初始数据，在解析when类的时用到
$this->app->when('App\Http\Controllers\UserController')
          ->needs('$variableName')
          ->give($value);
```

```
//绑定构造函数中类参数的实现，在解析when类的依赖类时用到

//如下2个绑定表示，在解析PhotoController::class与VideoController::class时都需要依赖Filesystem::class，但是根据不同的功能，给予了Filesystem::class不同的实现
$this->app->when(PhotoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('local');
          });

$this->app->when(VideoController::class)
          ->needs(Filesystem::class)
          ->give(function () {
              return Storage::disk('s3');
          });
          
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180622173206.png?raw=true)

#### 5.tag

<p>
此方法可将多个服务进行标记，在解析时可以使用tag同时解析多个服务。
</p>

##### 函数说明

```
/**
 * Assign a set of tags to a given binding.
 * 为一组绑定设定标记
 * @param  array|string  $abstracts 抽象类型
 * @param  array|mixed   ...$tags 需要设定的标记
 * @return void
 */
function tag($abstracts, $tags) {
    
}
```

##### 典型应用

```
$this->app->bind('SpeedReport', function () {
    //
});

$this->app->bind('MemoryReport', function () {
    //
});

$this->app->tag(['SpeedReport', 'MemoryReport'], 'reports');
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180622162919.png?raw=true)

#### 6.extend

<p>
此方法可对解析后的服务实例添加扩展方法，用于对实例进行装饰处理。
</p>

##### 函数说明

```
/**
 * "Extend" an abstract type in the container.
 * 扩展容器中的服务
 * @param  string    $abstract 抽象类型
 * @param  \Closure  $closure 闭包
 * @return void
 *
 * @throws \InvalidArgumentException
 */
function extend($abstract, Closure $closure) {
    
}
```

##### 典型应用

```
//对服务的实例进行装饰处理
$this->app->extend(Service::class, function($service) {
    return new DecoratedService($service);
});
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180622160956.png?raw=true)


## 解析

#### 1.make(app)

<p>
最常用的从容器中解析实例的方法。
</p>

##### 函数说明

```
/**
 * Resolve the given type from the container.
 * 从服务容器中解析服务
 * (Overriding Container::make)
 * @param  string  $abstract 类别名，实际类名，接口类名
 * @param  array  $parameters 类依赖的参数
 * @return mixed
 */
public function make($abstract, array $parameters = []) {
}
```

##### 典型应用

```
$api = $this->app->make('HelpSpot\API');
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/2018-06-23_152549.png?raw=true)

#### 2.makeWith(container)

<p>
在解析类时，可直接传入类的依赖项，而不需要通过容器去解析。
</p>

##### 函数说明

```
/**
 * An alias function name for make().
 * <br>make方法的别名
 * @param  string  $abstract 类别名，实际类名，接口类名
 * @param  array  $parameters 类依赖的参数
 * @return mixed
 */
public function makeWith($abstract, array $parameters = []) {
}
```

##### 典型应用

```
$api = $this->app->makeWith('HelpSpot\API', ['id' => 1]);
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180623105813.png?raw=true)

#### 3.resolve(helpers)

<p>
如果访问不到$app，可通过此全局辅助函数解析实例。
</p>

##### 函数说明

```
if (! function_exists('resolve')) {
    /**
     * Resolve a service from the container.
     * 从容器中解析类
     * @param  string  $name 抽象类型
     * @return mixed
     */
    function resolve($name)
    {
        return app($name);
    }
}

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     * 从容器中解析实例
     * @param  string  $abstract 类别名，实际类名，接口类名
     * @param  array   $parameters 类依赖的参数
     * @return mixed|\Illuminate\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            //如果没有抽象类型，返回容器实例
            return Container::getInstance();
        }
        //解析抽象类型
        return Container::getInstance()->make($abstract, $parameters);
    }
}
```

##### 典型应用

```
$api = resolve('HelpSpot\API');
```

##### 流程图

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-06-06-2-laravel-study-service-container/20180623105253.png?raw=true)

## 事件

<p>
注册解析服务时触发的全局事件或服务的事件，可在服务提供者的boot方法中注册。
</p>

```
public function boot()
{
    $this->app->resolving(function ($object, $app) {
        //注册全局解析方法，解析任何类型时都会触发
    });
    
    $this->app->resolving(HelpSpot\API::class, function ($api, $app) {
        //注册类型解析方法，解析对应类型时才会触发
    });
}
```

<p>
应用的启动在BootProviders中的bootstrap方法里。
</p>

```
public function bootstrap(Application $app)
{
    $app->boot();
}
```



## 注释版代码

[Application](https://github.com/beautymyth/laravelframework/blob/master/vendor/laravel/framework/src/Illuminate/Foundation/Application.php)

[Container](https://github.com/beautymyth/laravelframework/blob/master/vendor/laravel/framework/src/Illuminate/Container/Container.php)
