---
layout:     post
title:      "EasySwoole学习（一）"
subtitle:   "php新手教程"
date:       2019-08-13 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - easy-swoole
---

## php运行模式

#### web访问模式

##### CGI

<p>
通用网关接口（Common Gateway Interface）,允许web服务器通过特定的协议与应用程序通信。
</p>

<p>
调用过程：
</p>

- 用户请求
- Web服务器接收请求
- fork子进程，调用程序/执行程序
- 程序返回内容/程序调用结束
- web服务器接收内容
- 返回给用户

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-06-easyswoole-study-1/tu-1.png?raw=true)

<p>
CGI的缺点：
</p>

- 每次处理用户请求，都需要重新 fork CGI 子进程、销毁 CGI 子进程
- 一系列的 I/O 开销降低了网络的吞吐量，造成了资源的浪费，在大并发时会产生严重的性能问题

##### FastCGI

<p>
CGI的升级版，是一种常驻型的CGI协议（nginx+php-fpm），一旦开启后，就可一直处理请求，不需要每次都启动/结束进程。
</p>

<p>
cgi进程初始化：FastCGI 进程管理器启动时会创建一个主（Master） 进程和多个CGI 解释器进程（Worker 进程），然后等待Web服务器的连接。
</p>

<p>
调用过程：
</p>

- 用户请求
- web服务器接收请求
- 请求转发给进程管理器（php-fpm）
- 进程管理器（php-fpm）交给一个空闲进程处理
- 进程处理完成
- 进程管理器（php-fpm）返回给web服务器
- web服务器接收数据
- 返回给用户

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-06-easyswoole-study-1/tu-2.png?raw=true)

##### 模块模式

<p>
apache+php运行时,默认使用的是模块模式,它把php作为apache的模块随apache启动而启动,接收到用户请求时则直接通过调用mod_php模块进行处理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-06-easyswoole-study-1/tu-3.png?raw=true)

#### 后台模式

##### php-cli

<p>
php的命令行运行模式，通过<code>php test.php</code>方式来执行。
</p>

<p>
与web模式区别：
</p>

- 没有超时时间
- 默认关闭buffer缓冲
- STDIN和STDOUT标准输入/输出/错误 的使用
- echo var_dump,phpinfo等输出直接输出到控制台
- 可使用的类/函数不同
- php.ini配置的不同

## 网络协议

#### tcp

##### tcp

**三次握手**

<p>
TCP是因特网中的传输层协议，使用三次握手协议建立连接。当主动方发出SYN连接请求后，等待对方回答SYN+ACK ，并最终对对方的 SYN 执行 ACK 确认。这种建立连接的方法可以防止产生错误的连接，TCP使用的流量控制协议是可变大小的滑动窗口协议。 TCP三次握手的过程如下：
</p>

