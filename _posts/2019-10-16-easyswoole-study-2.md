---
layout:     post
title:      "EasySwoole学习（二）"
subtitle:   "连接池"
date:       2019-10-16 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - easy-swoole
---

## 概述

<p>
easyswoole的连接池主要使用了<code>Swoole\Coroutine\Channel（支持多生产者协程和多消费者协程）</code>来实现连接池对象的分配（出队列与入队列），如果连接池中没有可用对象，连接池会尝试创建新的对象（除非已经达到数量上限）。
</p>

## 类关系

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-10-16-easyswoole-study-2/tu_1.png?raw=true)

## 调用流程

<p>
绿色图示为类的方法。
</p>

##### 池管理类

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-10-16-easyswoole-study-2/tu_2.png?raw=true)

##### 池类

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-10-16-easyswoole-study-2/tu_3.png?raw=true)

## 参考资料

[easy swoole 连接池](http://www.easyswoole.com/Components/Pool/introduction.html)

[Swoole\Coroutine\Channel](https://wiki.swoole.com/wiki/page/p-coroutine_channel.html)

[注释版连接池代码](https://github.com/xuanxuan2016/easyswoole1/tree/master/vendor/easyswoole/component/src/Pool)