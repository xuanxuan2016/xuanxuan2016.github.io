---
layout:     post
title:      "es-环境准备"
subtitle:   "prepare-environment"
date:       2019-07-01 13:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍elk及相关依赖的安装。

## 依赖安装

#### jdk

<p>
因为使用的es版本为7.0.1，安装包会自带jdk，不需要额外安装。
</p>

##### 自行安装jdk

<p>
如果是低级版本的话，需要自行安装jdk。从<a href='https://www.oracle.com/technetwork/java/javase/downloads/jdk12-downloads-5295953.html' target='_blank'>官网</a>获取jdk安装包，这里选用<code>jdk-12.0.1</code>。
</p>

```
#解压安装包
[root@dev-interview1 bmsourse]# tar -xvf jdk-12.0.1_linux-x64_bin.tar.gz

[root@dev-interview1 bmsourse]# vim /bmsourse/elasticsearch-7.0.1/bin/elasticsearch

#在最上面增加环境变量
JAVA_HOME="/bmsourse/jdk-12.0.1/"
```

#### nodejs

<p>
为了可以使用第三方的工具，需要安装一下nodejs。
</p>

<p>
从<a href='https://nodejs.org/en/' target='_blank'>官网</a>获取nodejs安装包，这里选用<code>v10.16.0</code>。
</p>

```
#安装xz支持
[root@dev-interview1 bmsourse]# yum install xz

#解压安装包
[root@dev-interview1 bmsourse]# tar -xvf node-v10.16.0-linux-x64.tar.xz

#建立软连接
[root@dev-interview1 bmsourse]# ln -s /bmsource/node-v10.16.0-linux-x64/bin/npm /usr/bin/npm
[root@dev-interview1 bmsourse]# ln -s /bmsource/node-v10.16.0-linux-x64/bin/node /usr/bin/node
```

## ELK安装

#### ElasticSearch

##### 下载安装文件

