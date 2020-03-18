---
layout:     post
title:      "es-遇到的问题"
subtitle:   "problems"
date:       2019-07-02 18:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍在使用过程中碰到的一些问题及相应的解决方法。

## es服务启动问题

```
ERROR: [5] bootstrap checks failed
[1]: max file descriptors [4096] for elasticsearch process is too low, increase to at least [65535]
[2]: max number of threads [1024] for user [elk] is too low, increase to at least [4096]
[3]: max virtual memory areas vm.max_map_count [65530] is too low, increase to at least [262144]
[4]: system call filters failed to install; check the logs and fix your configuration or disable system call filters at your own risk
[5]: the default discovery settings are unsuitable for production use; at least one of [discovery.seed_hosts, discovery.seed_providers, cluster.initial_master_nodes] must be configured
```

##### 问题1

<p>
系统文件描述符低于es的运行要求。
</p>

```
#切换到root用户，修改如下文件
[root@dev-interview1 linhai.wang]# vim /etc/security/limits.conf

#增加配置，【elk】表示用户
elk               soft    nofile            65536
elk               hard    nofile            65536

#退出用户重新登录，确保配置已生效
[elk@dev-interview1 ~]$ ulimit -Hn
65536
[elk@dev-interview1 ~]$ ulimit -Sn
65536
```

##### 问题2

<p>
用户可打开的线程数低于es的运行要求。
</p>

```
#切换到root用户，修改如下文件
#文件可能为/etc/security/limits.d/90-nproc.conf
[root@dev-interview1 linhai.wang]# vim /etc/security/limits.conf

#增加配置，【elk】表示用户
elk               soft    nproc            4096
elk               hard    nproc            4096

#退出用户重新登录，确保配置已生效
[elk@dev-interview1 ~]$ ulimit -Hu
4096
[elk@dev-interview1 ~]$ ulimit -Su
4096
```

##### 问题3

<p>
系统最大虚拟内存区域低于es的运行要求。
</p>

```
#切换到root用户，修改如下文件
[root@dev-interview1 linhai.wang]# vim /etc/sysctl.conf

#增加配置
vm.max_map_count=262144

#使配置生效
[root@dev-interview1 linhai.wang]# sysctl -p
```

##### 问题4

<p>
系统调用筛选器安装失败。
</p>

```
#切换到root用户，修改如下文件
[root@dev-interview1 linhai.wang]# vim /bmsourse/elasticsearch-7.0.1/config/elasticsearch.yml

#增加配置
bootstrap.system_call_filter: false
```

##### 问题5

<p>
需要对相关的【discovery】配置，至少配置一个。
</p>

```
#切换到root用户，修改如下文件
[root@dev-interview1 linhai.wang]# vim /bmsourse/elasticsearch-7.0.0/config/elasticsearch.yml

#修改配置
cluster.initial_master_nodes: ["node-1"]
```

## es外部HTTP访问

<p>
默认es启动后，只能在本地通过127.0.0.1访问到，如果需要提供给外部访问，需要进行如下调整。
</p>

```
#切换到root用户，修改如下文件
[root@dev-interview1 linhai.wang]# vim /bmsourse/elasticsearch-7.0.0/config/elasticsearch.yml

#修改配置
network.host: 0.0.0.0
或
network.host: 本机ip
```

```
#设置为本机ip
[linhai.wang@dev-interview1 ~]$ netstat -anp|grep 9200
tcp        0      0 ::ffff:本机ip:9200     :::*                        LISTEN      -

#设置为0.0.0.1
[linhai.wang@dev-interview1 ~]$ netstat -anp|grep 9200
tcp        0      0 :::9200                     :::*                        LISTEN      -
```

## Kibana启动问题

<p>
使用的是<code>7.2.0</code>版本，启动的时候报缺少<code>GLIBC_2.17</code>错误。
</p>

```
[elk@dev-interview1 bmsourse]$ ./kibana-7.2.0-linux-x86_64/bin/kibana

  log   [05:16:08.676] [fatal][root] Error: /lib64/libc.so.6: version `GLIBC_2.14' not found (required by /bmsourse/kibana-7.2.0-linux-x86_64/node_modules/@elastic/nodegit/build/Release/nodegit.node)
