---
layout:     post
title:      "php设计模式"
subtitle:   "php design pattern"
date:       2019-12-31 20:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
---

> 介绍php中常用的设计模式

## 面向对象

#### 基本原则

- 单一职责：一个类，只需要做好一件事情
- 开放封闭：一个类，应该是可扩展的，而不可修改的
- 依赖倒置：一个类，不应该强依赖另外一个类。每个类对于另外一个类都是可替换的
- 配置化：尽可能地使用配置，而不是硬编码
- 面向接口编程：只需要关系接口，不需要关系实现

#### 基础知识

##### spl标准库

<p>
SPL是用于解决典型问题(standard problems)的一组接口与类的集合。
</p>

- SplStack-栈
- SplQueue-队列
- SplHeap-堆
- SplFixedArray-固定长度数组

##### 链式操作

<p>
用于对对象中方法的连续操作，在方法用使用【return $this】实现。
</p>

##### 魔术方法

<p>
当执行对象的不存在的方法时，默认的处理方法。PHP 将所有以 __（两个下划线）开头的类方法保留为魔术方法。所以在定义类方法时，除了上述魔术方法，建议不要以 __ 为前缀。
</p>

- __get/__set:对象属性的处理
- __call/__callStatic:对象方法的调用
- __toString:将对象转换为字符串
- __invoke:将对象当成方法来使用

## 设计模式

