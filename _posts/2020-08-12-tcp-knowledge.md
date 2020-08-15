---
layout:     post
title:      "tcp知识"
subtitle:   "tcp knowledge"
date:       2020-08-12 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - tcp/ip
---

## 基础知识

#### tcp数据格式

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_1.png?raw=true)

- 序列号：在建立连接时由计算机生成的随机数作为其初始值，通过 SYN 包传给接收端主机，每发送一
次数据，就「累加」一次该「数据字节数」的大小。<font color='blue'>用来解决网络包乱序问题</font>。
- 确认应答号：指下一次「期望」收到的数据的序列号，发送端收到这个确认应答以后可以认为在这个序
号以前的数据都已经被正常接收。<font color='blue'>用来解决不丢包的问题</font>。

**控制位：**
- SYN：该位为1时，表示希望建立连接，并在其「序列号」的字段进行序列号初始值的设定
- ACK：该位为1时，「确认应答」的字段变为有效，TCP 规定除了最初建立连接时的SYN包之外该位必须设置为1
- FIN：该位为1时，表示今后不会再有数据发送，希望断开连接。当通信结束希望断开连接时，通信双方的主机之间就可以相互交换FIN位置为1的TCP段
- RST：该位为1时，表示TCP连接中出现异常必须强制断开连接

#### tcp option字段

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_4.png?raw=true)

#### MSS(Max Segment Size)

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_3.png?raw=true)

- MTU：一个网络包的最大长度，以太网中一般为 1500 字节
- MSS：除去 IP 和 TCP 头部之后，一个网络包所能容纳的 TCP 数据的最大长度

##### MSS 选择目的
- 尽量每个 Segment 报文段携带更多的数据，以减少头部空间占用比率
- 防止 Segment 被某个设备的 IP 层基于 MTU 拆分

#### MSL(Maximum Segment Lifetime)

<p>
报文最大生存时间，它是任何报文在网络上存在的最长时间，超过这个时间报文将被丢弃。在linux系统中一般为30s。
</p>

#### RTT(Round-Trip Time)

<p>
数据从网络一端传送到另一端所需的时间，也就是包的往返时间。
</p>

## 三次握手建立连接

#### 握手流程与状态变迁

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_2.png?raw=true)

- 客户端和服务端都处于 CLOSED 状态。先是服务端主动监听某个端口，处于 LISTEN状态
- 客户端会随机初始化序号（ client_isn ），将此序号置于 TCP 首部的「序号」字段中，同时把SYN 标志位置为 1 ，表示 SYN 报文。接着把第一个SYN报文发送给服务端，表示向服务端发起连接，该报文不包含应用层数据，之后客户端处于 SYN-SENT 状态
- 服务端收到客户端的 SYN 报文后，首先服务端也随机初始化自己的序号（ server_isn ），将此序号填入 TCP 首部的「序号」字段中，其次把 TCP 首部的「确认应答号」字段填入 client_isn +1 , 接着把 SYN 和 ACK 标志位置为 1 。最后把该报文发给客户端，该报文也不包含应用层数据，之后服务端处于 SYN-RCVD 状态
- 客户端收到服务端报文后，还要向服务端回应最后一个应答报文，首先该应答报文 TCP 首部ACK 标志位置为 1 ，其次「确认应答号」字段填入server_isn+1，最后把报文发送给服务端，这次报文可以携带客户到服务器的数据，之后客户端处于 ESTABLISHED 状态
- 服务器收到客户端的应答报文后，也进入 ESTABLISHED 状态

**三次握手的原因：**
- 阻止重复历史连接的初始化（主要原因）
- 同步双方的初始序列号
- 避免资源浪费

#### 性能优化

##### 客户端

```
#主动建立连接时，发SYN的重试次数
net.ipv4.tcp_syn_retries = 6 
#建立连接时的本地端口可用范围
net.ipv4.ip_local_port_range = 32768 60999 
```

##### 服务端

```
#被动建立连接时，发SYN/ACK的重试次数
net.ipv4.tcp_synack_retries = 6
```

```
#调整SYN半连接队列大小
#1.增大tcp_max_syn_backlog
net.ipv4.tcp_max_syn_backlog = 1024
#2.增大somaxconn
net.core.somaxconn = 1024
#3.增大backlog(nginx)
server{
    listen 80 default backlog=1024;
    server_name localhost;
}
```

```
#全连接队列(accept)溢出控制
*0：如果 accept 队列满了，那么 server 扔掉 client 发过来的 ack ；
*1：如果 accept 队列满了，server 发送一个 RST 包给 client，表示废掉这个握手过程和这个连接；
net.ipv4.tcp_abort_on_overflow = 1
```

##### TCP Fast Open

