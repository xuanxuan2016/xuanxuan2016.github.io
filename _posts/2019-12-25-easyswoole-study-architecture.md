---
layout:     post
title:      "EasySwoole学习-architecture"
subtitle:   "架构"
date:       2019-12-25 14:30
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
介绍easyswoole中与架构相关的代码流程。
</p>

## 核心架构

#### 生命周期

<p>
此图为从【easyswoole start】到【swoole start】的流程，显示了主要的代码调用过程及其中的触发事件的地方。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-25-easyswoole-study-architecture/tu_1.png?raw=true)

## 基础使用

#### 常用命令

##### start

```
php easyswoole start
```

<p>
见生命周期
</p>

##### stop

```
#启动（需要守护进程）
php easyswoole stop
```

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-25-easyswoole-study-architecture/tu_2.png?raw=true)

##### reload

```
#热重启（需要守护进程）
php easyswoole reload
```

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-25-easyswoole-study-architecture/tu_3.png?raw=true)

##### restart

```
#重启（需要守护进程）
php easyswoole restart
```

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-12-25-easyswoole-study-architecture/tu_4.png?raw=true)

## 参考资料

[EasySwoole 生命周期](http://www.easyswoole.com/Core/lifecycle.html)

[EasySwoole 自定义命令](http://www.easyswoole.com/BaseUsage/customCommand.html)

[Server->reload](https://wiki.swoole.com/wiki/page/20.html)

[注释版代码](https://github.com/xuanxuan2016/easyswoole2)

[SIGKILL和SIGTERM、SIGINT区别](https://blog.csdn.net/qq_26836575/article/details/82147558)

[Linux进程信号详解](https://blog.csdn.net/flowing_wind/article/details/79967588)