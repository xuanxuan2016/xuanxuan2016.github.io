---
layout:     post
title:      "Swoole学习（一）"
subtitle:   "入门指引"
date:       2019-09-23 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - swoole
---

## 基础知识

<p>
学习swoole涉及的一些基础知识。
</p>

#### 多进程/多线程

- 了解Linux操作系统进程和线程的概念
- 了解Linux进程/线程切换调度的基本知识
- 了解进程间通信的基本知识，如管道、UnixSocket、消息队列、共享内存

[进程、线程和协程的理解](https://www.cnblogs.com/guolei2570/p/8810536.html)

[多线程和多进程的区别](https://blog.csdn.net/hairetz/article/details/4281931)

[Unix / Linux 线程的实质](https://my.oschina.net/cnyinlinux/blog/367910)

[Linux的进程线程及调度](https://www.cnblogs.com/leisure_chn/p/10393707.html)

#### SOCKET

- 了解SOCKET的基本操作如accept/connect、send/recv、close、listen、bind
- 了解SOCKET的接收缓存区、发送缓存区、阻塞/非阻塞、超时等概念

#### IO复用

- 了解select/poll/epoll
- 了解基于select/epoll实现的事件循环，Reactor模型
- 了解可读事件、可写事件

[并发编程(IO多路复用)](https://www.cnblogs.com/cainingning/p/9556642.html)

[事件驱动模型 select poll epoll](https://www.cnblogs.com/sunlong88/articles/9033143.html#top)

[Linux select/poll和epoll实现机制对比](https://naotu.baidu.com/file/9d4b407036639e201cc0104cd126886f)

#### TCP/IP网络协议

- 了解TCP/IP协议
- 了解TCP、UDP传输协议

#### 调试工具

- 使用 gdb 调试Linux程序
- 使用 strace 跟踪进程的系统调用
- 使用 tcpdump 跟踪网络通信过程
- 其他Linux系统工具，如ps、lsof、top、vmstat、netstat、sar、ss等

## 程序骨架

#### 程序入口

<p>
在4.4或更高版本中，需要使用以下方式作为程序单一入口。
</p>

- Swoole\Server::start：多进程的Server程序
- Swoole\Process\Pool::start：多进程程序
- Swoole\Coroutine\Scheduler::start 或 Swoole\Coroutine\run：单进程协程程序 (相当于main函数)
- Swoole\Process::start：自定义的子进程程序

<p>
Swoole提供的各种组件，如go创建协程、Co::sleep睡眠函数等，应该只在对应程序约定的<code>回调函数、主函数</code>中使用。
</p>

<p>
因此在index.php中只允许有Swoole\Server::start等上述四种方式启动。底层允许在index.php使用多次启动器。
</p>

## 快速起步

<p>
介绍swoole的常用使用案例。
</p>

#### 服务器

- [TCP服务器](https://wiki.swoole.com/wiki/page/476.html)
- [UDP服务器](https://wiki.swoole.com/wiki/page/477.html)
- [Web服务器](https://wiki.swoole.com/wiki/page/478.html)
- [WebSocket服务器](https://wiki.swoole.com/wiki/page/479.html)

#### 客户端

- [同步TCP客户端](https://wiki.swoole.com/wiki/page/482.html)
- [异步TCP客户端](https://wiki.swoole.com/wiki/page/483.html)
- [协程客户端](https://wiki.swoole.com/wiki/page/1005.html)

#### 协程

- [协程客户端](https://wiki.swoole.com/wiki/page/1005.html)：借助异步IO，提升系统的并发能力
- [并发 shell_exec](https://wiki.swoole.com/wiki/page/1017.html)：用co::exec并发地执行很多命令
- [Go + Chan + Defer](https://wiki.swoole.com/wiki/page/p-csp.html#entry_h2_0)
- [实现 Go 语言风格的 defer](https://wiki.swoole.com/wiki/page/p-go_defer.html)
- [实现 sync.WaitGroup 功能](https://wiki.swoole.com/wiki/page/p-waitgroup.html)

#### 其它

- [设置定时器](https://wiki.swoole.com/wiki/page/480.html)
- [执行异步任务](https://wiki.swoole.com/wiki/page/481.html)
- [多进程共享数据](https://wiki.swoole.com/wiki/page/836.html)

## 编程需知

<p>
介绍异步编程与同步编程的不同之处以及需要注意的事项。
</p>

#### 概要

##### 注意事项

- 不要在代码中执行sleep以及其他睡眠函数，这样会导致整个进程阻塞
- exit/die是危险的，会导致Worker进程退出
- 可通过register_shutdown_function来捕获致命错误，在进程异常退出时做一些清理工作([具体参考](https://wiki.swoole.com/wiki/page/305.html))
- PHP代码中如果有异常抛出，必须在回调函数中进行try/catch捕获异常，否则会导致工作进程退出
- 不支持set_exception_handler，必须使用try/catch方式处理异常
- Worker进程不得共用同一个Redis或MySQL等网络服务客户端，Redis/MySQL创建连接的相关代码可以放到onWorkerStart回调函数中([具体参考](https://wiki.swoole.com/wiki/page/325.html))

##### 异步编程

<p>
异步编程就是将避免IO阻塞或使用异步IO，来最大化接受请求，提高CPU的利用率。
</p>

- 异步程序要求代码中不得包含任何同步阻塞操作
- 异步与同步代码不能混用，一旦应用程序使用了任何同步阻塞的代码，程序即退化为同步模式

##### 协程编程

<p>
协程是跑在单线程上顺序执行的程序，主要思想是利用协程相关的类库，来讲同步IO变为异步IO。
</p>

<p>
使用Coroutine特性，请认真阅读<a target='_blank' href='https://wiki.swoole.com/wiki/page/851.html'>协程编程须知</a>
</p>

##### 并发编程

<p>
请务必注意与同步阻塞模式不同，异步和协程模式下程序是并发执行的，在同一时间内Server会存在多个请求，因此应用程序必须为每个客户端或请求，创建不同的资源和上下文。否则不同的客户端和请求之间可能会产生数据和逻辑错乱。
</p>

##### 类/函数重复定义

<p>
新手非常容易犯这个错误，由于Swoole是常驻内存的，所以加载类/函数定义的文件后不会释放。因此引入类/函数的php文件时必须要使用include_once或require_once，否则会发生cannot redeclare function/class 的致命错误。
</p>

<p>
最好使用<code>spl_autoload_register('self::autoLoad');</code>来加载文件，可以很好的避免此问题。
</p>

##### 内存管理

<p>
PHP守护进程与普通Web程序的变量生命周期、内存管理方式完全不同。请参考<a target='_blank' href='https://wiki.swoole.com/wiki/page/p-zend_mm.html'>Server内存管理</a>页面。编写Server或其他常驻进程时需要特别注意。
</p>

##### 进程隔离

<p>
Swoole\Server程序的不同Worker进程之间是隔离的，在编程时操作全局变量、定时器、事件监听，仅在当前进程内有效。请参考<a target='_blank' href='https://wiki.swoole.com/wiki/page/1038.html'>进程隔离</a>
</p>

<p>
Swoole提供的Table、Atomic、Lock组件是可以用于多进程编程的，但必须在Server->start之前创建。另外Server维持的TCP客户端连接也可以跨进程操作，如Server->send和Server->close。
</p>

#### 具体注意点

- [sleep/usleep的影响](https://wiki.swoole.com/wiki/page/500.html)
- [exit/die函数的影响](https://wiki.swoole.com/wiki/page/501.html)
- [while循环的影响](https://wiki.swoole.com/wiki/page/502.html)
- [stat缓存清理](https://wiki.swoole.com/wiki/page/676.html)
- [mt_rand随机数](https://wiki.swoole.com/wiki/page/732.html)
- [进程隔离](https://wiki.swoole.com/wiki/page/1038.html)
- [捕获异常和错误](https://wiki.swoole.com/wiki/page/1521.html)

## php.ini选项

<p>
通过修改一些配置来更好的使用swoole。
</p>


配置 | 说明
---|---
swoole.enable_coroutine | On, Off 开关内置协程, 默认开启
swoole.aio_thread_num | 设置AIO异步文件读写的线程数量，默认为2
swoole.display_errors | 关闭/开启Swoole错误信息，默认开启
swoole.socket_buffer_size | 设置进程间通信的Socket缓存区尺寸，默认为8M

## 内核参数调整

[参考此处](https://wiki.swoole.com/wiki/page/p-server/sysctl.html)

## 同步阻塞与异步非阻塞适用场景

#### 异步的优势

- 高并发，同步阻塞IO模型的并发能力依赖于进程/线程数量，例如 php-fpm开启了200个进程，理论上最大支持的并发能力为200。如果每个请求平均需要100ms，那么应用程序就可以提供2000qps。异步非阻塞的并发能力几乎是无限的，可以发起或维持大量并发TCP连接
- 无IO等待，同步模型无法解决IOWait很高的场景，如上述例子每个请求平均要10s，那么应用程序就只能提供20qps了。而异步程序不存在IO等待，所以无论请求要花费多长时间，对整个程序的处理能力没有任何影响

#### 同步的优势

- 编码简单，同步模式编写/调试程序更轻松
- 可控性好，同步模式的程序具有良好的过载保护机制，如在下面的情况异步程序就会出问题
- Accept保护，同步模式下一个TCP服务器最大能接受 进程数+Backlog 个TCP连接。一旦超过此数量，Server将无法再接受连接，客户端会连接失败。避免服务器Accept太多连接，导致请求堆积

## 参考资料

[入门指引](https://wiki.swoole.com/wiki/page/1.html)

[CPU密集型、IO密集型](https://blog.csdn.net/youanyyou/article/details/78990156)