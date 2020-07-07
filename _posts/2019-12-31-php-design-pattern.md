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

#### 工厂模式(创建型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_1.png?raw=true)

```
/**
 * The Creator class declares the factory method that is supposed to return an
 * object of a Product class. The Creator's subclasses usually provide the
 * implementation of this method.
 */
abstract class Creator
{
    /**
     * Note that the Creator may also provide some default implementation of the
     * factory method.
     */
    abstract public function factoryMethod(): Product;

    /**
     * Also note that, despite its name, the Creator's primary responsibility is
     * not creating products. Usually, it contains some core business logic that
     * relies on Product objects, returned by the factory method. Subclasses can
     * indirectly change that business logic by overriding the factory method
     * and returning a different type of product from it.
     */
    public function someOperation(): string
    {
        // Call the factory method to create a Product object.
        $product = $this->factoryMethod();
        // Now, use the product.
        $result = "Creator: The same creator's code has just worked with " .
            $product->operation();

        return $result;
    }
}

/**
 * Concrete Creators override the factory method in order to change the
 * resulting product's type.
 */
class ConcreteCreator1 extends Creator
{
    /**
     * Note that the signature of the method still uses the abstract product
     * type, even though the concrete product is actually returned from the
     * method. This way the Creator can stay independent of concrete product
     * classes.
     */
    public function factoryMethod(): Product
    {
        return new ConcreteProduct1;
    }
}

class ConcreteCreator2 extends Creator
{
    public function factoryMethod(): Product
    {
        return new ConcreteProduct2;
    }
}

/**
 * The Product interface declares the operations that all concrete products must
 * implement.
 */
interface Product
{
    public function operation(): string;
}

/**
 * Concrete Products provide various implementations of the Product interface.
 */
class ConcreteProduct1 implements Product
{
    public function operation(): string
    {
        return "{Result of the ConcreteProduct1}";
    }
}

class ConcreteProduct2 implements Product
{
    public function operation(): string
    {
        return "{Result of the ConcreteProduct2}";
    }
}

/**
 * The client code works with an instance of a concrete creator, albeit through
 * its base interface. As long as the client keeps working with the creator via
 * the base interface, you can pass it any creator's subclass.
 */
function clientCode(Creator $creator)
{
    // ...
    echo "Client: I'm not aware of the creator's class, but it still works.\n"
        . $creator->someOperation();
    // ...
}

/**
 * The Application picks a creator's type depending on the configuration or
 * environment.
 */
echo "App: Launched with the ConcreteCreator1.\n";
clientCode(new ConcreteCreator1);
echo "\n\n";

echo "App: Launched with the ConcreteCreator2.\n";
clientCode(new ConcreteCreator2);
```

#### 单例模式(创建型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_2.png?raw=true)

#### 注册器模式(创建型)

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

#### 原型模式(创建型)

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

#### 适配器模式(结构型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_3.png?raw=true)

```
/**
 * The Target defines the domain-specific interface used by the client code.
 */
class Target
{
    public function request(): string
    {
        return "Target: The default target's behavior.";
    }
}

/**
 * The Adaptee contains some useful behavior, but its interface is incompatible
 * with the existing client code. The Adaptee needs some adaptation before the
 * client code can use it.
 */
class Adaptee
{
    public function specificRequest(): string
    {
        return ".eetpadA eht fo roivaheb laicepS";
    }
}

/**
 * The Adapter makes the Adaptee's interface compatible with the Target's
 * interface.
 */
class Adapter extends Target
{
    private $adaptee;

    public function __construct(Adaptee $adaptee)
    {
        $this->adaptee = $adaptee;
    }

    public function request(): string
    {
        return "Adapter: (TRANSLATED) " . strrev($this->adaptee->specificRequest());
    }
}

/**
 * The client code supports all classes that follow the Target interface.
 */
function clientCode(Target $target)
{
    echo $target->request();
}

echo "Client: I can work just fine with the Target objects:\n";
$target = new Target;
clientCode($target);
echo "\n\n";

$adaptee = new Adaptee;
echo "Client: The Adaptee class has a weird interface. See, I don't understand it:\n";
echo "Adaptee: " . $adaptee->specificRequest();
echo "\n\n";

echo "Client: But I can work with it via the Adapter:\n";
$adapter = new Adapter($adaptee);
clientCode($adapter);
```


