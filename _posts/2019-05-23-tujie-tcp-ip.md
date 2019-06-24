---
layout:     post
title:      "图解TCP/IP笔记"
subtitle:   "tcp ip"
date:       2019-05-23 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - tcp/ip
---

## 网络基础知识

#### 计算机与网络发展的7个阶段

<table>
<tr>
<td style='width:120px'>年代</td>
<td>内容</td>
<td>说明</td>
</tr>
<tr>
<td>20世纪50年代</td>
<td>批处理</td>
<td>Batch Processing：
事先将用户程序和数据装入卡带或磁带，让计算机按照一定的顺序读取，使用户所要执行的这些程序和数据能够一并得到批量处理</td>
</tr>
<tr>
<td>20世纪60年代</td>
<td>分时系统</td>
<td>Time Sharing System：
多个终端与同一个计算机连接，允许多个用户同时使用一台计算机系统。特性：
1.多路性
2.独占性
3.交互性
4.及时性</td>
</tr>
<tr>
<td>20世纪70年代</td>
<td>计算机之间的通信</td>
<td></td>
</tr>
<tr>
<td>20世纪80年代</td>
<td>计算机网络的产生</td>
<td></td>
</tr>
<tr>
<td>20世纪90年代</td>
<td>互联网的普及</td>
<td></td>
</tr>
<tr>
<td>2000年</td>
<td>以互联网为中心的时代</td>
<td>通过IP（Internet Protocol）网可将，个人电脑，手机终端，电视机，电话，相机，家用电器等结合到一起。</td>
</tr>
<tr>
<td>2010年</td>
<td>从"单纯建立连接"到"安全建立连接"</td>
<td></td>
</tr>
</table>

#### 协议

<p>
<code>协议</code>是计算机与计算机之间通过网络实现通信事先达成的一种“约定”。这种“约定”使那些由不同厂商的设备、不同的CPU以及不同的操作系统组成的计算机之间，只要遵循相同的协议就能够实现通信。反之，如果使用的协议不同，就无法通信。
</p>

```linux
举例说明：

协议：人会的语言（如汉语，英语）
通信：人的聊天
数据：聊天的内容

只有双方使用同样的语言，才能够互相交流。
```

##### 分组交换协议

<p>
将大数据拆分成多个较小的包（Packet）进行传输。
</p>

<p>
<code>包=报文首部（分组序号+源主机地址+目标主机地址+数据处理规则等）+原始数据</code>
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_14.png?raw=true)


#### 协议分层与OSI参考模型

<p>
OSI的7个分层。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_18.png?raw=true)

<p>
各个分层的作用。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_19.png?raw=true)

#### 传输方式分类

##### 面向有连接型与面向无连接型

- 面向有连接型：在发送数据之前，需要在收发主机之间建立一条通信线路。
- 面向无连接型：不要建立和断开连接，发送端可在任何时候发送数据，接收端不知道何时会接收到数据，需要定时确认是否收到数据。

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_29.png?raw=true)


##### 电路交换与分组交换

- 电路交换(不能共享连接)：主要用于以前的电话网络。相互通信的计算机独占一条连接的电路。
- 分组交换(能共享连接)：主要用于现在的TCP/IP。将发送的数据拆分成多个数据包（包含首部），按顺序发送到分组交换机（路由器），交换机将数据缓存到本地，再顺序发送给目标计算机。可共享连接。

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_30.png?raw=true)

##### 接收端数量

- 单播（UniCast）：1对1通信。如早期固定电话。
- 广播（BroadCast）：1对多通信，将消息发送给所有与主机相连的机器。如电视播放。
- 多播（MultiCast）：类似于广播，不过要限定一定主机作为接收端。如电视会议。
- 任播（AnyCast）：在特定的多个主机中选出一台进行通信。如DNS根域名解析服务器。

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_1_32.png?raw=true)

#### 地址

