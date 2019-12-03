---
layout:     post
title:      "EasySwoole学习（四）"
subtitle:   "composer autoload"
date:       2019-12-02 14:30
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
介绍通过composer来维护包文件的项目中，autoload的实现方式。
</p>

## 类关系

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-02-easyswoole-study-4/tu_1.png?raw=true)

## 调用流程

#### 注册流程

<p>
自动加载器的注册流程
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-02-easyswoole-study-4/tu_2.png?raw=true)

#### 加载流程

<p>
加载类时的加载流程
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-02-easyswoole-study-4/tu_3.png?raw=true)

## 参考资料