#### 装饰器模式(结构型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_4.png?raw=true)

```
/**
 * The base Component interface defines operations that can be altered by
 * decorators.
 */
interface Component
{
    public function operation(): string;
}

/**
 * Concrete Components provide default implementations of the operations. There
 * might be several variations of these classes.
 */
class ConcreteComponent implements Component
{
    public function operation(): string
    {
        return "ConcreteComponent";
    }
}

/**
 * The base Decorator class follows the same interface as the other components.
 * The primary purpose of this class is to define the wrapping interface for all
 * concrete decorators. The default implementation of the wrapping code might
 * include a field for storing a wrapped component and the means to initialize
 * it.
 */
class Decorator implements Component
{
    /**
     * @var Component
     */
    protected $component;

    public function __construct(Component $component)
    {
        $this->component = $component;
    }

    /**
     * The Decorator delegates all work to the wrapped component.
     */
    public function operation(): string
    {
        return $this->component->operation();
    }
}

/**
 * Concrete Decorators call the wrapped object and alter its result in some way.
 */
class ConcreteDecoratorA extends Decorator
{
    /**
     * Decorators may call parent implementation of the operation, instead of
     * calling the wrapped object directly. This approach simplifies extension
     * of decorator classes.
     */
    public function operation(): string
    {
        return "ConcreteDecoratorA(" . parent::operation() . ")";
    }
}

/**
 * Decorators can execute their behavior either before or after the call to a
 * wrapped object.
 */
class ConcreteDecoratorB extends Decorator
{
    public function operation(): string
    {
        return "ConcreteDecoratorB(" . parent::operation() . ")";
    }
}

/**
 * The client code works with all objects using the Component interface. This
 * way it can stay independent of the concrete classes of components it works
 * with.
 */
function clientCode(Component $component)
{
    // ...

    echo "RESULT: " . $component->operation();

    // ...
}

/**
 * This way the client code can support both simple components...
 */
$simple = new ConcreteComponent;
echo "Client: I've got a simple component:\n";
clientCode($simple);
echo "\n\n";

/**
 * ...as well as decorated ones.
 *
 * Note how decorators can wrap not only simple components but the other
 * decorators as well.
 */
$decorator1 = new ConcreteDecoratorA($simple);
$decorator2 = new ConcreteDecoratorB($decorator1);
echo "Client: Now I've got a decorated component:\n";
clientCode($decorator2);
```

#### 代理模式(结构型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_5.png?raw=true)

```
/**
 * The Subject interface declares common operations for both RealSubject and the
 * Proxy. As long as the client works with RealSubject using this interface,
 * you'll be able to pass it a proxy instead of a real subject.
 */
interface Subject
{
    public function request(): void;
}

/**
 * The RealSubject contains some core business logic. Usually, RealSubjects are
 * capable of doing some useful work which may also be very slow or sensitive -
 * e.g. correcting input data. A Proxy can solve these issues without any
 * changes to the RealSubject's code.
 */
class RealSubject implements Subject
{
    public function request(): void
    {
        echo "RealSubject: Handling request.\n";
    }
}

/**
 * The Proxy has an interface identical to the RealSubject.
 */
class Proxy implements Subject
{
    /**
     * @var RealSubject
     */
    private $realSubject;

    /**
     * The Proxy maintains a reference to an object of the RealSubject class. It
     * can be either lazy-loaded or passed to the Proxy by the client.
     */
    public function __construct(RealSubject $realSubject)
    {
        $this->realSubject = $realSubject;
    }

    /**
     * The most common applications of the Proxy pattern are lazy loading,
     * caching, controlling the access, logging, etc. A Proxy can perform one of
     * these things and then, depending on the result, pass the execution to the
     * same method in a linked RealSubject object.
     */
    public function request(): void
    {
        if ($this->checkAccess()) {
            $this->realSubject->request();
            $this->logAccess();
        }
    }

    private function checkAccess(): bool
    {
        // Some real checks should go here.
        echo "Proxy: Checking access prior to firing a real request.\n";

        return true;
    }

    private function logAccess(): void
    {
        echo "Proxy: Logging the time of request.\n";
    }
}

/**
 * The client code is supposed to work with all objects (both subjects and
 * proxies) via the Subject interface in order to support both real subjects and
 * proxies. In real life, however, clients mostly work with their real subjects
 * directly. In this case, to implement the pattern more easily, you can extend
 * your proxy from the real subject's class.
 */
function clientCode(Subject $subject)
{
    // ...

    $subject->request();

    // ...
}

echo "Client: Executing the client code with a real subject:\n";
$realSubject = new RealSubject;
clientCode($realSubject);

echo "\n";

echo "Client: Executing the same client code with a proxy:\n";
$proxy = new Proxy($realSubject);
clientCode($proxy);
```

