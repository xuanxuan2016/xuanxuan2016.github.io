---
layout:     post
title:      "RabbitMQ学习（四）-web管理页面"
subtitle:   "web-management"
date:       2018-05-08 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍如何配置与使用RabbitMQ的web管理页面。

## 配置网页插件

#### 1.启用插件

需要使用到<code>rabbitmq-plugins</code>命令。

```linux
[root@vagrant otp_src_20.3]# rabbitmq-plugins enable rabbitmq_management
```
查看是否启用成功，<code>15672</code>端口是否处于监听状态。

```linux
[root@vagrant otp_src_20.3]# netstat -anp|grep 15672
tcp        0      0 0.0.0.0:15672               0.0.0.0:*                   LISTEN      29468/beam.smp      
tcp        0      0 10.100.255.115:15672        10.100.255.1:59372          ESTABLISHED 29468/beam.smp    
```

如果需要关闭的话，可使用如下命令。

```linux
[root@vagrant otp_src_20.3]# rabbitmq-plugins disable rabbitmq_management
```

#### 2.网站初体验

可通过<code>ip:15672</code>的形式来访问网站。
<br>
![image](https://github.com/beautymyth/beautymyth.github.io/blob/master/img/2018-05-08-4-rabbitmq-study-web-management/pzwycj-1.png?raw=true)

如果访问不了，可尝试使用如下一些解决方法。
- 使用域名
<br>
本地虚拟机，直接通过ip访问不了，在<code>C:\Windows\System32\drivers\etc\hosts</code>中增加如下配置：
<br>
<code>10.100.255.115 test.51job.com</code>
<br>
之后使用<code>test.51job.com:15672</code>访问。

- 开启端口号
<br>
<code>iptables -I INPUT -p tcp --dport 15672 -j ACCEPT</code>
 
- 配置插件使用目录
<br>
<code>mkdir /etc/rabbitmq</code>

#### 3.用户添加与权限设置

刚刚看到了网站，可惜没有账号密码怎么办呢，请往下看。

```linux
#新增一个账号(admin)，并设置密码(admin)
[root@vagrant otp_src_20.3]# rabbitmqctl add_user admin admin
#设置账号的目录，读写权限
[root@vagrant otp_src_20.3]# rabbitmqctl set_permissions -p / admin ".*" ".*" ".*"
#设置账号的角色，可有哪些操作权限
[root@vagrant otp_src_20.3]# rabbitmqctl set_user_tags admin administrator
```

#### 4.网站再体验

使用刚刚设置的账号与密码，就可以登录进来了。
<br>
![image](https://github.com/beautymyth/beautymyth.github.io/blob/master/img/2018-05-08-4-rabbitmq-study-web-management/pzwycj-2.png?raw=true)

## 网页插件功能说明

#### 1.Overview-概览

##### Totals：总况

![image](https://github.com/beautymyth/beautymyth.github.io/blob/master/img/2018-05-08-4-rabbitmq-study-web-management/overview-queued-messages.png?raw=true)



##### Nodes：节点

##### Ports and contexts

##### Export definitions

##### Import definitions

#### 2.Connections-连接

#### 3.Channels-信道

#### 4.Exchanges-交换器

#### 5.Queues-队列

#### 6.Admin-账号管理