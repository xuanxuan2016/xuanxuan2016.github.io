---
layout:     post
title:      "EasySwoole学习-rpc"
subtitle:   "rpc"
date:       2019-12-18 14:30
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
easyswoole中rpc的使用redis的hash结构来存储不同服务的可用节点，使用自定义进程（workprocess）开启socket监听等待客户端的socket调用，使用定时器（tickprocess）来广播本地服务节点或接收外部（udp socket）的服务节点。
</p>

## 类关系

- rpc服务端：用于接收服务请求，维护服务的节点
- rpc服务：具体的rpc服务，用于实际的业务功能
- 节点管理器：用于获取/删除服务节点，更新节点的心跳时间
- WrokerProcess：服务进程，开启socket的listen等待外部请求
- TickProcess：更新本服务节点的心跳，定时对外广播本地服务节点，监听广播更新其他服务节点
- rpc客户端：用于发起rpc请求

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-18-easyswoole-study-rpc/tu_1.png?raw=true)

## 调用流程

#### 服务端

<p>
开启rpc服务的流程
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-18-easyswoole-study-rpc/tu_2.png?raw=true)

#### 客户端

<p>
客户端请求rpc服务的流程
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-18-easyswoole-study-rpc/tu_3.png?raw=true)

## 参考资料

[EasySwoole RPC](http://www.easyswoole.com/Components/Rpc/introduction.html)

[注释版代码](https://github.com/xuanxuan2016/easyswoole2)

[Socket通信原理](https://www.cnblogs.com/wangcq/p/3520400.html)

[socket原理详解](https://www.cnblogs.com/zengzy/p/5107516.html)