#### 策略模式(行为型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_6.png?raw=true)

```
/**
 * The Context defines the interface of interest to clients.
 */
class Context
{
    /**
     * @var Strategy The Context maintains a reference to one of the Strategy
     * objects. The Context does not know the concrete class of a strategy. It
     * should work with all strategies via the Strategy interface.
     */
    private $strategy;

    /**
     * Usually, the Context accepts a strategy through the constructor, but also
     * provides a setter to change it at runtime.
     */
    public function __construct(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * Usually, the Context allows replacing a Strategy object at runtime.
     */
    public function setStrategy(Strategy $strategy)
    {
        $this->strategy = $strategy;
    }

    /**
     * The Context delegates some work to the Strategy object instead of
     * implementing multiple versions of the algorithm on its own.
     */
    public function doSomeBusinessLogic(): void
    {
        // ...

        echo "Context: Sorting data using the strategy (not sure how it'll do it)\n";
        $result = $this->strategy->doAlgorithm(["a", "b", "c", "d", "e"]);
        echo implode(",", $result) . "\n";

        // ...
    }
}

/**
 * The Strategy interface declares operations common to all supported versions
 * of some algorithm.
 *
 * The Context uses this interface to call the algorithm defined by Concrete
 * Strategies.
 */
interface Strategy
{
    public function doAlgorithm(array $data): array;
}

/**
 * Concrete Strategies implement the algorithm while following the base Strategy
 * interface. The interface makes them interchangeable in the Context.
 */
class ConcreteStrategyA implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        sort($data);

        return $data;
    }
}

class ConcreteStrategyB implements Strategy
{
    public function doAlgorithm(array $data): array
    {
        rsort($data);

        return $data;
    }
}

/**
 * The client code picks a concrete strategy and passes it to the context. The
 * client should be aware of the differences between strategies in order to make
 * the right choice.
 */
$context = new Context(new ConcreteStrategyA);
echo "Client: Strategy is set to normal sorting.\n";
$context->doSomeBusinessLogic();

echo "\n";

echo "Client: Strategy is set to reverse sorting.\n";
$context->setStrategy(new ConcreteStrategyB);
$context->doSomeBusinessLogic();
```

#### 观察者模式(行为型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_7.png?raw=true)

