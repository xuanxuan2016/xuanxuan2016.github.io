---
layout:     post
title:      "RabbitMQ学习-遇到的问题"
subtitle:   "problems"
date:       2018-05-08 18:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - rabbitmq
---

> 介绍在使用过程中碰到的一些问题及相应的解决方法。

## rabbit服务启动问题

#### 1.openssl未支持

<p>
此问题出现在公司电脑的虚拟机上，在启动rabbit时出现如下错误。
</p>

```linux
[root@vagrant bmsource]# rabbitmq-server start


Error description:
   {error,{missing_dependencies,[crypto,ssl],
                                [cowboy,cowlib,rabbitmq_management,
                                 rabbitmq_management_agent,
                                 rabbitmq_trust_store]}}

Log files (may contain more information):
   /usr/local/rabbitmq/var/log/rabbitmq/rabbit@vagrant.log
   /usr/local/rabbitmq/var/log/rabbitmq/rabbit@vagrant-sasl.log

Stack trace:
   [{rabbit_plugins,ensure_dependencies,1,
                    [{file,"src/rabbit_plugins.erl"},{line,185}]},
    {rabbit_plugins,prepare_plugins,1,
                    [{file,"src/rabbit_plugins.erl"},{line,203}]},
    {rabbit,broker_start,0,[{file,"src/rabbit.erl"},{line,300}]},
    {rabbit,start_it,1,[{file,"src/rabbit.erl"},{line,424}]},
    {init,start_em,1,[]},
    {init,do_boot,3,[]}]

{"init terminating in do_boot",{error,{missing_dependencies,[crypto,ssl],[cowboy,cowlib,rabbitmq_management,rabbitmq_management_age}
init terminating in do_boot ({error,{missing_dependencies,[crypto,ssl],[cowboy,cowlib,rabbitmq_management,rabbitmq_management_agent)

Crash dump is being written to: erl_crash.dump...done
```

<p>
查询发现服务器上没有安装openssl的扩展，安装openssl后，重新编译安装erlang。
</p>

```linux
[root@vagrant bmsource]# yum install openssl openssl-devel
[root@vagrant bmsource]# rm -rf otp_src_20.3
[root@vagrant bmsource]# tar -xvf otp_src_20.3.tar.gz 
[root@vagrant bmsource]# cd otp_src_20.3
[root@vagrant otp_src_20.3]# ./configure --prefix=/usr/local/erlang
[root@vagrant otp_src_20.3]# make && make install
```

<p>
启动rabbit，查看状态正常。
<br>
-detached：表示已守护进程的方式运行。
</p>

```linux
[root@vagrant otp_src_20.3]# rabbitmq-server -detached
Warning: PID file not written; -detached was passed.
[root@vagrant otp_src_20.3]# rabbitmqctl status
Status of node rabbit@vagrant
[{pid,29468},
 {running_applications,
     [{rabbit,"RabbitMQ","3.6.15"},
      {ranch,"Socket acceptor pool for TCP protocols.","1.3.2"},
      {ssl,"Erlang/OTP SSL application","8.2.4"},
      {public_key,"Public key infrastructure","1.5.2"},
      {asn1,"The Erlang ASN1 compiler version 5.0.5","5.0.5"},
      {crypto,"CRYPTO","4.2.1"},
      {rabbit_common,
          "Modules shared by rabbitmq-server and rabbitmq-erlang-client",
          "3.6.15"},
      {xmerl,"XML parser","1.3.16"},
      {recon,"Diagnostic tools for production use","2.3.2"},
      {os_mon,"CPO  CXC 138 46","2.4.4"},
      {compiler,"ERTS  CXC 138 10","7.1.5"},
      {mnesia,"MNESIA  CXC 138 12","4.15.3"},
      {syntax_tools,"Syntax tools","2.1.4"},
      {sasl,"SASL  CXC 138 11","3.1.1"},
      {stdlib,"ERTS  CXC 138 10","3.4.4"},
      {kernel,"ERTS  CXC 138 10","5.4.3"}]},
 {os,{unix,linux}},
 {erlang_version,
     "Erlang/OTP 20 [erts-9.3] [source] [64-bit] [smp:1:1] [ds:1:1:10] [async-threads:64] [hipe] [kernel-poll:true]\n"},
```