- 客户端发送SYN（SEQ=x）报文给服务器端，进入SYN_SEND状态。
- 服务器端收到SYN报文，回应一个SYN （SEQ=y）ACK(ACK=x+1）报文，进入SYN_RECV状态。
- 客户端收到服务器端的SYN报文，回应一个ACK(ACK=y+1）报文，进入Established状态。

##### http

**过程解析**

- 用户在浏览器输入www.baidu.com
- dns服务器解析/或者本机hosts,路由器hosts对比 获得ip
- 浏览器访问默认端口80,则访问的tcp地址为 ip:80
- tcp协议3次握手,建立连接
- 发送一个http request请求头
- 服务器获得http request请求头,表明该次访问为http访问,解析http请求头,获得请求类型,请求格式,以及请求数据(cookie,get,post数据)
- 服务器发送response响应数据,主动断开
- 浏览器接收response响应数据,解析响应文本类型,解析数据,断开连接

##### websocket

<p>
WebSocket协议是基于TCP的一种新的网络协议。它实现了浏览器与服务器全双工(full-duplex)通信——允许服务器主动发送信息给客户端。
</p>

## 会话管理

#### cookie

##### 存储

<p>
cookie存储在用户端(通常是浏览器端),可通过JavaScript脚本,服务端response头进行设置/修改/删除操作一个cookie。
</p>

- name：一个唯一确定的cookie名称,通常来讲cookie的名称是不区分大小写的。
- value：存储在cookie中的字符串值。
- domain：cookie对于哪个域是有效的。所有向该域发送的请求中都会包含这个cookie信息。这个值可以包含子域(如：
yq.aliyun.com)，也可以不包含它(如：.aliyun.com，则对于aliyun.com的所有子域都有效).
- path：表示这个cookie影响到的路径，浏览器跟会根据这项配置，像指定域中匹配的路径发送cookie。
- expires：失效时间，表示cookie何时应该被删除的时间戳(也就是，何时应该停止向服务器发送这个cookie)。如果不设置这个时间戳，浏览器会在页面关闭时即将删除所有cookie；不过也可以自己设置删除时间。这个值是GMT时间格式，如果客户端和服务器端时间不一致，使用expires就会存在偏差。
- max-age：与expires作用相同，用来告诉浏览器此cookie多久过期（单位是秒），而不是一个固定的时间点。正常情况下，max-age的优先级高于expires。
- HttpOnly：告知浏览器不允许通过脚本document.cookie去更改这个值，同样这个值在document.cookie中也不可见。但在http请求张仍然会携带这个cookie。注意这个值虽然在脚本中不可获取，但仍然在浏览器安装目录中以文件形式存在。这项设置通常在服务器端设置。
- secure：安全标志，指定后，只有在使用SSL链接时候才能发送到服务器，如果是http链接则不会传递该信息。就算设置了secure 属性也并不代表他人不能看到你机器本地保存的 cookie 信息，所以不要把重要信息放cookie就对了服务器端设置

##### 安全

- 服务端安全：cookie是存储在用户端的,可以被用户修改,所以服务端不能直接通过一个cookie来确定用户身份,需要用一定的方式加密或者对等存储(cookie作为凭证,在服务端记录对应数据),服务端session就是使用这种方法存储的
- 用户端安全：用户端的cookie安全的,网站以外的用户无法跨过网站来获取用户的cookie信息,但是有心之人可能会通过ajax方法,让用户访问A网站,却使用B网站的脚本进行敏感操作(跨站点脚本攻击)

#### session

<p>
根据会话id（php_session的cookie）将用户信息存储在服务器端，当会话过期或被放弃后，服务器将终止该会话。
</p>

#### api/token

<p>
token和session原理差不多,服务端通过给用户发送一个token（需要认证获取，可设定token有效时间,以及加密token,每隔一段时间变动一次token）,用户通过该token进行请求服务端,这种会话验证方式一般用于跨平台开发,以及接口开发。
</p>

## linux基础

#### 命令

<p>
linux命令存储以下位置：
</p>

- /bin(指向/usr/bin)目录：基本的用户命令,默认全体用户都可使用,例如curl,ls命令
- /sbin(指向/usr/sbin),/usr/local/sbin：root权限的命令以及工具,默认root用户使用,例如ip,halt命令
- /usr/local/bin：用户放置自己的可执行程序的地方,不会被系统升级覆盖
- /usr/local/sbin：管理员放置自己的可执行程序的地方,不会被系统升级覆盖

<p>
如果在每个命令目录都存在某个命令时,通过系统的<code>$PATH</code>变量决定优先级
</p>

```
#每台电脑输出不同
echo $PATH
/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/usr/local/protobuf/bin/:/root/bin
```

#### 进程管理

```
#查看进程
[root@iZwz9i8fd8lio2yh3oerizZ /]# ps -ef|grep httpd
root     11535 10886  0 18:14 pts/0    00:00:00 grep --color=auto httpd
root     21468     1  0 Aug29 ?        00:00:39 /www/bin/httpd -k start
nobody   21560 21468  0 Aug29 ?        00:05:31 /www/bin/httpd -k start
nobody   21561 21468  0 Aug29 ?        00:05:28 /www/bin/httpd -k start
nobody   21562 21468  0 Aug29 ?        00:05:22 /www/bin/httpd -k start
nobody   21650 21468  0 Aug29 ?        00:06:04 /www/bin/httpd -k start
```

```
#结束进程
[root@iZwz9i8fd8lio2yh3oerizZ /]# kill -9 21560
```

#### 端口监控

```
#查看端口占用情况
[root@iZwz9i8fd8lio2yh3oerizZ /]# netstat -anp|grep 80
```

```
#查看打开的文件

#列出所有tcp连接
[root@iZwz9i8fd8lio2yh3oerizZ /]# lsof -i tcp

#列出所有udp连接
[root@iZwz9i8fd8lio2yh3oerizZ /]# lsof -i udp

#列出使用80端口信息
[root@iZwz9i8fd8lio2yh3oerizZ /]# lsof -i:80
```

## php7

#### swoole使用的相关特性

##### 致命错误将可用异常形式抛出

<p>
在php7之后,大部分错误可通过异常形式抛出,并可使用catch拦截,例如:
</p>

```
try {
    //未定义该对象并没有该方法,抛出一个Throwable类
    $a->test();
} catch (Throwable $t) {
    var_dump($t->getMessage());
} catch (Exception $e) {
    
}
```

##### ?? null合并运算符

<p>
由于日常使用中存在大量同时使用三元表达式和 isset()的情况， php7添加了null合并运算符(??)这个语法糖。如果变量存在且值不为NULL， 它就会返回自身的值，否则返回它的第二个操作数。例如:
</p>

```
#如果为空字符串的话，会返回空字符串
$a='';
$b = $a ?? 1;
echo $b;
```

##### 标量类型声明

<p>
标量类型声明 有两种模式:强制(默认)和严格模式。现在可以使用下列类型参数（无论用强制模式还是严格模式）： 字符串(string), 整数 (int), 浮点数(float),以及布尔值(bool)。它们扩充了PHP5中引入的其他类型：类名，接口，数组和 回调类型。例如:
</p>

```
function a(
    ?int $a /*参数必须是int或者null*/,
    string $b/*参数必须string*/,
    Closure $function /*参数必须是匿名函数*/,
    array $array/*参数必须是数组*/
    ){}
```

##### 返回值类型声明

<p>
PHP 7 增加了对返回类型声明的支持。 类似于参数类型声明，返回类型声明指明了函数返回值的类型。可用的类型与参数声明中可用的类型相同。例如:
</p>

```
function a():int{//必须返回int类型,否则报错
    return 1;
}
function b():?int{//必须返回int类型或者null类型,否则报错
    return 'das';
}
```

##### 太空船操作符<=>（组合比较符）

<p>
太空船操作符用于比较两个表达式。当$a小于、等于或大于$b时它分别返回-1、0或1。 比较的原则是沿用 PHP 的常规比较规则进行的。
</p>

```
// Integers
echo 1 <=> 1; // 0
echo 1 <=> 2; // -1
echo 2 <=> 1; // 1
// Floats
echo 1.5 <=> 1.5; // 0
echo 1.5 <=> 2.5; // -1
echo 2.5 <=> 1.5; // 1
// Strings
echo "a" <=> "a"; // 0
echo "a" <=> "b"; // -1
echo "b" <=> "a"; // 1
```

## php回调/闭包

#### 回调事件

<p>
回调函数就是在主进程执行当中,突然跳转到预先设置好的函数中去执行的函数。
</p>

##### 函数名

```
function insert($i) {
    echo "插入数据{$i}\n"; //模拟数据库插入
    return true;
}

$arr = range(0, 1000); //模拟生成1001条数据

function action(array $arr, callable $function) {
    foreach ($arr as $value) {
        if ($value % 10 == 0) {//当满足条件时,去执行回调函数处理
            call_user_func($function, $value);
        }
    }
}

action($arr, 'insert');
```

##### 匿名函数

```
$arr = range(0, 1000); //模拟生成1001条数据

function action(array $arr, callable $function) {
    foreach ($arr as $value) {
        if ($value % 10 == 0) {//当满足条件时,去执行回调函数处理
            call_user_func($function, $value);
        }
    }
}

action($arr, function($i) {
    echo "插入数据{$i}\n"; //模拟数据库插入
    return true;
});
```

##### 类静态方法

```
$arr = range(0, 1000); //模拟生成1001条数据

function action(array $arr, callable $function) {
    foreach ($arr as $value) {
        if ($value % 10 == 0) {//当满足条件时,去执行回调函数处理
            call_user_func($function, $value);
        }
    }
}

class A {

    static function insert($i) {
        echo "插入数据{$i}\n"; //模拟数据库插入
        return true;
    }

}

action($arr, 'A::insert');
action($arr, ['A', 'insert']);
```

##### 类方法

```
$arr = range(0, 1000); //模拟生成1001条数据

function action(array $arr, callable $function) {
    foreach ($arr as $value) {
        if ($value % 10 == 0) {//当满足条件时,去执行回调函数处理
            call_user_func($function, $value);
        }
    }
}

class A {

    public function insert($i) {
        echo "插入数据{$i}\n"; //模拟数据库插入
        return true;
    }

}

$a = new A();
action($arr, [$a, 'insert']);
```

#### 闭包/匿名函数

##### 闭包的概念

<p>
闭包就是能够读取其他函数内部变量的函数。例如在javascript中，只有函数内部的子函数才能读取局部变量，所以闭包可以理解成“定义在一个函数内部的函数“。在本质上，闭包是将函数内部和函数外部连接起来的桥梁。 在php中,闭包函数一般就是匿名函数. 举例,有一个定时任务,每一秒执行一次,现在我们要开启一个服务,然后准备在10秒的时候关闭这个服务
</p>

```
function tick($callback) {
    while (1) {//简单实现的定时器,每秒都去执行一次回调
        if (call_user_func($callback) == false) {
            break;
        }
        sleep(1);
    }
}

class Server {

    private $intCount = 0;

    //模拟退出一个服务
    public function exitServer() {
        $this->intCount++;
        echo $this->intCount . PHP_EOL;
        if ($this->intCount == 10) {
            return false;
        } else {
            return true;
        }
    }

}

$server = new Server();
tick(function ()use($server) {
    return $server->exitServer();
});
```

##### 匿名函数

<p>
匿名函数 通俗来讲,就是没有名字的函数,例如上面写的function(){},它通常作为闭包函数使用,使用方法如下:
</p>

```
$fun = function($name){
    printf("Hello %s\r\n",$name);
};
echo $fun('Tioncico');
function a($callback){
    return $callback();
}
a(function (){
    echo "EasySwoole\n";
    return 1;
});
```

##### use

<p>
PHP在默认情况下，匿名函数不能调用所在代码块的上下文变量，而需要通过使用use关键字。
</p>

```
function a($callback){
    return $callback();
}
$str1 = "hello,";
$str2 = "Tioncico,";
a(function ()use($str1,$str2){
    echo $str1,$str2,"EasySwoole\n";
    return 1;
});
```

## php多进程

<p>
php多进程是在开发业务逻辑层面,并行处理多个任务的一种开发方式。
</p>

#### 开启多进程

##### swoole

<p>
swoole扩展是面向生产环境的PHP异步网络通信引擎,它也有着进程管理模块。
</p>

```
$num = 1;
$str = "EasySwoole,Easy学swoole\n";

$process = new swoole_process(function () use ($str) {//实例化一个进程类,传入回调函数
    echo $str;//变量内存照常复制一份,只不过swoole的开启子进程后使用的是回调方法运行
    echo "我是子进程,我的pid是" . getmypid() . "\n";
});
$pid = $process->start();//开启子进程,创建成功返回子进程的PID，创建失败返回false。
echo $str;
if ($pid > 0) {//主进程代码
    echo "我是主进程,子进程的pid是{$pid}\n";
}else{
    echo "我是主进程,我现在不慌了,失败就失败吧\n";
}
```

#### 僵死进程与孤儿进程

##### 僵死进程

<p>
一个子进程在其父进程还没有调用wait()或waitpid()的情况下退出。这个子进程就是僵尸进程。任何一个子进程(init除外)在exit()之后，并非马上就消失掉，而是留下一个称为僵尸进程(Zombie)的数据结构，等待父进程处理。这是每个子进程在结束时都要经过的阶段。如果子进程在exit()之后，父进程没有来得及处理，那么保留的那段信息就不会释放，其进程号就会一直被占用，但是系统所能使用的进程号是有限的，如果大量的产生僵尸进程，将因为没有可用的进程号而导致系统不能产生新的进程. 此即为僵尸进程的危害，应当避免。
</p>

##### 孤儿进程

<p>
一个父进程退出，而它的一个或多个子进程还在运行，那么那些子进程将成为孤儿进程。孤儿进程将被init进程(进程号为1)所收养，并由init进程对它们完成状态收集工作。孤儿进程是没有父进程的进程，孤儿进程这个重任就落到了init进程身上，init进程就好像是一个民政局，专门负责处理孤儿进程的善后工作。每当出现一个孤儿进程的时候，内核就把孤儿进程的父进程设置为init，而init进程会循环地wait()它的已经退出的子进程。这样，当一个孤儿进程凄凉地结束了其生命周期的时候，init进程就会代表党和政府出面处理它的一切善后工作。因此孤儿进程并不会有什么危害 。
</p>

#### 守护进程

<p>
守护进程(daemon)是一类在后台运行的特殊进程，用于执行特定的系统任务。很多守护进程在系统引导的时候启动，并且一直运行直到系统关闭。另一些只在需要的时候才启动，完成任务后就自动结束。
</p>

##### 特点

<p>
首先，守护进程最重要的特性是后台运行。其次，守护进程必须与其运行前的环境隔离开来。这些环境包括未关闭的文件描述符、控制终端、会话和进程组、工作目录以及文件创建掩码等。这些环境通常是守护进程从执行它的父进程(特别是shell)继承下来的。最后，守护进程的启动方式有其特殊之处。它可以在Linux系统启动时从启动脚本/etc/rc．d中启动，也可以由作业控制进程crond启动，还可以由用户终端(通常是shell)执行。
</p>

<p>
除这些以外，守护进程与普通进程基本上没有什么区别。因此，编写守护进样实际上是把一个普通进程按照上述的守护进程的特性改造成为守护进程。
</p>

##### 分类

<p>
按照服务类型分为如下几个。
</p>

- 系统守护进程：syslogd、login、crond、at等。
- 网络守护进程：sendmail、httpd、xinetd、等。
- 独立启动的守护进程：httpd、named、xinetd等。
- 被动守护进程（由xinetd启动）：telnet、finger、ktalk等。

## 同步/异步

- 同步：指调用某个逻辑时,会等待到该逻辑返回调用结果
- 异步：指调用某个逻辑时,不会等待该逻辑返回的结果,只会返回是否已经调用的最初结果(或不返回)

## 阻塞/非阻塞

#### 阻塞

<p>
阻塞往往是和"同步"概念一起存在的,例如查询数据库,获取文件数据,请求其他网站,等等,只要需要<code>消耗非进程本身执行时间并需要进程等待(同步)的</code>,都可以说是阻塞。
</p>

<p>
几乎所有的阻塞,都是与I/O有关。阻塞一定是同步代码调用阻塞函数才会阻塞,但同步代码不一定会阻塞(不调用阻塞函数的同步代码)。
</p>

#### 非阻塞

<p>
非阻塞,顾名思义,就是在进程在运行中,不存在阻塞情况,一直能往下执行。
</p>

<p>
非阻塞一般是指调用I/O操作时,进程无需等待I/O操作,直接往下执行的情况,非阻塞通常是和"异步"概念一起存在,只要是异步获取I/O,就一定是非阻塞。异步调用I/O一定是非阻塞的,但非阻塞不一定需要异步调用才可实现(非阻塞模型)。
</p>

## 协程

<p>
协程是指一种用代码实现任务交叉执行的逻辑，协程可以使得代码中不同的函数进行交叉运行，而不需要等待某个函数的IO执行完（协程切换后，原协程IO会继续执行），才执行下一个函数，从而可以加快整体程序的执行效率。
</p>

[协程执行流程](https://wiki.swoole.com/wiki/page/1029.html)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-09-06-easyswoole-study-1/tu-4.png?raw=true)

## 参考资料

[easy swoole 新手入门](https://www.easyswoole.com/Cn/NoobCourse/introduction.html)

[CGI 和 FastCGI 协议的运行原理](https://www.cnblogs.com/itbsl/archive/2018/10/22/9828776.html)

[PHP运行模式](https://blog.csdn.net/hguisu/article/details/7386882)

[PHP 的命令行模式](https://www.php.net/manual/zh/features.commandline.php)

[Unix / Linux 线程的实质](https://my.oschina.net/cnyinlinux/blog/367910)