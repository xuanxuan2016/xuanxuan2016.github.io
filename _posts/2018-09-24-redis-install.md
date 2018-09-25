---
layout:     post
title:      "redis的安装"
subtitle:   "Redis Install"
date:       2018-09-24 10:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - redis
---

> 介绍redis的安装及相关配置说明

## 前言

<p>
为了可以使用redis来做缓存服务器，首先需要进行服务的安装并启动服务，这里主要介绍的是单机单实例的安装
</p>

## 安装与启动

#### 1.安装

<p>
可在官网查看redis的可用版本，一般选择最新的可用版，这里使用的是4.0.11
</p>

```
[root@vagrant bmsource]# wget http://download.redis.io/releases/redis-4.0.11.tar.gz
[root@vagrant bmsource]# tar zxvf redis-4.0.11.tar.gz
[root@vagrant bmsource]# cd redis-4.0.11
[root@vagrant redis-4.0.11]# make
```

<p>
如果在<code>make</code>后执行<code>make install</code>则redis的相关可执行文件会复制到<code>/usr/local/bin</code>中。此处不使用这种方式，而是手动将可执行文件与配置文件复制到<code>/usr/local/redis</code>中
</p>

```
[root@vagrant redis-4.0.11]# cd /usr/local/
[root@vagrant local]# mkdir redis
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-cli /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-check-aof /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-check-rdb /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-benchmark  /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-server /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/src/redis-sentinel /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/redis.conf /usr/local/redis/
[root@vagrant local]# cp /bmsource/redis-4.0.11/sentinel.conf /usr/local/redis/
```

<p>
可执行文件说明
</p>

- redis-server：启动redis服务
- redis-cli：redis命令行客户端
- redis-benchmark：redis基准测试工具
- redis-check-aof：Redis AOF持久化文件检测和修复工具
- redis-check-dump：Redis RDB持久化文件检测和修复工具
- redis-sentinel：启动redis sentinel服务

<p>
为了以后可以方便使用，将redis路径配置到环境变量中
</p>

```
#编辑文件
[root@vagrant local]# vim /etc/profile
#增加配置
export REDIS=/usr/local/redis/
export PATH=$PATH:$REDIS
#生效配置
[root@vagrant local]# source /etc/profile
```

#### 2.启动

<p>
redis的实际运行需要设置相关配置项，所以启动都是通过配置文件启动，且都是以守护进程模式运行
</p>

```
#编辑配置文件
[root@vagrant local]# vim /usr/local/redis/redis.conf
#设置为守护模式
daemonize yes
#启动
[root@vagrant local]# redis-server /usr/local/redis/redis.conf 
#客户端查看
28191:C 24 Sep 06:25:41.019 # Configuration loaded
[root@vagrant local]# redis-cli 
127.0.0.1:6379> info server
# Server
redis_version:4.0.11
redis_git_sha1:00000000
redis_git_dirty:0
redis_build_id:90349ad45b8aa06a
redis_mode:standalone
os:Linux 2.6.32-573.el6.x86_64 x86_64
arch_bits:64
multiplexing_api:epoll
atomicvar_api:sync-builtin
gcc_version:4.4.7
process_id:28192
run_id:86fc3f7746c55aa156f0af86289422d0aeeb733f
tcp_port:6379
uptime_in_seconds:76
uptime_in_days:0
hz:10
lru_clock:11043761
executable:/usr/local/redis-server
config_file:/usr/local/redis/redis.conf
127.0.0.1:6379> 
```

#### 3.单机多实例

<p>
资源不够，使用服务器的多进程与多线程，最大内存配置，错时持久化。
</p>

## 配置文件

<p>
此配置文件redis.conf对应的是redis-server，为单服务的配置
</p>

#### 基本配置

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">基本配置</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>daemonize</td>
			<td>守护进程</td>
			<td>no</td>
			<td>yes|no</td>
			<td>no</td>
		</tr>
		<tr>
			<td>port</td>
			<td>端口号</td>
			<td>6379</td>
			<td>整数</td>
			<td>no</td>
		</tr>
		<tr>
			<td>loglevel</td>
			<td>日志级别</td>
			<td>notice</td>
			<td>debug|verbose|notice|warning</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>logfile</td>
			<td>日志文件</td>
			<td>空</td>
			<td>可按端口号区别</td>
			<td>no</td>
		</tr>
		<tr>
			<td>databases</td>
			<td>数据库数量</td>
			<td>16</td>
			<td>整数</td>
			<td>no</td>
		</tr>
		<tr>
			<td>unixsocket</td>
			<td>unix套接字</td>
			<td>空(不监听)</td>
			<td>套接字文件</td>
			<td>no</td>
		</tr>
		<tr>
			<td>unixsocketperm</td>
			<td>unix套接字权限</td>
			<td>0</td>
			<td>linux三位数权限</td>
			<td>no</td>
		</tr>
		<tr>
			<td>pidfile</td>
			<td>进程文件</td>
			<td>/var/run/redis.pid</td>
			<td>/var/run/redis_{port}.pid</td>
			<td>no</td>
		</tr>
		<tr>
			<td>lua-time-limit</td>
			<td>lua脚本超时时间<br />
			单位：毫秒</td>
			<td>5000</td>
			<td>整数，超时还会执行<br />
			script kill 或 shutdown</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>tcp-backlog</td>
			<td>tcp-backlog</td>
			<td>511</td>
			<td>整数</td>
			<td>no</td>
		</tr>
		<tr>
			<td>watchdog-period</td>
			<td>检查redis延迟问题<br />
			的周期</td>
			<td>0</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>activerehashing</td>
			<td>重置hash</td>
			<td>yes</td>
			<td>yes：延迟要求不高，可尽快释放内存<br />
			no：延迟要求很高</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>dir</td>
			<td>工作目录</td>
			<td>./(当前目录)</td>
			<td>自定义目录</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 最大内存配置

