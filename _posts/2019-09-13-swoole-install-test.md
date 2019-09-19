---
layout:     post
title:      "swoole的安装"
subtitle:   "swoole install"
date:       2019-08-13 14:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - swoole
---

> 介绍swoole扩展的安装。

## 依赖

#### gcc 4.8

<p>
swoole最新版本需要<code>gcc-4.8+</code>，这里需要进行安装，如果以前有低版本的需要先卸载。
</p>

```
#卸载gcc
[root@localhost swoole-src-4.4.3]# yum remove gcc
```

```
#安装gcc

#1.设置yum源
[root@localhost swoole-src-4.4.3]# curl -Lks http://www.hop5.in/yum/el6/hop5.repo > /etc/yum.repos.d/hop5.repo
#2.安装
[root@localhost swoole-src-4.4.3]# yum install gcc gcc-c++ -y
#查看版本
[root@localhost swoole-src-4.4.3]# gcc --version
gcc (GCC) 4.8.2 20131212 (Red Hat 4.8.2-8)
[root@localhost swoole-src-4.4.3]# g++ --version
g++ (GCC) 4.8.2 20131212 (Red Hat 4.8.2-8)
```

## swoole

#### 资源位置

<p>
官方建议安装的话，最好选取最新的版本，在如下位置有所有可用版本，根据需要自行获取。
</p>

[资源](https://github.com/swoole/swoole-src/releases)

#### 安装

<p>
swoole的版本会依赖php的版本，这里机器php是7.0的所以安装了swoole-4.2.11版本。
</p>

```
[root@localhost bmsource]# wget https://github.com/swoole/swoole-src/archive/v4.2.11.tar.gz
[root@localhost bmsource]# tar xvf swoole-src-4.2.11.tar.gz
[root@localhost bmsource]# cd swoole-src-4.2.11
[root@localhost swoole-src-4.2.11]# phpize
[root@localhost swoole-src-4.2.11]# ./configure
[root@localhost swoole-src-4.2.11]# make && make install
[root@localhost swoole-src-4.2.11]# make install
Installing shared extensions:     /usr/lib64/php/modules/
Installing header files:          /usr/include/php/
```

```
#将swoole.so复制到php的扩展目录

#在php.ini添加引用
extension=swoole.so
```

## 参考资料

[编译安装](https://wiki.swoole.com/wiki/page/6.html)

[Linux之CentOS 6通过yum安装gcc 4.8版本gcc](https://blog.csdn.net/xiaominggunchuqu/article/details/78625994)

[Linux设置本地yum源](https://cloud.tencent.com/developer/article/1336558)