<p>
绕过三次握手，使得 HTTP 请求减少了1个RTT的时间，Linux下可以通过tcp_fastopen开启该功能，同时必须保证服务端和客户端同时支持。
</p>

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_7.png?raw=true)

```
#系统开启TFO功能
*0：关闭
*1：作为客户端时可以使用 TFO
*2：作为服务器时可以使用 TFO
*3：无论作为客户端还是服务器，都可以使用 TFO
net.ipv4.tcp_fastopen = 1
```

##### 状态查看

```
#服务端进程accept队列长度

* -l：显示正在监听的socket
* -n：不解析服务名称
* -t：只显示tcp socket

*Recv-Q：当前 accept 队列的大小，也就是当前已完成三次握手并等待服务端 accept() 的 TCP
连接；
*Send-Q：accept 队列最大长度，上面的输出结果说明监听 8088 端口的 TCP 服务，accept 队列
的最大长度为 128

[root@localhost tcpdump]# ss -lnt
State       Recv-Q Send-Q      Local Address:Port  Peer Address:Port
LISTEN      0      128         127.0.0.1:9000      *:*
LISTEN      0      128         *:111               *:*
```

#### 安全问题

##### 避免SYN攻击1

<p>
攻击者短时间伪造不同 IP 地址的 SYN 报文，快速占满 backlog 队列，使服务器不能为正常用户服务。修改 Linux内核参数，控制队列大小和当队列满时应做什么处理。
</p>

- net.core.netdev_max_backlog：接收自网卡、但未被内核协议栈处理的报文队列长度
- net.ipv4.tcp_max_syn_backlog：SYN_RCVD 状态连接的最大个数
- net.ipv4.tcp_abort_on_overflow：超出处理能力时，对新来的 SYN 直接回包 RST，丢弃连接

##### 避免SYN攻击2

<p>
Linux 内核的 SYN （未完成连接建立）队列与 Accpet （已完成连接建立）队列是如何工作
</p>

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_5.png?raw=true)

- 当服务端接收到客户端的 SYN 报文时，会将其加入到内核的「 SYN 队列」；
- 接着发送 SYN + ACK 给客户端，等待客户端回应 ACK 报文；
- 服务端接收到 ACK 报文后，从「 SYN 队列」移除放入到「 Accept 队列」；
- 应用通过调用 accpet() socket 接口，从「 Accept 队列」取出的连接

<p>
通过tcp_syncookies来应对攻击。
</p>

```
#/etc/sysctl.conf
*0：表示关闭该功能；
*1：表示仅当 SYN 半连接队列放不下时，再启用它；
*2：表示无条件开启功能；
net.ipv4.tcp_syncookies = 1
```

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_6.png?raw=true)

- 当 「 SYN 队列」满之后，后续服务器收到 SYN 包，不进入「 SYN 队列」；
- 计算出一个 cookie 值，再以 SYN + ACK 中的「序列号」返回客户端，
- 服务端接收到客户端的应答报文时，服务器会检查这个 ACK 包的合法性。如果合法，直接放入到「 Accept 队列」。
- 最后应用通过调用 accpet() socket 接口，从「 Accept 队列」取出的连接

## 四次挥手断开连接

#### 挥手流程与状态变迁

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_8.png?raw=true)

- 客户端打算关闭连接，此时会发送一个 TCP 首部 FIN标志位被置为1的报文，也即FIN报文，之后客户端进入 FIN_WAIT_1 状态。
- 服务端收到该报文后，就向客户端发送 ACK 应答报文，接着服务端进入 CLOSED_WAIT 状态。
- 客户端收到服务端的 ACK 应答报文后，之后进入 FIN_WAIT_2 状态。
- 等待服务端处理完数据后，也向客户端发送 FIN 报文，之后服务端进入 LAST_ACK 状态。
- 客户端收到服务端的 FIN 报文后，回一个 ACK 应答报文，之后进入 TIME_WAIT 状态
- 服务器收到了 ACK 应答报文后，就进入了 CLOSE 状态，至此服务端已经完成连接的关闭。
- 客户端在经过 2MSL 一段时间后，自动进入 CLOSE 状态，至此客户端也完成连接的关闭。

##### 为何需要四次挥手

- 关闭连接时，客户端向服务端发送 FIN 时，仅仅表示客户端不再发送数据了但是还能接收数据。
- 服务器收到客户端的 FIN 报文时，先回一个ACK应答报文，而服务端可能还有数据需要处理和发送，等服务端不再发送数据时，才发送 FIN报文给客户端来表示同意现在关闭连接。

##### 为何需要2MSL的TIME_WAIT

- 防止具有相同「四元组」的「旧」数据包被收到；
- 保证「被动关闭连接」的一方能被正确的关闭，即保证最后的ACK能让被动关闭方接收，从而帮助其正常关闭；