```

<p>
安装<code>glibc-2.14</code>
</p>

```
[root@dev-interview1 bmsourse]# wget http://ftp.gnu.org/gnu/glibc/glibc-2.14.tar.gz
[root@dev-interview1 bmsourse]# tar -xvf glibc-2.14.tar.gz
[root@dev-interview1 bmsourse]# mkdir build
[root@dev-interview1 bmsourse]# cd build
[root@dev-interview1 bmsourse]# ../configure --prefix=/usr/local/glibc-2.14
[root@dev-interview1 bmsourse]# make && make install
```

<p>
切换到elk用户，设置临时环境变量运行有如下错误。
</p>

```
export LD_LIBRARY_PATH=/usr/local/glibc-2.14/lib:$LD_LIBRARY_PATH
```

```
[elk@dev-interview1 kibana-7.2.0-linux-x86_64]$ ./bin/kibana 

  log   [23:35:49.545] [fatal][root] Error: /bmsourse/kibana-7.2.0-linux-x86_64/node_modules/@elastic/nodegit/build/Release/nodegit.node: cannot enable executable stack as shared object requires: Operation not permitted
    at Object.Module._extensions..node (internal/modules/cjs/loader.js:718:18)
    at Module.load (internal/modules/cjs/loader.js:599:32)
    at tryModuleLoad (internal/modules/cjs/loader.js:538:12)
    at Function.Module._load (internal/modules/cjs/loader.js:530:3)
    at Module.require (internal/modules/cjs/loader.js:637:17)
    at require (internal/modules/cjs/helpers.js:22:18)
    at Object.<anonymous> (/bmsourse/kibana-7.2.0-linux-x86_64/node_modules/@elastic/nodegit/dist/nodegit.js:12:12)
    at Module._compile (internal/modules/cjs/loader.js:689:30)
    at Module._compile (/bmsourse/kibana-7.2.0-linux-x86_64/node_modules/pirates/lib/index.js:99:24)
    at Module._extensions..js (internal/modules/cjs/loader.js:700:10)
    at Object.newLoader [as .js] (/bmsourse/kibana-7.2.0-linux-x86_64/node_modules/pirates/lib/index.js:104:7)
    at Module.load (internal/modules/cjs/loader.js:599:32)
    at tryModuleLoad (internal/modules/cjs/loader.js:538:12)
    at Function.Module._load (internal/modules/cjs/loader.js:530:3)
    at Module.require (internal/modules/cjs/loader.js:637:17)
    at require (internal/modules/cjs/helpers.js:22:18)
    at Object.require (/bmsourse/kibana-7.2.0-linux-x86_64/x-pack/plugins/code/server/git_operations.js:10:19)
    at Module._compile (internal/modules/cjs/loader.js:689:30)
    at Module._compile (/bmsourse/kibana-7.2.0-linux-x86_64/node_modules/pirates/lib/index.js:99:24)
    at Module._extensions..js (internal/modules/cjs/loader.js:700:10)
    at Object.newLoader [as .js] (/bmsourse/kibana-7.2.0-linux-x86_64/node_modules/pirates/lib/index.js:104:7)
    at Module.load (internal/modules/cjs/loader.js:599:32)

 FATAL  Error: /bmsourse/kibana-7.2.0-linux-x86_64/node_modules/@elastic/nodegit/build/Release/nodegit.node: cannot enable executable stack as shared object requires: Operation not permitted
```

<p>
之后找系统组帮忙修改了啥东西，重新启动报<code>`GLIBC_2.17' not found</code>。暂时不做尝试了，先使用<code>7.0.0</code>版本，以后再尝试。
</p>

## 参考资料

[elasticsearch启动常见错误](https://www.cnblogs.com/zhi-leaf/p/8484337.html?tdsourcetag=s_pcqq_aiomsg)

[解决Elasticsearch外网访问的问题](https://blog.csdn.net/wd2014610/article/details/89532638)

[127.0.0.1和0.0.0.0和本机IP的区别](https://www.cnblogs.com/operationhome/p/8681475.html)