#### 2.hosts配置问题

<p>
此问题出现在阿里云机器上，在启动rabbit时出现如下错误。
</p>

```linux
[root@i-wz9i8fd8lio2yh3oeriz bmsource]# rabbitmq-server -detached
Warning: PID file not written; -detached was passed.
ERROR：epmd error for host i-wz9i8fd8lio2yh3oeriz：timeout（time out）
```
<p>
查看错误信息，推断可能跟hosts有关系，查询<code>/etc/hosts</code>，发现有奇怪的一行，注释掉先。
</p>

```linux
#10.116.9.118 i-wz9i8fd8lio2yh3oeriz
```
<p>
启动rabbit，可正常启动。
</p>

```linux
[root@i-wz9i8fd8lio2yh3oeriz bmsource]# rabbitmq-server -detached
Warning: PID file not written; -detached was passed.
[root@i-wz9i8fd8lio2yh3oeriz bmsource]#
```

#### 3.Erlang Cookie问题

<p>
此问题出现在搭建集群时，在关闭重启rabbit时出现如下错误。
</p>

```
[root@DEV-mHRO64 bmsource]# rabbitmqctl stop
Stopping and halting node 'rabbit@DEV-mHRO64'
Error: unable to connect to node 'rabbit@DEV-mHRO64': nodedown

DIAGNOSTICS
===========

attempted to contact: ['rabbit@DEV-mHRO64']

rabbit@DEV-mHRO64:
  * connected to epmd (port 4369) on DEV-mHRO64
  * epmd reports node 'rabbit' running on port 25672
  * TCP connection succeeded but Erlang distribution failed

  * Authentication failed (rejected by the remote node), please check the Erlang cookie


current node details:
- node name: 'rabbitmq-cli-23@DEV-mHRO64'
- home dir: /root
- cookie hash: r5tor8XZxXSjsNTj8qfTyg==

```

<p>
根据错误信息联想，先启动了Rabbit服务，然后将Cookie更新为rabbitmq_node1上的Cookie了，所以验证不了导致错误。
</p>

<p>
没找到其他可用方法，所以通过将进程杀掉的方法来解决。
</p>

```
[root@DEV-mHRO64 bmsource]# ps -aux|grep rabbit
Warning: bad syntax, perhaps a bogus '-'? See /usr/share/doc/procps-3.2.8/FAQ
root     12581  0.1  1.6 3851948 66348 ?       Sl   May24   1:59 /usr/local/erlang/lib/erlang/erts-9.3/bin/beam.smp -W w -A 64 -P 1048576 -t 5000000 -stbt db -zdbbl 128000 -K true -- -root /usr/local/erlang/lib/erlang -progname erl -- -home /root -- -pa /usr/local/rabbitmq/ebin -noshell -noinput -s rabbit boot -sname rabbit@DEV-mHRO64 -boot start_sasl -kernel inet_default_connect_options [{nodelay,true}] -sasl errlog_type error -sasl sasl_error_logger false -rabbit error_logger {file,"/usr/local/rabbitmq/var/log/rabbitmq/rabbit@DEV-mHRO64.log"} -rabbit sasl_error_logger {file,"/usr/local/rabbitmq/var/log/rabbitmq/rabbit@DEV-mHRO64-sasl.log"} -rabbit enabled_plugins_file "/usr/local/rabbitmq/etc/rabbitmq/enabled_plugins" -rabbit plugins_dir "/usr/local/rabbitmq/plugins" -rabbit plugins_expand_dir "/usr/local/rabbitmq/var/lib/rabbitmq/mnesia/rabbit@DEV-mHRO64-plugins-expand" -os_mon start_cpu_sup false -os_mon start_disksup false -os_mon start_memsup false -mnesia dir "/usr/local/rabbitmq/var/lib/rabbitmq/mnesia/rabbit@DEV-mHRO64" -kernel inet_dist_listen_min 25672 -kernel inet_dist_listen_max 25672 -noshell -noinput
root     23573  0.0  0.0 103248   888 pts/2    S+   13:42   0:00 grep rabbit
[root@DEV-mHRO64 bmsource]# kill -9 12581
```