<p>
内存淘汰算法：
</p>

- [lru（Least recently used）](http://flychao88.iteye.com/blog/1977653)：最近最少使用
- [lfu（Least Frequently Used）](https://blog.csdn.net/joeyon1985/article/details/52442385)：最不经常使用

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">最大内存</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>maxmemory</td>
			<td>最大可用内存(字节)</td>
			<td>0(不限制)</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>maxmemory-policy</td>
			<td>内存淘汰策略<br />
			如果设置了最大内存</td>
			<td>noeviction</td>
			<td>volatile-lru：可过期键，lru算法<br />
			allkeys-lru：所有键，lru算法<br />
			volatile-lfu：可过期键，lfu算法<br />
			allkeys-lfu：所有键，lru算法<br />
			volatile-random：可过期键，随机<br />
			allkeys-random：所有键，随机<br />
			volatile-ttl：可过期键，最近要过期键<br />
			noeviction：不淘汰，执行写命名返回错误</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>maxmemory-samples</td>
			<td>lru,lfu,ttl采样数</td>
			<td>5</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### aof配置

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">AOF</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>appendonly</td>
			<td>开启aof持久化</td>
			<td>no</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>appendfsync</td>
			<td>同步磁盘频率</td>
			<td>everysec</td>
			<td>no|always|everysec</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>appendfilename</td>
			<td>aof文件名</td>
			<td>appendonly.aof</td>
			<td>appendonly_{port}.aof</td>
			<td>no</td>
		</tr>
		<tr>
			<td>aof-load-truncated</td>
			<td>加载aof文件，是否忽略<br />
			aof不完整的情况</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>no-appendfsync-on-rewrite</td>
			<td>rewrite期间对新的写入<br />
			是否不执行fsync</td>
			<td>no</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>auto-aof-rewrite-min-size</td>
			<td>触发自动重写的aof文件<br />
			的最小阀值(兆)<br />
			(即使不配置同步频率？)</td>
			<td>64M</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>auto-append-rewrite-percentage</td>
			<td>触发自动重写的aof文件<br />
			的增长比例条件<br />
			(即使不配置同步频率？)</td>
			<td>100</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>aof-rewrite-incremental-fsync</td>
			<td>重写过程中，是否采取<br />
			增量文件同步策略，每<br />
			32M同步磁盘</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### rbd配置

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">RDB</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>save</td>
			<td>保存条件</td>
			<td>save 900 1<br />
			save 300 10<br />
			save 60 10000</td>
			<td>不设置，表示不使用<br />
			rdb策略</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>dbfilename</td>
			<td>文件名</td>
			<td>dump.rdb</td>
			<td>dump_{prot}.rdb</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>rdbcompression</td>
			<td>文件是否压缩</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>rdbchecksum</td>
			<td>文件是否校验和</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>stop-writes-on-bgsave-error</td>
			<td>当前bgsave执行错误，是否拒绝<br />
			redis服务的写请求<br />
			下次触发bgsave时会恢复写请求</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 慢查询配置

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">RDB</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>slowlog-log-slower-than</td>
			<td>慢日志被记录阀值(微秒)</td>
			<td>10000</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>slowlog-max-len</td>
			<td>慢日志队列长度</td>
			<td>128</td>
			<td>整数</td>
			<td>yes</td>
		</tr> 
		<tr>
			<td>latency-monitor-threshold</td>
			<td>开启redis服务内存延迟监控<br />
			高负载的情况下，对性能可能会有影响</td>
			<td>0(关闭)</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 数据结构优化

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">数据结构优化</td>
		</tr>
		<tr>
			<td>名称</td>
			<td>说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>hash-max-ziplist-entries</td>
			<td>hash数据结构优化参数</td>
			<td>512</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>hash-max-ziplist-value</td>
			<td>hash数据结构优化参数</td>
			<td>64</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>list-max-ziplist-size</td>
			<td>list数据结构优化参数</td>
			<td>-2</td>
			<td>[-5,-1]<br />
			&gt;=0</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>list-compress-depth</td>
			<td>list数据结构优化参数</td>
			<td>0</td>
			<td>&gt;=0</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>set-max-intset-entries</td>
			<td>set数据结构优化参数</td>
			<td>512</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>zset-max-ziplist-entries</td>
			<td>zset数据结构优化参数</td>
			<td>128</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>zset-max-ziplist-value</td>
			<td>zset数据结构优化参数</td>
			<td>64</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>hll-sparse-max-bytes</td>
			<td>HyperLogLog数据结构优化参数</td>
			<td>3000</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 主从复制

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">主从复制</td>
		</tr>
		<tr>
			<td>名称</td>
			<td style="width:260px">说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>slaveof</td>
			<td>从节点属于哪个主节点</td>
			<td>空</td>
			<td>ip+port</td>
			<td>no<br />
			可使用slaveof配置</td>
		</tr>
		<tr>
			<td>repl-ping-slave-period</td>
			<td>从节点向主节点发送ping命令的周期(s)</td>
			<td>10</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-timeout</td>
			<td>主从复制超时时间(s)<br />
			(需&gt;repl-ping-slave-period)</td>
			<td>60</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-backlog-size</td>
			<td>复制积压缓冲区大小<br />
			保存从机断开连接期间的数据</td>
			<td>1M</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-backlog-ttl</td>
			<td>积压缓冲区释放时间(s)<br />
			从节点缓冲区不能被释放，因为可能被提升为主节点</td>
			<td>3600</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>slave-priority</td>
			<td>从节点优先级<br />
			越低的会优先被sentinel提升为master<br />
			0代表从永远不能变为master</td>
			<td>100</td>
			<td>[0-100]</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>min-slaves-to-write</td>
			<td colspan="1" rowspan="2">当从节点数&lt;min-slaves-to-write且延迟&lt;=min-slaves-max-lag时，master不接受写入请求。<br />
			防止有较多的从机不可用</td>
			<td>0</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>min-slaves-max-lag</td>
			<td>10</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>slave-serve-stale-data</td>
			<td>当从节点与主节点断开连接时，从节点是否可以继续提供服务</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>slave-read-only</td>
			<td>从节点是否只读<br />
			集群模式，从节点读写都不可用，需要使用readonly手动开启</td>
			<td>yes</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-disable-tcp-nodelay</td>
			<td>是否开启主从复制TCP_NODELAY<br />
			yes：合并tcp包，降低带宽，会有延迟<br />
			no：立即同步，没有延迟</td>
			<td>no</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-diskless-sync</td>
			<td>是否开启无盘复制<br />
			yes：直接将rdb文件发送到从机<br />
			no：先保存rdb文件，逐步将文件发送给从机</td>
			<td>no</td>
			<td>yes|no</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>repl-diskless-sync-delay</td>
			<td>无盘复制时，开始传送rdb文件的等待时间<br />
			用于等待多个从机加入进来，可一起传送</td>
			<td>5</td>
			<td>&nbsp;</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 客户端

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">客户端</td>
		</tr>
		<tr>
			<td>名称</td>
			<td style="width:260px">说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>maxclients</td>
			<td>最大客户端连接数</td>
			<td>10000</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>client-output-buffer-limit</td>
			<td>客户端输出缓冲区限制</td>
			<td>normal 0 0 0<br />
			slave 256mb 64mb 60<br />
			pubsub 32mb 8mb 60</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>timeout</td>
			<td>客户端限制多久自动关闭连接(s)</td>
			<td>0</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>tcp-keepalive</td>
			<td>检查tcp连接活性的周期(s)</td>
			<td>300</td>
			<td>整数</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>

#### 安全

<table border="1" cellpadding="0" cellspacing="0" style="width:700px">
	<tbody>
		<tr>
			<td colspan="5" style="text-align:center">安全</td>
		</tr>
		<tr>
			<td>名称</td>
			<td style="width:260px">说明</td>
			<td>默认值</td>
			<td>可选值</td>
			<td>支持热配置</td>
		</tr>
		<tr>
			<td>requirepass</td>
			<td>密码</td>
			<td>空</td>
			<td>自定义</td>
			<td>yes</td>
		</tr>
		<tr>
			<td>bind</td>
			<td>绑定ip</td>
			<td>空</td>
			<td>自定义</td>
			<td>no</td>
		</tr>
		<tr>
			<td>masterauth</td>
			<td>从节点配置主节点的密码</td>
			<td>127.0.0.1</td>
			<td>主节点的密码</td>
			<td>yes</td>
		</tr>
	</tbody>
</table>


## 参考资料

[官网](https://redis.io/download)

[redis.conf](https://github.com/antirez/redis/blob/unstable/redis.conf)

[redis开发与运维](http://item.jd.com/12121730.html?spm=1.1.1)