#### 关闭连接

##### 关闭方式

- RST报文关闭：如果进程异常退出了，内核就会发送 RST 报文来关闭，它可以不走四次挥手流程
- FIN报文关闭：安全关闭连接的方式必须通过四次挥手，它由进程调用 close 和 shutdown 函数发起 FIN 报文

##### close与shutdown区别

- close：完全断开连接，无法传输数据，而且也不能发送数据
- shutdown：可控制只关闭一个方向的连

##### shutdown的三种方式

```
int shutdown(int sock,int howto)
```

- SHUT_RD(0)：关闭连接的「读」这个方向，如果接收缓冲区有已接收的数据，则将会被丢弃，并且后续再收到新的数据，会对数据进行 ACK，然后悄悄地丢弃。也就是说，对端还是会接收到ACK，在这种情况下根本不知道数据已经被丢弃了。
- SHUT_WR(1)：关闭连接的「写」这个方向，这就是常被称为「半关闭」的连接。如果发送缓冲区还有未发送的数据，将被立即发送出去，并发送一个 FIN 报文给对端。
- SHUT_RDWR(2)：相当于 SHUT_RD 和 SHUT_WR 操作各一次，关闭套接字的读和写两个方向。

#### 性能优化

##### TCP保活机制

<p>
定义一个时间段，在这个时间段内，如果没有任何连接相关的活动，TCP 保活机制会开始作用，每隔一
个时间间隔，发送一个探测报文，该探测报文包含的数据非常少，如果连续几个探测报文都没有得到响
应，则认为当前的 TCP 连接已经死亡，系统内核将错误信息通知给上层应用程序。
</p>

```
#超过此时间无活动，启动保活机制
net.ipv4.tcp_keepalive_time=7200
#每次检测间隔时间
net.ipv4.tcp_keepalive_intvl=75
#总归检测次数
net.ipv4.tcp_keepalive_probes=9
```

##### FIN_WAIT1/LAST_ACK状态的优化

```
#调整FIN报文重传次数，默认为8。适用于主动或被动方发送FIN报文
net.ipv4.tcp_orphan_retries = 5
#调整孤儿连接最大个数
net.ipv4.tcp_max_orphans = 16000
```

##### FIN_WAIT2 状态的优化

```
#调整孤儿连接FIN_WAIT2状态的持续时间
net.ipv4.tcp_fin_timeout = 60
```

##### TIME_WAIT状态优化

**危害**
- 客户端：TIME_WAIT过多，就会导致端口资源被占用，因为端口就65536个，被占满就会导致无法创建新的连接。
- 服务端：由于一个四元组表示 TCP 连接，理论上服务端可以建立很多连接，服务端确实只监听一个端口 但
是会把连接扔给处理线程，所以理论上监听的端口可以继续监听。但是线程池处理不了那么多一直不断的连接了。所以当服务端出现大量 TIME_WAIT 时，系统资源被占满时，会导致处理不过来新的连接

```
#方式1(仅限客户端)

#复用处于TIME_WAIT的socket 为新的连接所用
net.ipv4.tcp_tw_reuse = 1
#操作系统可以拒绝迟到的报文（默认即为 1）
net.ipv4.tcp_timestamps=1
```

```
#方式2

#开启后，同时作为客户端和服务器都可以使用 TIME-WAIT 状态的端口
* 不安全，无法避免报文延迟、重复等给新连接造成混乱
net.ipv4.tcp_tw_recycle = 1
```

```
#方式3

#time_wait 状态连接的最大数量，超出后直接关闭连接
net.ipv4.tcp_max_tw_buckets = 262144
```

## 重传机制

#### 超时重传

<p>
在发送数据时，设定一个定时器，当超过指定的时间后，没有收到对方的ACK确认应答报文，就会重发该数据。
</p>

**重传发生情况：**
- 数据包丢失
- 确认应答丢失

##### RTO

<p>
超时重传时间（Retransmission Timeout）应该略大于RTT。
</p>    

- 当超时时间 RTO 较大时，重发就慢，丢了老半天才重发，没有效率，性能差；
- 当超时时间 RTO 较小时，会导致可能并没有丢就重发，于是重发的就快，会增加网络拥塞，导致更多的超时，更多的超时导致更多的重发    

<p>
每当遇到一次超时重传的时候，都会将下一次超时时间间隔设为先前值的两倍。两次超时，就说明网络环境差，不宜频繁反复发送。
</p>

#### 快速重传

<p>
快速重传（Fast Retransmit）机制，它不以时间为驱动，而是以数据驱动重传。
</p>

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_9.png?raw=true)

