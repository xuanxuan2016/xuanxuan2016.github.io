---
layout:     post
title:      "tcpdump/wireshark使用"
subtitle:   "tcpdump wireshark"
date:       2020-08-11 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - tcp/ip
---

## tcpdump

#### 命令格式

```
tcpdump [option] [proto] [dir] [type]
```

<p style='color:red;'>
Tips：如果[proto、dir、type]同时使用的话，需要使用[and]符进行连接
</p>

- proto(协议过滤)：tcp、udp、icmp、ip、ip6、arp、rarp、ether、wlan
- dir(数据流向过滤)：src、dst、src or dst
- type(主机端口过滤)：host、port、portrange、net
- option(可选参数)：

```
[-aAbdDefhHIJKlLnNOpqStuUvxX#] [ -B size ] [ -c count ]
[ -C file_size ] [ -E algo:secret ] [ -F file ] [ -G seconds ]
[ -i interface ] [ -j tstamptype ] [ -M secret ] [ --number ]
[ -Q|-P in|out|inout ]
[ -r file ] [ -s snaplen ] [ --time-stamp-precision precision ]
[ --immediate-mode ] [ -T type ] [ --version ] [ -V file ]
[ -w file ] [ -W filecount ] [ -y datalinktype ] [ -z postrotate-command ]
[ -Z user ] [ expression ]
```

#### 结果说明

```
[root@localhost tcpdump]# tcpdump -nn -S tcp
tcpdump: verbose output suppressed, use -v or -vv for full protocol decode
listening on eth0, link-type EN10MB (Ethernet), capture size 262144 bytes
17:42:24.571500 IP 10.0.2.15.23491 > 10.100.3.83.5672: Flags [P.], seq 1301996162:1301996170, ack 1402292654, win 30016, length 8
17:42:24.571744 IP 10.100.3.83.5672 > 10.0.2.15.23491: Flags [.], ack 1301996170, win 65535, length 0
17:42:25.867564 IP 10.100.3.106.5672 > 10.0.2.15.21874: Flags [P.], seq 1402740654:1402740662, ack 3186804
```

- 第一列：tcp交互时间
- 第二列：网络协议
- 第三列：发送方的ip+port
- 第四列：箭头 >， 表示数据流向
- 第五列：接收方的ip+port
- 第六列：冒号
- 第七列：数据包内容，包括Flags 标识符，seq 号，ack 号，win 窗口，数据长度 length

<p>
可用标识符：
</p>

- [S]：SYN（开始连接）
- [P]：PSH（推送数据）
- [F]：FIN（结束连接）
- [R]：RST（重置连接）
- [.]：没有 Flag，由于除了 SYN 包外所有的数据包都有ACK，所以一般这个标志也可表示 ACK

#### 命令使用

```
#基于IP地址过滤：host
tcpdump host 192.168.10.100

#基于网段进行过滤：net
tcpdump net 192.168.10.0/24

#基于端口进行过滤：port
tcpdump port 8088
tcpdump portrange 8000-8080

#基于协议进行过滤：proto
tcpdump tcp
```

#### 可选参数

##### 常用

```
#过滤结果输出到文件
tcpdump icmp -w icmp.pcap

#从文件中读取包数据
tcpdump icmp -r all.pcap

#过滤指定网卡的数据包
tcpdump -i any

#显示所有可用网络接口的列表
tcpdump -D

#捕获 count 个包 tcpdump 就退出
tcpdump -nn -c 2 tcp

#指定每条报文的最大字节数，默认262144
tcpdump -nn -s 100 tcp

#显示ack绝对序列号
tcpdump -S tcp
```

##### 设置不解析域名提升速度

- n：不把ip转化成域名，直接显示 ip，避免执行 DNS lookups 的过程，速度会快很多
- nn：不把协议和端口号转化成名字，速度也会快很多。
- N：不打印出host 的域名部分.。比如,，如果设置了此选现，tcpdump 将会打印'nic' 而不是 'nic.ddn.mil'.

##### 控制时间的显示

- t：在每行的输出中不输出时间
- tt：在每行的输出中会输出时间戳
- ttt：输出每两行打印的时间间隔(以毫秒为单位)
- tttt：在每行打印的时间戳之前添加日期的打印（此种选项，输出的时间最直观）

##### 显示数据包的头部

- x：以16进制的形式打印每个包的头部数据（但不包括数据链路层的头部）
- xx：以16进制的形式打印每个包的头部数据（包括数据链路层的头部）
- X：以16进制和 ASCII码形式打印出每个包的数据(但不包括连接层的头部)，这在分析一些新协议的数据包很方便。
- XX：以16进制和 ASCII码形式打印出每个包的数据(包括连接层的头部)，这在分析一些新协议的数据包很方便。

## wireshark


## 参考资料

[tcpdump教程](https://baijiahao.baidu.com/s?id=1671144485218215170&wfr=spider&for=pc)

[wireshark怎么抓包](https://www.cnblogs.com/moonbaby/p/10528401.html)