- 唯一性：一个地址必须明确的表示一个主体对象，在同一个通信网络中不允许有两个相同地址的通信主体存在。可对由多个设备组成的一组通信对象赋予唯一的通信地址。
- 层次性：便于高效的从很多地址中找出通信的目标地址。如ip地址的分层(127.0.0.1)。

#### 网络的构成要素

<table>
<tr>
<td style='width:220px'>设备</td>
<td>作用</td>
</tr>
<tr>
<td>网卡</td>
<td>计算机联网的设备。</td>
</tr>
<tr>
<td>中继器（Repeater）/1层交换机</td>
<td>从物理层上延长网络的设备。
将电缆传过来的电信号或光信号经由中继器波形调整和放大再传给另一个电缆（不能无限连接）。</td>
</tr>
<tr>
<td>网桥（Bridge）/2层交换机</td>
<td>从数据链路层上延长网络的设备。
根据物理地址（MAC地址）进行处理。</td>
</tr>
<tr>
<td>路由器（Router）/3层交换机</td>
<td>通过网络层转发分组数据的设备。
根据IP地址进行处理。</td>
</tr>
<tr>
<td>4-7层交换机</td>
<td>处理传输层以上各层网络传输的设备。
如负载均衡器，广域网加速器，特殊应用访问加速，防火墙等。</td>
</tr>
<tr>
<td>网关（Gateway）</td>
<td>转换协议的设备。
负责将从传输层到应用层的数据进行转换和转发的设备。
如代理服务器</td>
</tr>
</table>

## TCP/IP基础知识

#### TCP/IP出现的背景

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_zi_1.png?raw=true)

#### TCP/IP的标准化

<p>
含义：利用IP进行通信时所必须用到的协议群的统称。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_2_3.png?raw=true)

<p>
TCP/IP的规范为RFC（Request For Comment），记录了协议规范内容，协议的实现和运用的相关信息，以及实验方面的信息。
</p>

- RFC规范特性
- 开放性：允许任何人加入组织并进行讨论
- 实用性：先开发，再写规范，在标准确定时已经在很多设备上进行了验证

<p>
RFC制定流程通常包括如下阶段
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_zi_2.png?raw=true) 

#### TCP/IP协议分层模型

<p>
TCP/IP与OSI在分层模块上稍有区别，OSI参考模型注重的是<code>通信协议必要的功能是什么</code>，TCP/IP主要的是<code>在计算机上实现协议应该开发哪种程序</code>。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_2_8.png?raw=true) 

##### 硬件（物理层）

<p>
以太网或电话线路等物理层设备。
</p>

##### 网络接口层（数据链路层）

<p>
利用以太网中的数据链路层进行通信。
</p>

##### 互联网层（网络层）

<p>
互联网层使用IP协议，IP协议基于IP地址转发分包数据。
</p>

- ARP（Address Resolution Protocol 地址解析协议）：从分组数据包的IP地址中解析出物理（MAC）地址。
- IP（Internet Protocol 网络协议，非可靠性传输协议）：使用IP地址作为主机标识，跨越网络传送数据包，整个互联网都能收到数据。
- ICMP（Internet Control Message Protocol 控制报文协议）：在IP数据包因为异常到不了目标地址时，通知发送端出现异常。
##### 传输层

<p>
在应用程序之间实现通信。
</p>

- TCP（Transmission Control Protocol 传输控制协议）：面向有连接的、可靠的、基于字节流的传输层通信协议。由于复杂的规则，不利于视频会议等场合。
- UDP（User Datagram Protocol 用户数据报协议）：面向无连接的、不可靠的传输层协议。由于规则简单，可用于视频、音频等传输场合。

##### 应用层（会话层，表示层，应用层）

<p>
一般在应用程序中实现了OSI模型中会话层，表示层，应用层的功能。TCP/IP应用的架构大多数属于客户端/服务端模型。
</p>