- 第一份 Seq1 先送到了，于是就 Ack 回 2；
- 结果 Seq2 因为某些原因没收到，Seq3 到达了，于是还是 Ack 回 2；
- 后面的 Seq4 和 Seq5 都到了，但还是 Ack 回 2，因为 Seq2 还是没有收到；
- 发送端收到了三个 Ack = 2 的确认，知道了 Seq2 还没有收到，就会在定时器过期之前，重传丢失的Seq2。
- 最后，接收到收到了 Seq2，此时因为 Seq3，Seq4，Seq5 都收到了，于是 Ack 回 6 。

**接收方：**
- 当接收到一个失序数据段时，立刻发送它所期待的缺口 ACK 序列号
- 当接收到填充失序缺口的数据段时，立刻发送它所期待的下一个 ACK 序列号

**发送方：**
- 当接收到 3 个重复的失序ACK段（4个相同的失序ACK段）时，不再等待重传定时器的触发，立刻基于快速重传机制重发报文段

#### SACK

<p>
SACK(Selective Acknowledgment)，在TCP头部「选项」字段里加一个SACK的东西，它可以将缓存的数据map发送给发送方，这样发送方就可以知道哪些数据收到了，哪些数据没收到，知道了这些信息，就可以只重传丢失的数据。
</p>

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_10.png?raw=true)

<p>
发送方收到了三次同样的 ACK 确认报文，于是就会触发快速重发机制，通过 SACK 信息发现只有 200~299 这段数据丢失，则重发时，就只选择了这个 TCP 段进行重复。
</p>

```
#支持sack配置，默认开启
net.ipv4.tcp_sack =1 
```

#### Duplicate SACK

<p>
使用了 SACK 来告诉「发送方」有哪些数据被重复接收了。
</p>

- 可以让「发送方」知道，是发出去的包丢了，还是接收方回应的 ACK 包丢了;
- 可以知道是不是「发送方」的数据包被网络延迟了;
- 可以知道网络中是不是把「发送方」的数据包给复制了

## 滑动窗口

#### 基本概念

<p>
窗口：指无需等待确认应答，而可以继续发送数据的最大值。
</p>

<p>
窗口大小(window)：接收端告诉发送端自己还有多少缓冲区可以接收数据。于是发送端就可以根据这个接收端的处理能力来发送数据，而不会导致接收端处理不过来。
</p>

#### 发送方

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_11.png?raw=true)

- #1 是已发送并收到 ACK确认的数据：1~31 字节
- #2 是已发送但未收到 ACK确认的数据：32~45 字节
- #3 是未发送但总大小在接收方处理范围内（接收方还有空间）：46~51字节
- #4 是未发送但总大小超过接收方处理范围（接收方没有空间）：52字节以后

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_12.png?raw=true)

- SND.WND ：表示发送窗口的大小（大小是由接收方指定的）；
- SND.UNA ：是一个绝对指针，它指向的是已发送但未收到确认的第一个字节的序列号，也就是#2的第一个字节。
- SND.NXT ：也是一个绝对指针，它指向未发送但可发送范围的第一个字节的序列号，也就是#3的第一个字节
- 指向 #4 的第一个字节是个相对指针，它需要 SND.UNA 指针加上 SND.WND 大小的偏移量，就可以指向 #4 的第一个字节了。

```
可用窗口大小 = SND.WND -（SND.NXT - SND.UNA）
```

#### 接收方

![image](http://my.beautymyth.cn/2020-08-12-tcp-knowledge/tu_13.png?raw=true)

- RCV.WND ：表示接收窗口的大小，它会通告给发送方。
- RCV.NXT ：是一个指针，它指向期望从发送方发送来的下一个数据字节的序列号，也就是 #3 的
第一个字节。
- 指向 #4 的第一个字节是个相对指针，它需要 RCV.NXT 指针加上 RCV.WND 大小的偏移量，
就可以指向 #4 的第一个字节了。

## 流量控制

## 拥塞控制

## 其他

#### tcp调节参数

<p>
tcp调节参数用于根据实际情况对tcp的运行控制进行调整，以满足需求。
</p>

```
#tcp参数
[root@localhost tcpdump]# ls -l /proc/sys/net/ipv4/tcp*
-rw-r--r-- 1 root root 0 Aug 12 13:57 /proc/sys/net/ipv4/tcp_abort_on_overflow
```

<p>
如果需要修改tcp相关参数，可在如下文件修改。
</p>

```
#编辑
vim /etc/sysctl.conf

#生效
sysctl -p
```

## 参考资料

[Web协议详解与抓包实战](https://time.geekbang.org/course/detail/100026801-118169)

[小林tcp](https://mp.weixin.qq.com/s/fjnChU3MKNc_x-Wk7evLhg)

[linux 内核参数优化](https://www.cnblogs.com/weifeng1463/p/6825532.html)