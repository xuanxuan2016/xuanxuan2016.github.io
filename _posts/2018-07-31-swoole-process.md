---
layout:     post
title:      "swoole的进程构成"
subtitle:   "swoole process"
date:       2018-07-31 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - swoole
---

> 介绍swoole模块中，进程的构成。

## 概述

<p>
swoole的进程由master进程，manager进程，worker进程，task进程组成。
</p>

<p>
master与manager进程只会有一个，worker与task进程根据配置可能会产生多个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731153032.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731153049.png?raw=true)

## worker_num

<p>
设置启动的Worker进程数，默认为SWOOLE_CPU_NUM（逻辑cpu个数）。最好为cpu的1-4倍，不超过100倍。
</p>

#### 1.不设置worke_num

<p>
不手动设置worker进程数，默认为逻辑cpu的个数。
</p>

<p>
逻辑cpu为1，加上master与manager总共为3个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731161604.png?raw=true)

<p>
逻辑cpu为4，加上master与manager总共为6个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731164732.png?raw=true)

#### 2.设置worke_num

<p>
手动启动3个worker进程，加上master与manager总共为5个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731162049.png?raw=true)

<p>
手动启动6个worker进程，加上master与manager总共为8个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731162300.png?raw=true)

## task_worker_num

<p>
设置启动的Task进程数，默认不启动，需要自己手动配置。
</p>

##### 设置worke_num=3，task_worker_num=3

<p>
worker与task进程，加上master与manager总共为8个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731175826.png?raw=true)

##### 设置worke_num=3，task_worker_num=6

<p>
worker与task进程，加上master与manager总共为11个。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180731175838.png?raw=true)

## worker进程与task进程的区别

<p>
从上面可以看出worker进程与task进程是并列的，task进程并不属于某个worker进程，那为什么有了worker还需要task呢？
</p>

<p>
swoole中任务的处理流程如下图，服务器接收到客户端的处理请求可以直接在worker中进行处理，如果处理的耗时较长，可将任务异步投递到空闲的task中处理（如果任务异步处理的话），这样worker就可以接收新的客户端处理请求，从而提高了服务器处理任务的速度。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180805111411.png?raw=true)

<p>
为了验证实际情况是不是如上面说的，进行如下测试，服务端开启3个worker，2个task。
</p>

- taskworker：当前工作进程为task进程还是worker进程
- work_id：当前工作进程的编号[0-(worker_num+task_num-1)]，按worker+task顺序编号
- work_pid：当前工作进程对应的系统进程id
- src_worker_id：当前task进程处理的任务来自哪个worker进程
- task_id：swoole自动生成的任务编号，src_worker_id+task_id为全局唯一

[服务端代码](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/server.php)

[客户端代码](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/client.php)

[测试结果](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/2018-08-05INFO.log)

#### 测试结果

<p>
多个worker会调用相同的task处理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180805201910.png?raw=true)

<p>
相同的worker会调用不同的task处理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-07-31-swoole-process/20180805202449.png?raw=true)

## 参考资料

[Linux查看物理CPU个数、核数、逻辑CPU个数](https://www.cnblogs.com/bugutian/p/6138880.html)

[swoole worker_num](https://wiki.swoole.com/wiki/page/275.html)

[task_worker_num](https://wiki.swoole.com/wiki/page/276.html)

[swoole_server->task](https://wiki.swoole.com/wiki/page/134.html)

[swoole_server->taskwait](https://wiki.swoole.com/wiki/page/p-server/taskwait.html)