- WWW：浏览器与服务端使用HTTP（HyperText Transfer Protocol，应用层）协议传输HTML（HyperText Markup Language，表示层）数据
- EMail（电子邮件）：使用SMTP（Simple Mail Transfer Protocol，应用层）协议传输的MIME（表示层）邮件数据
- FTP（文件传输）：使用FTP（File Transfer Protocol）协议传输文件数据，需要建立2个TCP连接，一个用于发出传输请求时用到的控制连接，一个用于传输数据时用到的数据连接
- TELNET与SSH（远程登录）：登录到远程的计算机，运行那台计算机上的功能
- SNMP（网络管理）：使用SNMP（Simple Network Management Protocol，应用层）协议来管理网络，管理信息可通过MIB（Management Information Base，表示层）访问

#### TCP/IP分层模型与通信示例

##### 数据包首部

<p>
每个分层中，都会对所发送的数据附加一个首部，首部中包含了该层的必要信息（如发送的目标地址以及协议相关信息），为协议提供的信息为<code>包首部</code>，所要发送的内容为<code>数据</code>。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_2_17.png?raw=true) 

##### 数据包传输过程

<p>
通过从主机A向主机B发送电子邮件来看TCP/IP的通信过程。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_2_18.png?raw=true) 

##### 经过数据链路的包

<p>
分组数据包经过以太网的数据链路时大致流程如下。
</p>

- 包首部中包含的必要信息：
- 1.发送端与接收端地址
- 2.上一层的协议类型（用于数据到接受端后向上层传递时的依据）

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_2_19.png?raw=true)

## 数据链路

#### 数据链路的作用

<p>
数据链路层的协议：定义了通过通信媒介（双绞线电缆、同轴电缆、光纤、电波、红外线等）<span style='color:red;'>直接互连</span>的设备之间传输的规范。
</p>

##### 网络拓扑

<p>
定义：网络的连接和构成的形态，分为总线型、环形，星型、网状型等
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_3.png?raw=true)

#### 数据链路的相关技术

##### MAC地址

<p>
作用：用于识别数据链路中互连的节点。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_4.png?raw=true)

<p>
MAC地址长度为48位（二进制），但是一般使用16进制表示，则为6个16进制数值连接。
</p>

- 第1位：单播地址（0）/多播地址（1）
- 第2位：全局地址（0）/本地地址（1）
- 第3-24位：由IEEE管理，并保证各厂商之间不重复
- 第25-48位：有厂商管理，并保证产品之间不重复

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_5.png?raw=true)

##### 通信介质

<p>
通信介质类型：
</p>

- 共享介质型网络（半双工通信）：多个设备共享通信介质
- 非共享介质型网络（全双工通信）：多个设备不共享通信介质，直连交换机，由交换机转发数据帧

<p>
共享介质型网络中的介质访问控制方式：
</p>

- 争用方式（Contention）：争夺获取数据传输的权利，通常是先到先得的方式占用信道发送数据
- 令牌传递方式：沿着令牌环发送“令牌”，获得令牌的站可以发送数据


<p>
半双工通信与全双工通信区别：
</p>

- 半双工通信：只发送或只接收的通信方式，如无线电收发器
- 全双工通信：同时发送与接收的通信方式，如电话

##### 根据MAC地址转发

<p>
以太网交换机：拥有多个端口的网桥，根据数据链路层中的每个帧的目标MAC地址，决定从哪个网络端口发送数据。
</p>

<p>
转发表（Forwarding Table）：记录源MAC地址与接受数据的网络端口的对应关系
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_12.png?raw=true)

##### 环路检测技术

- 生成树方式：
- 源路由法：

##### VLAN

<p>
VLAN：在交换机上按照端口划分出不同的网段，从而限制了广播数据的传输范围、减少了网络负载、提交了网络安全性。
</p>

<p>
TAG VLAN：数据经过交换机时，加入<code>VLAN ID</code>可实现跨网段的数据传输。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_15.png?raw=true)

#### 数据链路的传输方式

##### 以太网