[下载地址，选择需要版本](https://www.elastic.co/cn/downloads/past-releases#elasticsearch)

```
#获取文件
[root@dev-interview1 bmsourse]# wget https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-7.0.1-linux-x86_64.tar.gz

#解压文件
[root@dev-interview1 bmsourse]# tar -xvf elasticsearch-7.0.1-linux-x86_64.tar.gz
```

##### 创建用户组与用户

<p>
es不能直接运行在root用户上，这里新建专门用于运行elk程序的用户组。
</p>

```
#创建用户组
[root@dev-interview1 bmsourse]#  groupadd elk

#创建用户
[root@dev-interview1 bmsourse]#  useradd elk -g elk -p 123456

#将es文件夹授权给elk
[root@dev-interview1 bmsourse]#  chown -R elk:elk elasticsearch-7.0.1
```

##### 启动

```
#切换到elk用户
[root@dev-interview1 bmsourse]# su elk

#定位到es目录
[elk@dev-interview1 /]$ cd /bmsourse/elasticsearch-7.0.1

#前台启动
[elk@dev-interview1 elasticsearch-7.0.1]$ ./bin/elasticsearch
#后台启动
[elk@dev-interview1 elasticsearch-7.0.1]$ ./bin/elasticsearch -d
```

##### 关闭

```
#查看es进程
[root@dev-interview1 linhai.wang]#  ps -ef|grep elasticsearch
elk       5918     1 49 10:39 pts/4    00:01:08 /bmsourse/elasticsearch-7.2.0/jdk/bin/java -Xms1g -Xmx1g -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=75 -XX:+UseCMSInitiatingOccupancyOnly -Des.networkaddress.cache.ttl=60 -Des.networkaddress.cache.negative.ttl=10 -XX:+AlwaysPreTouch -Xss1m -Djava.awt.headless=true -Dfile.encoding=UTF-8 -Djna.nosys=true -XX:-OmitStackTraceInFastThrow -Dio.netty.noUnsafe=true -Dio.netty.noKeySetOptimization=true -Dio.netty.recycler.maxCapacityPerThread=0 -Dlog4j.shutdownHookEnabled=false -Dlog4j2.disable.jmx=true -Djava.io.tmpdir=/tmp/elasticsearch-10427101075890948233 -XX:+HeapDumpOnOutOfMemoryError -XX:HeapDumpPath=data -XX:ErrorFile=logs/hs_err_pid%p.log -Xlog:gc*,gc+age=trace,safepoint:file=logs/gc.log:utctime,pid,tags:filecount=32,filesize=64m -Djava.locale.providers=COMPAT -Dio.netty.allocator.type=unpooled -XX:MaxDirectMemorySize=536870912 -Des.path.home=/bmsourse/elasticsearch-7.2.0 -Des.path.conf=/bmsourse/elasticsearch-7.2.0/config -Des.distribution.flavor=default -Des.distribution.type=tar -Des.bundled_jdk=true -cp /bmsourse/elasticsearch-7.2.0/lib/* org.elasticsearch.bootstrap.Elasticsearch -d
elk       5933  5918  0 10:39 pts/4    00:00:00 /bmsourse/elasticsearch-7.2.0/modules/x-pack-ml/platform/linux-x86_64/bin/controller

#杀掉进程
[root@dev-interview1 linhai.wang]# kill -9 5918
```

#### Kibana

<p>

</p>

##### 下载安装文件

[下载地址](https://www.elastic.co/cn/downloads/kibana)

```
#获取文件
[root@dev-interview1 bmsourse]# wget https://artifacts.elastic.co/downloads/kibana/kibana-7.0.1-linux-x86_64.tar.gz

#解压文件
[root@dev-interview1 bmsourse]# tar -xvf kibana-7.0.1-linux-x86_64.tar.gz

#将es文件夹授权给elk
[root@dev-interview1 bmsourse]# chown -R elk:elk kibana-7.0.1-linux-x86_64
```

##### 修改配置

```
server.host: "10.100.3.83"
server.port: 5601

elasticsearch.hosts: ["http://10.100.3.83:9200"]

#中文支持
i18n.locale: "zh-CN"
```

##### 启动

```
#前台启动
[elk@DEV-interview1 bmsource]$ ./kibana-7.0.1-linux-x86_64/bin/kibana

#后台启动
[elk@DEV-interview1 bmsource]$ nohup ./kibana-7.0.1-linux-x86_64/bin/kibana >/dev/null 2>&1 &
```

##### 关闭

```
#查看kibana进程
[elk@DEV-interview1 kibana]$ lsof -i:5601
COMMAND  PID USER   FD   TYPE    DEVICE SIZE/OFF NODE NAME
node    6089  elk   18u  IPv4 503997785      0t0  TCP mapidev.51job.com:esmagent (LISTEN)

[elk@DEV-interview1 bmsource]$ ps -ef|grep node

#杀掉进程
[elk@DEV-interview1 bmsource]$ kill -9 6089
```

#### LogStash

## 插件安装

#### Head

[git地址](https://github.com/mobz/elasticsearch-head)

<p>
用于web页面查看es集群的状态。
</p>

##### es配置

```
#需要支持跨域访问
http.cors.enabled: true
http.cors.allow-origin: "*"
```

##### 安装head

```
[root@DEV-interview1 bmsource]# wget https://github.com/mobz/elasticsearch-head/archive/master.zip
[root@DEV-interview1 bmsource]# unzip master.zip
[root@DEV-interview1 bmsource]# cd elasticsearch-head-master/
[root@DEV-interview1 bmsource]# npm install
[root@DEV-interview1 bmsource]# npm run start
```

<p>
安装的时候需要使用到<a href='https://github.com/Medium/phantomjs/releases/download/v2.1.1/phantomjs-2.1.1-linux-x86_64.tar.bz2' target='_blank'>phantomjs</a>，可能下载的时候有问题，第一次install失败，后来手动下载了放到<code>/tmp/phantomjs</code>目录，重新install成功了。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-01-1-es-prepare-environment/20190705094319.png?raw=true)

#### x-pack

[X-Pack Settings](https://www.elastic.co/guide/en/x-pack/6.2/xpack-settings.html)

<p>
用于权限控制，监控等功能。目前高版本里已经都自带了，不需要额外进行安装，这里主要记录通过x-pack实现权限控制。
</p>

<p style='color:red;'>
Tips：需要同时配置es与kinaba。
</p>

##### es

[Security settings in Elasticsearch](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/security-settings.html)

```
#配置文件，elasticsearch.yml
#开启安全认证
xpack.security.enabled: true
#开启ssl
xpack.security.transport.ssl.enabled: true

#启动es
[elk@DEV-interview1 bmsource]$ ./elasticsearch-7.1.1/bin/elasticsearch -d

#设置密码，根据提示进行
#如下命令只能执行一次，之后需要修改密码可以到kibana的web页面进行
[elk@DEV-interview1 bmsource]$ ./elasticsearch-7.1.1/bin/elasticsearch -d
OpenJDK 64-Bit Server VM warning: Option UseConcMarkSweepGC was deprecated in version 9.0 and will likely be removed in a future release.
[elk@DEV-interview1 bmsource]$ ./elasticsearch-7.1.1/bin/elasticsearch-setup-passwords interactive
Initiating the setup of passwords for reserved users elastic,apm_system,kibana,logstash_system,beats_system,remote_monitoring_user.
You will be prompted to enter passwords as the process progresses.
Please confirm that you would like to continue [y/N]y

Enter password for [elastic]:
Reenter password for [elastic]:
Enter password for [apm_system]:
Reenter password for [apm_system]:
Enter password for [kibana]:
Reenter password for [kibana]:
Enter password for [logstash_system]:
Reenter password for [logstash_system]:
Enter password for [beats_system]:
Reenter password for [beats_system]:
Enter password for [remote_monitoring_user]:
Reenter password for [remote_monitoring_user]:
Changed password for user [apm_system]
Changed password for user [kibana]
Changed password for user [logstash_system]
Changed password for user [beats_system]
Changed password for user [remote_monitoring_user]
Changed password for user [elastic]
```

[查看用户信息](http://10.100.3.83:9200/_xpack/security/user?pretty)

- elastic：超级用户
- kibana：用于kibana与es交互的用户
- logstash_system：用于logstash_system与es交互的用户
- beats_system：用于beats_system与es交互的用户

<p>
此时通过浏览器访问es，就会要求输入用户名与密码。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-01-1-es-prepare-environment/20190705134010.png?raw=true)

##### kibana

[Security settings in Kibana](https://www.elastic.co/guide/en/kibana/7.1/security-settings-kb.html)

```
#配置文件，kibana.yml
#kibana使用的账号与密码
elasticsearch.username: "kibana"
elasticsearch.password: "123456"
#开启安全认证
xpack.security.enabled: true
#32位加密key
xpack.security.encryptionKey: "vupouibolpdpi3y2jba3x59ez4n9l1oh"
#cookie名称
xpack.security.cookieName: "kibana_user"
#session过期时间
xpack.security.sessionTimeout: 600000

#启动kibana
[elk@DEV-interview1 bmsource]$ ./kibana-7.1.1-linux-x86_64/bin/kibana
```

<p>
此时通过浏览器访问kibana，就会要求输入用户名与密码。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-01-1-es-prepare-environment/20190705142625.png?raw=true)

<p>
使用<code>elastic</code>用户登录，就可以进行数据的查看，及用户与角色的管理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-01-1-es-prepare-environment/20190705143305.png?raw=true)

## 参考资料

[Installation（安装）](http://cwiki.apachecn.org/pages/viewpage.action?pageId=4260676)

[Kibana安全特性之权限控制](https://www.cnblogs.com/cjsblog/p/9501858.html)

[nohup 详解](https://www.cnblogs.com/jinxiao-pu/p/9131057.html)

[linux shell中"2>&1"含义](https://www.cnblogs.com/zhenghongxin/p/7029173.html)