```
/**
 * PHP has a couple of built-in interfaces related to the Observer pattern.
 *
 * Here's what the Subject interface looks like:
 *
 * @link http://php.net/manual/en/class.splsubject.php
 *
 *     interface SplSubject
 *     {
 *         // Attach an observer to the subject.
 *         public function attach(SplObserver $observer);
 *
 *         // Detach an observer from the subject.
 *         public function detach(SplObserver $observer);
 *
 *         // Notify all observers about an event.
 *         public function notify();
 *     }
 *
 * There's also a built-in interface for Observers:
 *
 * @link http://php.net/manual/en/class.splobserver.php
 *
 *     interface SplObserver
 *     {
 *         public function update(SplSubject $subject);
 *     }
 */

/**
 * The Subject owns some important state and notifies observers when the state
 * changes.
 */
class Subject implements \SplSubject
{
    /**
     * @var int For the sake of simplicity, the Subject's state, essential to
     * all subscribers, is stored in this variable.
     */
    public $state;

    /**
     * @var \SplObjectStorage List of subscribers. In real life, the list of
     * subscribers can be stored more comprehensively (categorized by event
     * type, etc.).
     */
    private $observers;
    
    public function __construct()
    {
        $this->observers = new \SplObjectStorage;
    }

    /**
     * The subscription management methods.
     */
    public function attach(\SplObserver $observer): void
    {
        echo "Subject: Attached an observer.\n";
        $this->observers->attach($observer);
    }

    public function detach(\SplObserver $observer): void
    {
        $this->observers->detach($observer);
        echo "Subject: Detached an observer.\n";
    }

    /**
     * Trigger an update in each subscriber.
     */
    public function notify(): void
    {
        echo "Subject: Notifying observers...\n";
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }

    /**
     * Usually, the subscription logic is only a fraction of what a Subject can
     * really do. Subjects commonly hold some important business logic, that
     * triggers a notification method whenever something important is about to
     * happen (or after it).
     */
    public function someBusinessLogic(): void
    {
        echo "\nSubject: I'm doing something important.\n";
        $this->state = rand(0, 10);

        echo "Subject: My state has just changed to: {$this->state}\n";
        $this->notify();
    }
}

/**
 * Concrete Observers react to the updates issued by the Subject they had been
 * attached to.
 */
class ConcreteObserverA implements \SplObserver
{
    public function update(\SplSubject $subject): void
    {
        if ($subject->state < 3) {
            echo "ConcreteObserverA: Reacted to the event.\n";
        }
    }
}

class ConcreteObserverB implements \SplObserver
{
    public function update(\SplSubject $subject): void
    {
        if ($subject->state == 0 || $subject->state >= 2) {
            echo "ConcreteObserverB: Reacted to the event.\n";
        }
    }
}

/**
 * The client code.
 */

$subject = new Subject;

$o1 = new ConcreteObserverA;
$subject->attach($o1);

$o2 = new ConcreteObserverB;
$subject->attach($o2);

$subject->someBusinessLogic();
$subject->someBusinessLogic();

$subject->detach($o2);

$subject->someBusinessLogic();
```

#### 迭代器模式(行为型)

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

##### Refactoring.Guru

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-31-php-design-pattern/tu_7.png?raw=true)

```
/**
 * Concrete Iterators implement various traversal algorithms. These classes
 * store the current traversal position at all times.
 */
class AlphabeticalOrderIterator implements \Iterator
{
    /**
     * @var WordsCollection
     */
    private $collection;

    /**
     * @var int Stores the current traversal position. An iterator may have a
     * lot of other fields for storing iteration state, especially when it is
     * supposed to work with a particular kind of collection.
     */
    private $position = 0;

    /**
     * @var bool This variable indicates the traversal direction.
     */
    private $reverse = false;

    public function __construct($collection, $reverse = false)
    {
        $this->collection = $collection;
        $this->reverse = $reverse;
    }

    public function rewind()
    {
        $this->position = $this->reverse ?
            count($this->collection->getItems()) - 1 : 0;
    }

    public function current()
    {
        return $this->collection->getItems()[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        $this->position = $this->position + ($this->reverse ? -1 : 1);
    }

    public function valid()
    {
        return isset($this->collection->getItems()[$this->position]);
    }
}

/**
 * Concrete Collections provide one or several methods for retrieving fresh
 * iterator instances, compatible with the collection class.
 */
class WordsCollection implements \IteratorAggregate
{
    private $items = [];

    public function getItems()
    {
        return $this->items;
    }

    public function addItem($item)
    {
        $this->items[] = $item;
    }

    public function getIterator(): Iterator
    {
        return new AlphabeticalOrderIterator($this);
    }

    public function getReverseIterator(): Iterator
    {
        return new AlphabeticalOrderIterator($this, true);
    }
}

/**
 * The client code may or may not know about the Concrete Iterator or Collection
 * classes, depending on the level of indirection you want to keep in your
 * program.
 */
$collection = new WordsCollection;
$collection->addItem("First");
$collection->addItem("Second");
$collection->addItem("Third");

echo "Straight traversal:\n";
foreach ($collection->getIterator() as $item) {
    echo $item . "\n";
}

echo "\n";
echo "Reverse traversal:\n";
foreach ($collection->getReverseIterator() as $item) {
    echo $item . "\n";
}
```

## 参考资料

[大话PHP设计模式](https://www.imooc.com/learn/236)

[设计模式](https://github.com/beautymyth/skilltree/blob/master/design%20pattern/summary.md)

[设计模式图文讲解](https://refactoringguru.cn/design-patterns/catalog)