<p>
以太网连接形式：
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_18.png?raw=true)

<p>
以太网帧格式：
</p>

- FCS（Frame Check Sequence）：帧检测序列，用来检测数据帧是否损坏。

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_3_20.png?raw=true)

- 类型：网络层协议类型

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_3_2.png?raw=true)

##### 无线通信

<P>
定义：通常使用电磁波、红外线、激光等方式进行传输数据。
</P>

##### PPP

<P>
定义（Point-to-Point Protocol）：点对点，即1对1的连接计算机的协议。
</P>

## IP协议

#### 网络层的作用

<p>
作用：实现终端节点（需要直接相连）间的通信（end-to-end），可跨越多种数据链路。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_1.png?raw=true)

<p>
以外出旅行为例：
</p>

- 数据链路层：旅途中所需要的机票、车票、船票等，用于2个相邻目的地间的交通工具
- 网络层：旅途行程表，用于确定什么时候该坐哪种交通工具，从而完成整个旅程

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_2.png?raw=true)

#### IP基础知识

##### IP地址

<p>
定义：网络层的地址，用于在连接到网络中的所有主机中识别出进行通信的目标地址。
</p>

<p>
IP属于面向无连接型的传输，为了传输的可靠性，上一层的TCP采用面向有连接型的传输。
</p>

- 简化：面向连接比面向无连接处理相对复杂
- 提速：每次通信之前都要事先建立连接，会降低处理速度

##### 路由控制

<p>
定义：Routing，将分组数据发送到最终目的地址的功能。不管网络多么复杂，也可以通过路由控制确定到达目标地址的通路。
</p>

<p>
Hop中文叫“跳”，它是指网络中的一个区间，IP包正是在网络中一个跳间被转发。数据链路实现某一个区间（一跳）内的通信，而IP实现直至最终目标地址的通信（点对点）。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_5.png?raw=true)

<p>
为了将数据包发送给目标主机，所有主机都维护者一张路由控制表（Routing Table），该表记录IP数据在下一步应该发给哪一个路由器。IP包将根据这个路由表在各个数据链路上传输。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_8.png?raw=true)

<p>
以快递运输为例：
</p>

- IP数据包：送的包裹
- 数据链路：送货车
- 网络层：送货目的地
- 路由控制：转运站

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_7.png?raw=true)

#### IP地址

<p>
IP地址（IPv4）在计算机内部由32位二进制表示，在日常使用中由4个10进制表示，如<code>192.168.1.1</code>。由“网络标识”与“主机标识”组成。
</p>

- 网络标识：在数据链路的每个段配置不同的值，相互连接的每个段不能重复
- 主机标识：同一网段内的主机标识不能重复，但网络标识必须相同

#### IP数据包分组与合并处理

<p>
每种数据链路的最大传输单元（MTU）不一定相同，当发送的报文比较大时，在路由器中会对报文进行分片，被主机接收时进行合并。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_24.png?raw=true)

#### IPV4与IPv6

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_31.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_4_33.png?raw=true)

## IP协议相关技术

#### DNS

<p>
DNS（Domain Name System）：管理主机名和IP地址之间对应关系的系统。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_5.png?raw=true)

#### ARP

<p>
ARP（Address Resolution Protocol）：以目标IP地址为线索，定位下一个应该接收数据分包的网络设备对应的MAC地址。如果目标主机不在同一个链路上，则可通过ARP查询下一跳路由的MAC地址。
</p>

<p>
Tips：ARP只适用于IPv4，IPv6需要使用<code>ICMPv6</code>来探索邻居信息。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_6.png?raw=true)

<p>
RARP（Reverse Address Resolution Protocol）：从MAC地址定位IP地址的一种协议。
</p>

#### ICMP

<p>
ICMP（Internet Control Message Protocol）：控制报文协议，确认IP包是否成功送达目标地址，通知在发送过程中IP包被废弃的具体原因，改善网络设置等。如<code>ping</code>命令，就是典型的ICMP应用。
</p>