[代码位置](https://github.com/xuanxuan2016/easyswoole2/tree/master/App/DesignPattern)

#### 工厂模式

<p>
通过工厂方法或者类生成对象，而不是在代码中直接new。
</p>

```
class Service1 {

    public function run() {
        echo "service1 \n";
    }

}

class Service2 {

    public function run() {
        echo "service2 \n";
    }

}

/**
 * 工厂类
 */
class Factory {

    public static function getService1() {
        return new Service1();
    }

    public static function getService2() {
        return new Service2();
    }

}

//运行
Factory::getService1()->run();
Factory::getService2()->run();
```

#### 单例模式

<p>
某个类的对象仅允许创建一个。
</p>

```
/**
 * 单例复用
 */
trait Singleton {

    private static $objInstance;

    /**
     * 获取对象实例
     */
    public static function getInstance(...$arrParam) {
        if (!isset(self::$objInstance)) {
            self::$objInstance = new static(...$arrParam);
        }
        return self::$objInstance;
    }

}

/**
 * 单例
 */
class Service1 {

    use Singleton;

    protected $intId;

    /**
     * 私有化构造函数
     */
    private function __construct(...$arrParam) {
        $this->intId = $arrParam[0];
    }

    public function run() {
        echo "{$this->intId} \n";
        $this->intId++;
    }

}

//运行
Service1::getInstance(1)->run();
Service1::getInstance(1)->run();
```

#### 注册器模式

<p>
通过注册器来管理全局对象。
</p>

```
/**
 * 注册器
 */
class Register {

    private static $arrObj = [];

    /**
     * 获取对象
     */
    public static function get($strKey) {
        return isset(self::$arrObj[$strKey]) ? self::$arrObj[$strKey] : null;
    }

    /**
     * 保存对象
     */
    public static function set($strKey, $obj) {
        self::$arrObj[$strKey] = $obj;
    }

}

class Service1 {

    public function run() {
        echo "service1 \n";
    }

}

//运行
Register::set('service1', new Service1());

Register::get('service1')->run();
Register::get('service1')->run();
```

#### 适配器模式

<p>
将截然不同的函数接口封装成统一的API，可简单理解为接口+接口的实现。
</p>

```
/**
 * 缓存适配器
 */
interface CacheAdapter {

    /**
     * 连接
     */
    public function connect();

    /**
     * 获取
     */
    public function get();

    /**
     * 关闭
     */
    public function close();
}

/**
 * redis缓存
 */
class CacheRedis implements CacheAdapter {

    public function connect() {
        
    }

    public function get() {
        echo "redis get \n";
    }

    public function close() {
        
    }

}

/**
 * memcache缓存
 */
class MemcacheRedis implements CacheAdapter {

    public function connect() {
        
    }

    public function get() {
        echo "memcache get \n";
    }

    public function close() {
        
    }

}

//运行
(new CacheRedis())->get();
(new MemcacheRedis())->get();
```

#### 策略模式

<p>
将一组特定的行为和算法封装成类，以适应某些特定的上下文环境。
</p>

```
/**
 * 用户策略
 */
interface UserStrategy {

    /**
     * 广告推荐
     */
    public function showAd();
}

/**
 * 男士策略
 */
class MaleStrategy implements UserStrategy {

    public function showAd() {
        echo "男士广告 \n";
    }

}

/**
 * 女士策略
 */
class FemaleStrategy implements UserStrategy {

    public function showAd() {
        echo "女士广告 \n";
    }

}

/**
 * 商品页面
 */
class Page {

    private $objStrategy;

    public function setStrategy($objStrategy) {
        $this->objStrategy = $objStrategy;
    }

    public function show() {
        $this->objStrategy->showAd();
    }

}

//运行
$objPage = new Page();

$objPage->setStrategy(new MaleStrategy());
$objPage->show();

$objPage->setStrategy(new FemaleStrategy());
$objPage->show();
```

#### 数据对象映射模式
#### 观察者模式

<p>
当一个对象的状态发生改变时，依赖它的对象全部会收到通知，并自动更新。可实现低耦合，非侵入式的通知与更新机制。
</p>

```
/**
 * 观察者接口
 */
interface Observer {

    /**
     * 更新
     */
    public function update();
}

/**
 * 事件生成抽象类
 */
abstract class Event {

    /**
     * 观察者集合
     */
    private $arrObserver = [];

    /**
     * 添加观察者
     */
    public function addObserver($objObserver) {
        $this->arrObserver[] = $objObserver;
    }

    /**
     * 通知观察者
     */
    public function notify() {
        foreach ($this->arrObserver as $objObserver) {
            $objObserver->update();
        }
    }

}

class Observer1 implements Observer {

    public function update() {
        echo "Observer1 \n";
    }

}

class Observer2 implements Observer {

    public function update() {
        echo "Observer2 \n";
    }

}

class UserEvent extends Event {
    
}

//运行
$objUserEvent = new UserEvent();
$objUserEvent->addObserver(new Observer1());
$objUserEvent->addObserver(new Observer2());
$objUserEvent->notify();
```

#### 原型模式

<p>
与工厂模式类似，用来创建对象。与工厂模式的实现不同，原型模式是先创建好一个原型对象，然后通过clone原型对象来创建新的对象。这样就免去了类创建时重复的初始化操作。原型模式适用于大对象的创建。创建一个大对象需要很大的开销，如果每次new就会消耗很大，原型模式仅需内存拷贝即可。
</p>

```
/**
 * 原型
 */
class Prototype {

    private static $arrObj = [];

    public static function getService1() {
        if (!isset(self::$arrObj['service1'])) {
            self::$arrObj['service1'] = new Service1();
        }
        //克隆对象
        return clone self::$arrObj['service1'];
    }

}

class Service1 {

    private $intTime1;
    private $intTime2;

    /**
     * 构造函数
     * 1.执行很多初始化操作
     */
    public function __construct() {
        $this->intTime1 = time();
    }

    public function change() {
        $this->intTime2 = microtime();
        return $this;
    }

    public function run() {
        echo "time1:{$this->intTime1} time2:{$this->intTime2} \n";
    }

}

//运行
Prototype::getService1()->change()->run();
Prototype::getService1()->change()->run();
```

#### 装饰器模式

<p>
动态的添加修改类的功能。一个类提供了一项功能，如果要修改并添加额外的功能，传统的编程模式，需要一个子类来继承它，并重新实现类的方法。使用装饰器模式，仅需在运行时添加一个装饰器对象即可。
</p>

```
/**
 * 装饰器接口
 */
interface Decorator {

    /**
     * 主逻辑执行前
     */
    public function before();

    /**
     * 主逻辑执行后
     */
    public function after();
}

class Decorator1 implements Decorator {

    public function before() {
        echo "Decorator1 before \n";
    }

    public function after() {
        echo "Decorator1 after \n";
    }

}

class Decorator2 implements Decorator {

    public function before() {
        echo "Decorator2 before \n";
    }

    public function after() {
        echo "Decorator2 after \n";
    }

}

class Service1 {

    private $arrDecorator = [];

    public function addDecorator($objDecorator) {
        $this->arrDecorator[] = $objDecorator;
    }

    protected function before() {
        foreach ($this->arrDecorator as $objDecorator) {
            $objDecorator->before();
        }
    }

    protected function after() {
        foreach (array_reverse($this->arrDecorator) as $objDecorator) {
            $objDecorator->after();
        }
    }

    public function run() {
        $this->before();
        echo "Service1 run \n";
        $this->after();
    }

}

//运行
$objService1 = new Service1();
$objService1->addDecorator(new Decorator1());
$objService1->addDecorator(new Decorator2());
$objService1->run();
```

#### 迭代器模式

<p>
在不需要了解内部实现的前提下，遍历一个聚合对象的内部。相比于传统模式，迭代器模式可以隐藏遍历元素所需的操作。
</p>

```
class Service1 implements \Iterator {

    protected $intIndex = 0;
    protected $arrData = [];

    public function __construct() {
        $this->intIndex = 0;
        $this->arrData = [1, 2, 3, 4, 5];
    }

    /**
     * 返回当前元素
     */
    public function current() {
        return $this->arrData[$this->intIndex];
    }

    /**
     * 返回当前元素的键
     */
    public function key() {
        return $this->intIndex;
    }

    /**
     * 向前移动到下一个元素
     */
    public function next() {
        $this->intIndex++;
    }

    /**
     * 返回到迭代器的第一个元素
     */
    public function rewind() {
        $this->intIndex = 0;
    }

    /**
     * 检查当前位置是否有效
     */
    public function valid() {
        return $this->intIndex < count($this->arrData);
    }

}

//运行
$objService1 = new Service1();
foreach ($objService1 as $value) {
    echo $value;
}
```

#### 代理模式

<p>
在客户端与实体之间建立一个代理对象（proxy），客户端对实体进行操作全部委派给代理对象，隐藏实体的具体实现细节。Proxy还可以与业务代码分离，部署到另外的服务器。业务代码中通过RPC来委派任务。
</p>

```
class ServiceProxy {

    public function run() {
        //复杂逻辑
        echo "ServiceProxy\n";
    }

}

class Service1 {

    private $objProxy;

    public function __construct() {
        $this->objProxy = new ServiceProxy();
    }

    public function run() {
        $this->objProxy->run();
    }

}

//运行
$objService1 = new Service1();
$objService1->run();
```

## 参考资料

[大话PHP设计模式](https://www.imooc.com/learn/236)

[设计模式](https://github.com/beautymyth/skilltree/blob/master/design%20pattern/summary.md)