<p>
测试可正常启动与关闭服务。
</p>

```
[root@DEV-mHRO64 bmsource]# rabbitmq-server -detached
Warning: PID file not written; -detached was passed.
You have mail in /var/spool/mail/root
[root@DEV-mHRO64 bmsource]# netstat -anp|grep 5672
tcp        0      0 0.0.0.0:25672               0.0.0.0:*                   LISTEN      29960/beam.smp 
tcp        0      0 :::5672                     :::*                        LISTEN      29960/beam.smp 
[root@DEV-mHRO64 bmsource]# netstat -anp|grep 5672
You have mail in /var/spool/mail/root
[root@DEV-mHRO64 bmsource]# 
```


## 消息推送接收问题

#### 1.生产者发送消费者没接收到

<p>
在demo示例中，程序一直是正常的，没改过任何东西，在终端运行时出现问题。
</p>

<p>
查询web控制台发现，队列被2个消费者绑定了，但是终端感觉只有一个，还有一个不知道是不是测试没处理好，导致一直在运行。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-2-rabbitmq-study-problems/20180519121651.png?raw=true)


<p>
处理方法只能在web控制台关闭连接了。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-2-rabbitmq-study-problems/20180519121158.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-05-08-2-rabbitmq-study-problems/20180519121122.png?raw=true)

## 参数绑定问题

#### 1.死信队列绑定参数失败

<p>
在测试死信队列时，想给queue绑定x-dead-letter-exchange，出现如下异常。
</p>

```
PHP Fatal error:  Uncaught exception 'Lib\PhpAmqpLib\Exception\AMQPProtocolChannelException' with message 'PRECONDITION_FAILED - inequivalent arg 'x-dead-letter-exchange' for queue 'queue_rpc_fibonacci' in vhost '/': received the value 'amq.direct' of type 'longstr' but current is none' in /vagrant/htdocs/RabbitMQStudy/Lib/PhpAmqpLib/Channel/AMQPChannel.php:188
```
<p>
大意是已存在的queue的x-dead-letter-exchange与想设置的queue的x-dead-letter-exchange不一致
</p>

<p>
解决方法：在web管理端发现已存在一个同名的queue且没有设置queue的x-dead-letter-exchange参数，删除已存在的queue或者新的queue取一个别的名字。
</p>

## 应用层问题思考

#### 1.消息重复消费

##### 场景

- 场景1：消费者从队列中获取到消息后，相关业务处理结束，但之后消费者异常，导致消息未确认消费。
- 场景2：消费者从队列中获取到消息后，消费者与服务器连接断开，导致消息未确认消费。


##### 解决思路

- 1.对消息增加全局唯一ID，在消费者消费后将id记录到redis
- 2.每次在消费之前检测redis是否存在id，存在不进行业务处理（可通知开发者），直接确认消费

##### 其它思考

- 1.因为消费者的异常可能出现在任何时候，所以感觉不能100%保证幂等性。
- 2.消息id记录到redis之后量会很大，可能考虑设置过期时间


#### 2.消费者死机

##### 场景

- 场景1：消费者获取到消息后，内部出现如死循环之类的bug，一直占用消息，导致消息不能被消费

##### 解决思路

- 1.在消费者的逻辑内，自己实现超时机制
- 2.在连接服务器时，增加心跳参数，让服务器可以主动断开连接，让消息重回队列

##### 其它思考

- 1.其中也可能会遇到重复消费的问题，可参考消息重复消费的解决方法

## 参考资料

[幂等性](https://www.cnblogs.com/javalyy/p/8882144.html)

[消息重复消费](https://www.jianshu.com/p/8d1c242872a4)