<p>
ICMP消息类型：
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_5_2.png?raw=true)

#### DHCP

<p>
DHCP（Dynamic Host Configuration Protocol）：动态主机配置协议，自动设置IP地址，统一管理IP地址分配。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_17.png?raw=true)

#### NAT

<p>
NAT（Network Address Translator）：在本地网络中使用私有地址，在连接互联网时转而使用全局IP地址。
</p>

<p>
NAPT（Network Address Ports Translator）：除了转换IP地址，还可以转换TCP，UDP的端口。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_19.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_20.png?raw=true)

#### IP隧道

<p>
在一个网络环境中，网络A与B使用IPv6，如果处于中间位置的网络C支持IPv4的话，网络A与网络B之间将无法直接进行通信。为了能让它们之间正常通信，需要采用IP隧道功能。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_22.png?raw=true)

<p>
IP隧道中可以将那些从网络A发过来的IPv6包统和为一个数据，再为之追加一个IPv4的首部转发给网络C，这种在网络层的首部后面继续追加网络层首部的通信方法叫做<code>IP隧道</code>。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_5_23.png?raw=true)

## TCP与UDP

#### 传输层的作用

##### TCP

<p>
TCP是面向连接的，可靠的<code>流</code>协议。
</p>

```
流是指不间断的数据结构，你可以把它想象成排水管道中的水流。
当应用程序采用TCP发送消息时，虽然可以保证发送的顺序，但还是犹如没有任何间隔的数据流发送给客户端。
因此在发送端发送消息时，可以设置一个表示长度或间隔的字段信息。
```

##### UDP

<p>
UDP是面向无连接的，不可靠的<code>数据报</code>协议。
</p>

##### TCP与UDP使用场景

- TCP：用于在传输层有必要实现可靠传输的情况。因为它具备顺序控制、重发控制等机制。
- UDP：用于对高速传输和实时性有较高要求的通信或广播通信。

##### Socket

<p>
在日常使用TCP或UDP时，会用到操作系统提供的类库，这种类库一般被称为API，对于TCP或UDP来说会广泛使用到套接字（Socket）的API。应用程序使用套接字时，可以设置对端的IP地址、端口号，并实现数据的发送与接收。
</p>

#### 端口号

<p>
端口号用来识别同一台计算机中进行通信的不同应用程序。
</p>

##### 区分不同通信

<p>
源IP地址+目标IP地址+协议号+源端口号+目标端口号
</p>

##### 端口号确定方法

[知名端口号](http://tool.oschina.net/commons?type=7)

- 标准既定的端口号：每个端口号有固定的使用目的。知名端口号:【0-1023】。已注册但可用:【1024-49151】。
- 时许分配法：根据请求动态分配端口号。动态端口号:【49152-65535】

#### TCP

<p>
TCP（Transmission Control Protocol）：面向有连接的协议，只有在确认通信对端存在时才会发送数据，从而可以控制通信流量的浪费。
</p>

##### TCP的特点及目的

<p>
TCP通过<span style='color:red;'>检验和、序列号、确认应答、重发控制、连接管理、以及窗口控制</span>等机制来实现可靠传输。解决了数据的破坏、重发以及分片顺序混乱等问题。
</p>

##### 通过序列号与确认应答提高可靠性

- 应答标记：
- ACK：确认应答
- NACK：非确认应答

<p>
序列号：按顺序给发送数据的每一个字节标记编号。接收端查询接收数据TCP首部中的序列号和数据长度，将自己下一步需要接收的序号作为ACK返送回去。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_10.png?raw=true)

##### 重发超时如何确定

<p>
重发超时：在重发数据之前，等待ACK到来的那个特定时间间隔。通常比【往返时间+偏差时间】稍大一点，为0.5的整数倍。
</p>

##### 连接管理

<p>
一个TCP连接的建立与断开，正常过程至少需要来回发送7个包才能完成，也就是常说的3次握手，2次挥手。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_12.png?raw=true)

