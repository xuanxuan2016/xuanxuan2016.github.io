---
layout:     post
title:      "Swoole学习（四）"
subtitle:   "Memory"
date:       2019-10-17 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - swoole
---

## 概要

- Memory下的模块可以安全的用于异步非阻塞程序中，不存在任何IO消耗
- 所有模块均为多进程安全的，无需担心数据同步问题
- Memory相关模块对象为有限资源，不可大量创建

## Table

<p>
<code>Table</code>一个基于共享内存和锁实现的超高性能，并发数据结构。用于解决多进程/多线程数据共享和同步加锁问题。
</p>

<blockquote>
  <p>请谨慎使用数组方式读写<code>Table</code>, 建议使用文档中提供的<code>API</code>来进行操作<br>
  数组方式取出的<code>Swoole\Table\Row</code>对象为一次性对象, 请勿依赖其进行过多操作</p>
</blockquote>

#### 方法

方法名 | 说明 | 链接
---|---|---
__construct | 创建内存表 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/254.html'>详情</a>
column | 内存表增加一列 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/256.html'>详情</a>
create | 创建内存表，之后才能使用 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/257.html'>详情</a>
set | 设置行的数据 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/258.html'>详情</a>
incr | 原子自增操作 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/419.html'>详情</a>
decr | 原子自减操作 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/420.html'>详情</a>
get | 获取一行数据 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/259.html'>详情</a>
exist | 检查table中是否存在某一个key | <a target='_blank' href='https://wiki.swoole.com/wiki/page/443.html'>详情</a>
count | 返回table中存在的条目数 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/928.html'>详情</a>
del | 删除数据 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/260.html'>详情</a>

<p>
使用场景：
</p>

- 限流控制
- 多进程共享数据

## Atomic

<p>
<code>Atomic</code>是<code>Swoole</code>底层提供的原子计数操作类，可以方便整数的无锁原子增减。
</p>

<ul>
<li>使用共享内存，可以在不同的进程之间操作计数</li>
<li>基于<code>gcc/clang</code>提供的<code>CPU</code>原子指令，无需加锁</li>
<li>在服务器程序中必须在<code>Server-&gt;start</code>前创建才能在<code>Worker</code>进程中使用</li>
<li>默认使用<code>32</code>位无符号类型，如需要<code>64</code>有符号整型，可使用<code>Swoole\Atomic\Long</code></li>
</ul>

<p>
<strong>
注意：请勿在<code>onReceive</code>等回调函数中创建原子数，否则底层的<code>GlobalMemory</code>内存会持续增长，造成内存泄漏。
</strong>
</p>

<p>
使用场景：
</p>

- 限流控制

#### 方法

方法名 | 说明 | 链接
---|---|---
__construct | 创建一个原子计数对象 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/465.html'>详情</a>
add | 增加计数 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/466.html'>详情</a>
sub | 减少计数 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/467.html'>详情</a>
get | 获取当前计数的值 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/468.html'>详情</a>
set | 将当前值设置为指定的数字 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/471.html'>详情</a>
cmpset | 比较设置 | <a target='_blank' href='https://wiki.swoole.com/wiki/page/469.html'>详情</a>


## Lock

## 参考资料

[内存操作模块](https://wiki.swoole.com/wiki/page/245.html)