##### TCP以段为单位发送数据

<p>
MSS（Maximum Segment Size）：最大消息长度，用于对大数据的分割度量，数据重发时的度量。在建立TCP连接时根据通信双方接口能够适应的MSS决定（取小值）。
</p>

##### 利用窗口控制提高速度

<p>
窗口大小：无需等待确认应答而可以继续发送数据的最大值。
</p>

<p>
滑动窗口控制：使用缓冲区，通过对多个段同时进行确认应答来提高发送性能。缓冲区里的数据会保留到发送成功为止。
</p>

##### 窗口控制与重发控制

<p>
快速的重发服务：接收端在没有收到自己所期望序号的数据时，会对之前收到的数据进行确认应答。发送端则一旦收到某个确认应答后，又连续收到3次同样的确认应答，则认为数据段已丢失，需要进行重发。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_18.png?raw=true)

##### 流控制

<p>
流控制：TCP提供了一种机制，可以让发送端根据接收端的实际接收能力动态的调整发送的数据量。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_19.png?raw=true)

##### 拥塞控制
##### 提高网络利用率规范

##### TCP首部

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_26.png?raw=true)


#### UDP

<p>
UDP（User Datagram Protocol）：不提供复杂的控制机制，利用IP提供面向无连接的通信服务。因此，它不会负责：流量控制、丢包重发等。
</p>

- UDP应用场景：
- 包总量较少的通信（DNS、SNMP等）
- 视频、音频等多媒体通信（即时通讯）
- 限定于LAN等特定网络中的应用通信
- 广播通信（广播、多播）

##### UDP首部

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_6_24.png?raw=true)

- 源端口号：发送端端口号，字段长16位，可为0（表示不需要返回）
- 目标端口号：接收端端口号，字段长16位
- 包长度：UDP的首部长度+数据长度。单位为字节(byte)
- 校验和：用于提供可靠的UDPshoubu和数据

## 路由协议

#### 路由控制的定义

<p>
路由控制：互联网是由路由器连接的网络组合而成，为了能让数据包正确到达目标主机，路由器必须在途中进行正确的转发。
</p>

<p>
静态路由（Static Routing）：事先设置好路由器和主机中并将路由信息固定的一种方法。
</p>

<p>
动态路由（Dynamic Routing）：让路由器协议在运行过程中自动的设置路由控制信息。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_7_1.png?raw=true)

#### 路由控制范围

<p>
IGP（Interior Gateway Protocol）：内部网关协议，自治系统内部动态路由采用的协议。
</p>

<p>
EGP（Exterior Gateway Protocol）：外部网关协议，自治系统之间的路由控制采用的是域间路由协议。
</p>

#### 路由算法

##### 距离向量算法

<p>
距离向量（Distance Vector）：根据距离（代价）和方向决定目标网络或目标主机位置的方法。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_7_4.png?raw=true)

##### 链路状态算法

<p>
链路状态（Link-State）：路由器在了解网络整体连接状态的基础上生成路由控制表的方法。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_7_5.png?raw=true)

#### 路由协议

路由协议名 | 下一层协议 | 方式 | 适用范围 | 循环检测
---|---|---|---|---
RIP | UDP | 距离向量 | 域内 | 不可以
RIP2 | UDP | 距离向量 | 域内 | 不可以
OSFP | IP | 链路状态 | 域内 | 不可以
BGP | TCP | 路径向量 | 对外连接 | 可以

##### RIP

<p>
RIP（Routing Information Protocol）：距离向量型的一种路由协议，广泛用于LAN。
</p>

- 广播路由：将路由控制信息定期（30秒一次）向全网广播
- 确定路由：RIP基于距离向量算法决定路径。距离（Metrics）的单位为“跳数”，指所经过的路由器个数。RIP希望尽可能少通过路由器将数据包转发到目标IP地址

##### OSPF

<p>
OSPF（Open Shortest Path First）：根据OSI的IS-IS协议提出的链路状态型路由协议，即使网络中环路，也能进行稳定的路由控制。
</p>

##### BGP

<p>
BGP（Border Gateway Protocol）：边界网关协议是连接不同组织机构（或者说连接不同自治系统）的一种协议。
</p>

##### MPLS

## 应用协议

#### 远程登录

<p>
远程登录：从本地计算机登录到网络的另一个终端计算功能的应用。
</p>

##### TELNET

<p>
利用TCP的一条连接，通过连接向主机发送文字命令并在主机上执行。
</p>

```
通常情况下TELNET客户端的命令为telnet。
telnet 主机名 端口号
```

##### SSH

<p>
SSH（Secure SHell）：加密的远程登录系统，可加密通信内容，即使被窃听也无法破解所发送的密码，具体命令以及命令返回的结果。
</p>

- 可以使用更强的认证机制
- 可以转发文件
- 可以使用端口转发功能

#### 文件传输

<p>
FTP：是在两个相连的计算机之间进行文件传输时使用的协议。
</p>

<p>
FTP工作时需要使用2条TCP连接，一条用来控制（端口21），一条用来数据传输（端口随机分配）。
</p>

#### 电子邮件

<p>
SMTP（Simple Mail Transfer Protocol）：电子邮件传输协议
</p>

<p>
POP3（Post Office Protocol）：接收邮件时使用的协议
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_8_10.png?raw=true)

##### 邮件地址

<p>
邮件地址：使用电子邮件时需要拥有的地址，相当于通信地址与姓名。
</p>

##### 数据格式

<p>
MIME（Multipurpose Internet Mail Extension）：由首部和正文部分组成。
</p>

<p>
如果MIME首部的“Content-Type”中指定“Multipart/Mixed”，并以“boundary==”后面的字符串作为分隔符，可将多个MIME消息组合成为一个MIME消息。这就叫做multipart。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_3.png?raw=true)

##### 发送与接收协议

<p>
SMTP：发送电子邮件的协议，使用的端口25。建立TCP连接后，在这个连接上进行控制，应答以及数据的发送。
</p>

<p>
POP：从POP服务器接收邮件，需要用户验证。
</p>

<p>
IMAP（Internet Mail Access Protocol）：在服务器上保存和管理邮件。
</p>

#### WWW

<p>
万维网（WWW，World Wide Web）：将互联网中的信息以超文本形式展示的系统。使用WEB浏览器来显示WWW信息。
</p>

##### URI

<p>
URL（Uniform Resource Locator）：表示互联网中资源（文件）的具体位置。  
</p>

<p>
URI（Uniform Resource Identifier）：不局限于标记互联网资源，可作为所有资源的识别码。  
</p>

[uri schemes](http://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml)

##### HTML

<p>
HTML（HyperText Markup Language）：记述Web页的一种语言（数据格式），可指定浏览器中显示文字，文字的大小和颜色等。
</p>

##### HTTP

<p>
HTML（HyperText Transfer Protocol）：Http默认使用80端口，在建立TCP连接后，在这个TCP连接上进行请求的应答以及数据报文的发送。  
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/tu_8_18.png?raw=true)

- HTTP1.0：每一个命令和应答都会触发一次TCP连接的建立与断开
- HTTP1.1：一个TCP连接上发送多个命令和应答

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_1.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_2.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_3.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_4.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_5.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-05-22-tcp-ip/biao_8_8_6.png?raw=true)

#### 网络管理

<p>
SNMP（Simple Network Management Protocol）：基于UDP/IP的协议，管理端叫做管理器（Manager，网络终端监控），被管理端叫做代理（路由器，交换机）。
</p>

<p>
MIB（Management Information Base）：SNMP中交互的信息，是在树形结构的数据库中为每个项目附件编号的一种信息结构。
</p>

## 参考资料

[图解TCP/IP](https://item.jd.com/11253710.html)
