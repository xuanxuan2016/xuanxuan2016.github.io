---
layout:     post
title:      "图解HTTP笔记"
subtitle:   "tcp ip"
date:       2019-06-24 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - http
---

> 介绍了HTTP协议报文的构成，响应状态码，请求与响应的首部字段。为了应对新场景需求的Ajax，Comet，WebSocket等技术。通过HTTPS与用户认证实现安全通信，一些常见的Web攻击技术。

## 了解Web

#### TCP/IP通信传输流

<p>
发送端：在层与层之间传输数据时，每经过一层时必定会被打上一个该层所属的首部信息。
</p>

<p>
接收端：在层与层传输数据时，每经过一层时会把对应的首部去掉。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_1.png?raw=true)

#### 各协议与HTTP协议的关系

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_2.png?raw=true)

#### URI与URL

- URI：统一资源定位符
- URL：统一资源定位符，属于URI的子集

##### URI格式

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_3.png?raw=true)

- 协议名：使用<code>http:</code>或<code>https:</code>等协议方案名获取访问资源时要指定协议类型。不区分字母大小写，最后附加一个冒号<code>:</code>
- 登录信息（可选）：指定用户名和密码作为从服务器端获取资源时必要的登录信息
- 服务器地址：使用绝对URI必须指定待访问的服务器地址。如域名，IPv4地址，IPv6地址
- 服务器端口号（可选）：指定服务器连接的网络端口号
- 带层次的文件路径：指定服务器上的文件路径来定位特指的资源
- 查询字符串（可选）：针对已指定的文件路径内的资源，可以使用查询字符串传入任意参数
- 片段标识符（可选）：标记已获取资源的子资源

## 简单的HTTP协议

#### HTTP协议的作用

<p>
HTTP协议用于客户端与服务端之间的通信，应用HTTP协议时，必定是一端担任客户端角色，另一端担任服务器端角色。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_4.png?raw=true)

#### HTTP协议的工作方式

<p>
HTTP协议通过请求和响应的交换达成通信，请求从客户端发出，最后服务器端响应请求并返回。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_5.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_6.png?raw=true)

#### HTTP支持的方法

<p>
向请求URI指定的资源发送请求报文时，采用称为<code>方法</code>的命令，方法名区分大小写，注意要使用<code>大写字母</code>。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_7.png?raw=true)

#### HTTP持久连接

<p>
持久连接（HTTP Persistent Connections/HTTP Keep-alive）：只要客户端或服务端的任意一端没有明确提出断开连接，则保持TCP连接状态。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_8.png?raw=true)

<p>
管线化（pipelining）：不用等待每个请求的响应，直接发送下一个请求。
</p>

#### Cookie

<p>
Cookie通过在请求和响应报文中写入Cookie信息来控制客户端的状态。
</p>

## HTTP报文内的HTTP信息

#### HTTP报文

<p>
HTTP报文：由<code>报文首部</code>和<code>报文主体</code>构成，使用<code>CR+LF</code>分割。客户端为请求报文，服务端为响应报文。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_9.png?raw=true)

#### 请求报文与响应报文结构

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_10.png?raw=true)

- 请求行：用于请求的方法，请求的URI或HTTP版本
- 状态行：表明响应结果的状态码，原因短语和HTTP版本
- 首部字段：表示请求和响应的各种条件和属性的各类首部

#### 编码传输

##### 报文主体与实体主体

<p>
HTTP报文的主体用于传输请求或响应的实体主体。
</p>

- 报文（message）：HTTP通信的基本单位，由8位组字节流组成，通过HTTP传输
- 实体（entity）：作为请求或响应的有效载荷数据被传输，由实体首部和实体主体组成

##### 常用压缩编码

- gzip（GUN zip）
- compress（UNIX系统的标准压缩）
- deflate（zlib）
- identity（不进行编码）

##### 发送多种数据

<p>
类似于发送邮件时的多个附件，HTTP协议也采纳了多部分对象集合，发送的一份报文主体内可含有多类型实体。通常用在图片或文件的上传。
</p>

- multipart/form-data；在Web表单文件上传时使用

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_11.png?raw=true)

<p>
使用<code>boundary</code>字符串来划分多部分对象集合指明的各类实体。在<code>boundary</code>字符串指定的各个实体的起始行之前插入<code>--</code>标记（如:--AaB03x），而在多部分对象集合对应的字符串的最后插入<code>--</code>标记（如:--AaB03x--）作为结束。
</p>

## 返回结果的HTTP状态码

<p>
状态码：当客户端向服务器端发送请求时，描述返回的请求结果。
</p>


状态码分类 | 类别 | 原因短语
---|---|---
1** | Informational（信息性状态码） | 接收的请求正在处理
2** | Success（成功状态码） | 请求正常处理完毕
3** | Redirection（重定向状态码） | 需要进行附加操作以完成请求
4** | Client Error（客户端错误状态码） | 服务器无法处理请求
5** | Server Error（服务端错误状态码） | 服务器处理请求出错

#### 2**成功

- 200 OK：从客户端发来的请求在服务器端被正常处理了
- 204 No Content：服务器接收的请求已成功处理，但在返回的响应报文中不含实体的主体部分
- 206 Partial Content：客户端进行了范围请求，服务器成功执行了这部分GET请求

#### 3**重定向

- 301 Moved Permanently：永久性重定向。请求的资源已被分配了新的URI，以后应使用新URI访问资源
- 302 Found：临时性重定向。请求的资源已被分配了新的URI，用户（本次）能使用新的URI访问
- 303 See Other：请求对应的资源存在着另一个URI，应使用GET方法重定向获取请求的资源
- 304 Not Modified：客户端发送附带条件（If-Match,If-Modified-Since,If-None-Match,If-Range,If-Unmodified-Since）的请求，服务器端允许请求访问资源，但条件未满足
- 307 Temporary Redirect：临时重定向

#### 4**客户端错误

- 400 Bad Request：请求报文中存在语法错误
- 401 Unauthorized：发送的请求需要有通过HTTP认证（BASIC认证，DIGEST认证）的认证信息
- 403 Forbidden：对请求资源的访问被服务器拒绝了
- 404 Not Found：服务器上无法找到请求的资源

#### 5**服务器错误

- 500 Internal Server Error：服务器端在执行请求时发生了错误
- 503 Service Unavailable：服务器暂时处于超负载或正在进行停机维护

## 与HTTP协作的Web服务器

#### 虚拟主机

<p>
在一台HTTP服务器上可通过虚拟主机（Virtual Host）搭建多个Web站点。
</p>

#### 通信数据转发

##### 代理

<p>
代理是一种有转发功能的程序，它扮演了位于服务器和客户端<code>中间人</code>的角色，接收由客户端发来的请求并转发给服务器，同时接收服务器返回的响应并转发给客户端。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_12.png?raw=true)

<p>
代理的作用：利用缓存技术减少网络带宽的流量；针对特定网站进行访问控制
</p>

- 缓存代理（Caching Proxy）：预先将资源副本保存在代理服务器上，供请求获取
- 透明代理（Transparent Proxy）：转发请求或响应时，不对报文做任何加工

##### 网关

<p>
网关是转发其他服务器通信数据的服务器，接收从客户端发来的请求时，它就像自己拥有资源的源服务器一样对请求处理。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_13.png?raw=true)

<p>
网关的作用：提高通信的安全性；使通信线路上的服务器提供非HTTP协议服务
</p>

##### 隧道

<p>
隧道是在相隔较远的客户端与服务器之间进行中转，并保持双方通信连接的程序。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_14.png?raw=true)

<p>
隧道的作用：建立一条与其他服务器的通信线路，通过SSL加密手段，确保客户端能与服务器进行安全通信
</p>

#### 缓存

<p>
缓存：代理服务器或客户端本地磁盘内保存的副本资源。
</p>

##### 缓存的有效性

<p>
需要根据客户端的请求、缓存的有效性等因素，向源服务器确认资源的有效性，从而确认是否需要更新缓存。
</p>

##### 客户端缓存

<p>
缓存的功能同代理服务器，缓存的位置在客户端浏览器中，同样也需要根据有效性更新缓存。
</p>

## HTTP首部

#### http首部字段

<p>
在客户端与服务器之间以HTTP协议进行通信的过程中，无论是请求还是响应都会使用首部字段，起到传递额外重要信息的作用。
</p>

##### 首部字段结构

<p>
首部字段由<code>首部字段名</code>和<code>字段值</code>构成，使用<code>:</code>分隔。
</p>

```
格式：
首部字段名:字段值

如：
Content-Type:text/html
Keep-Alive:timeout=5,max=100
```

##### 4种首部字段类型

- 通用首部字段（General Header Fields）：请求报文和响应报文都会使用的首部
- 请求首部字段（Request Header Fields）：从客户端向服务器端发送请求报文时使用的首部
- 响应首部字段（Response Header Fields）：从服务器端向客户端返回响应报文时使用的首部
- 实体首部字段（Entity Header Fields）：请求报文和响应报文的实体部分使用的首部

##### 按代理转发区分

<p>
缓存代理（端到端首部 End-to-end Header）：在此类别中的首部会转发给请求/响应对应的最终接收目标，且必须保存在由缓存生成的响应中，必须被转发。
</p>

<p>
非缓存代理（逐跳首部 Hop-by-hop Header）：在此类别中的首部只对单次转发有效，会因为通过缓存或代理而不再转发，如果要使用此首部需提供Connection首部字段。
</p>

- 属于逐跳首部的字段，除此之外都是端到端首部
- Connection
- Keep-Alive
- Proxy-Authenticate
- Proxy-Authorization
- Trailer
- TE
- Transfer-Encoding
- Upgrade

#### 通用首部字段


首部字段名 | 说明
---|---
Cache-Control | 控制缓存的行为
Connection | 逐跳首部，连接的管理
Date       | 创建报文的日期时间
Pragma     | 报文指令
Trailer       | 报文末端的首部一览
Transfer-Encoding       | 指定报文主体的传输编码方式
Upgrade       | 升级为其他协议
Via       | 代理服务器的相关信息
Warning       | 错误通知

##### Cache-Control

<p>
通过指定Cache-Control的指令，来控制缓存的工作机制。指令的参数是可选的，多个指令之间通过<code>,</code>分隔。
</p>

```
Cache-Control:private,max-age=0,no-cache
```

<p>
请求的缓存指令
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_15.png?raw=true)

<p>
响应的缓存指令
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_16.png?raw=true)

##### Connection

- 控制不再转发给代理的首部字段
- 管理持久连接（keep-alive,close）

#### 请求首部字段

首部字段名 | 说明
---|---
Accept | 用户代理可处理的媒体类型
Accept-Charset | 优先的字符集
Accept-Encoding       | 优先的内容编码
Accept-Language     | 优先的语言（自然语言）
Authorization | Web认证信息
Expect | 期待服务器的特性行为
From | 用户的电子邮箱地址
Host | 请求资源所在的服务器
If-Match | 比较实体标记（ETag）
If-None-Match | 比较实体标记（与If-Match相反）
If-Modified-Since | 比较资源的更新时间
If-Unmodified-Since | 比较资源的更新时间（与If-Modified-Since相反）
If-Range | 资源未更新时发送实体Byte的范围请求
Max-Forwards | 最大传输逐跳数
Proxy-Authorization | 代理服务器要求客户端的认证信息
Range | 实体的字节范围请求
Referer | 对请求中URI的原始获取方
TE | 传输编码的优先级
User-Agent | HTTP客户端程序的信息

##### Accept

<p>
告知服务端用户代理能够处理的媒体类型及相对优先级，使用<code>type/subtype</code>格式表示，可指定多种媒体类型，使用<code>,</code>分隔。
</p>

[可用媒体类型](http://www.w3school.com.cn/media/media_mimeref.asp)

<p>
媒体类型优先级，使用<code>q=</code>来表示权重值（范围：0-1）。不指定权重时，默认权重为<code>q=1.0</code>。当服务器提供多种内容时，优先返回权值较高的媒体类型。
</p>

##### Authorization

<p>
用户代理的认证信息。
</p>

```
格式：
Authorization:Basic 认证信息

python:
认证信息=base64.b64encode("%s:%s" % (username,password))
```

#### 响应首部字段

首部字段名 | 说明
---|---
Accept-Ranges | 是否接收字节范围请求
Age | 推算资源采集经过的时间
ETag | 资源的匹配信息
Location | 令客户端重定向到指定的URI
Proxy-Authenticate | 代理服务器对客户端的认证信息
Retry-After | 对再次发起请求的时机要求
Server | HTTP服务器的安装信息
Vary | 代理服务器缓存的管理信息
WWW-Authenticate | 服务器对客户端的认证信息

#### 实体首部字段

首部字段名 | 说明
---|---
Allow | 资源可支持的HTTP方法
Content-Encoding | 实体主体适用的编码方式
Content-Language | 实体主体的自然语言
Content-Length | 实体主体的大小（单位：字节）
Content-Location | 替代对应资源的URI
Content-MD5 | 实体主体的报文摘要
Content-Range | 实体主体的位置范围
Content-Type | 实体主体的媒体类型
Expires | 实体主体过期的日期时间
Last-Modified | 资源的最后修改日期时间

#### Cookie首部字段

首部字段名 | 说明 | 首部类型
---|---|---
Set-Cookie | 开始状态管理所使用的Cookie信息(服务端->客户端) | 响应首部字段
Cookie | 服务器接收到的Cookie信息(客户端->服务端) | 请求首部字段

##### Set-Cookie

属性 | 说明
---|---
NAME=VALUE | 赋予Cookie的名称和值(必填)
expires=DATE | Cookie有效期(不指定的话默认为浏览器关闭为止)
path=PATH | 将服务器上文件目录作为Cookie的适用对象(默认值是设置 Cookie 时的当前目录)
domain=域名 | 作为Cookie适用对象的域名(默认值是设置 Cookie 时的域名)
Secure | 仅在https安全通信时才会发送Cookie
HttpOnly | 增加限制，使Cookie不能被JavaScript访问

## HTTP的追加协议

#### HTTP自身改进

##### Ajax

<p>
Ajax（异步JavaScript与XML技术）：可异步加载数据，以达到局部更新Web页面。
</p>

<p>
缺点：利用Ajax实时地从服务器获取内容，可能会导致大量的请求产生，如果服务器内容未更新，还会有很多无效请求。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_19.png?raw=true)

##### Comet

<p>
接收到请求时，Comet先将响应挂起，当服务器端有内容更新时，再返回响应，达到实时更新的目的。
</p>

<p>
缺点：为了保留响应，一次连接的持续时间变长，为了维持连接会消耗更多的资源。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_20.png?raw=true)

#### WebSocket

<p>
由客户端发起连接，一旦建立WebSocket协议的通信连接后，所有的通信都依靠这个专用协议进行，可互相发送JSON、XML、HTML、图片等任意数据格式。
</p>

- 推送功能：不必等待客户端请求，就可以直接从服务端向客户端发送数据
- 减少通信量：只要WebSocket连接后，就可以一直通信；WebSocket的首部信息也较少

<p>
为了实现WebSocket通信，在HTTP连接建立后，需要完成一次握手。
</p>

```
#握手-请求
GET /chat HTTP/1.1 
Host: server.example.com 
Upgrade: websocket //告诉服务器现在发送的是WebSocket协议
Connection: Upgrade 
Sec-WebSocket-Key: x3JJHMbDL1EzLkh9GBhXDw== //是一个Base64encode的值，这个是浏览器随机生成的，用于验证服务器端返回数据是否是WebSocket助理
Sec-WebSocket-Protocol: chat, superchat 
Sec-WebSocket-Version: 13 
Origin: http://example.com
```

```
#握手-响应
HTTP/1.1 101 Switching Protocols 
Upgrade: websocket //依然是固定的，告诉客户端即将升级的是Websocket协议，而不是mozillasocket，lurnarsocket或者shitsocket
Connection: Upgrade 
Sec-WebSocket-Accept: HSmrc0sMlYUkAGmm5OPpG2HaGWk= //这个则是经过服务器确认，并且加密过后的 Sec-WebSocket-Key,也就是client要求建立WebSocket验证的凭证
Sec-WebSocket-Protocol: chat
```

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_21.png?raw=true)

## HTTPS安全通信

#### HTTP的缺点

- 通信使用明文（不加密），内容可能会被窃听
- 不验证通信方的身份，有可能遭遇伪装
- 无法证明报文的完整性，有可能已遭篡改

#### HTTPS

<p>
HTTPS（HTTP Secure）：HTTP+通信加密+证书+完整性保护
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_17.png?raw=true)

<p>
HTTPS并非是应用层的一种新协议，只是HTTP通信接口部分用SSL（Secure Socket Layer 安全套接层）和TLS（Transport Layer Security 安全层传输协议）协议代替而已。通常，HTTP直接和TCP通信，当使用SSL时，则变为先和SSL通信，再由SSL和TCP通信。<span style='color:red;font-weight:bold;'>所谓HTTPS，就是身披SSL协议外壳的HTTP</span>
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_18.png?raw=true)

<p>
SSL是独立于HTTP的协议，所以不光是HTTP协议，其他运行在应用层的SMTP和Telnet等协议均可配合SSL协议使用。可以说，SSL是当今世界上应用最为广泛的网络安全技术。在采用了SSL之后，HTTP就拥有了HTTPS的加密、证书和完整性保护这些功能。
</p>

## 用户身份认证

#### BASIC认证（基本认证）

<p>
不够灵活，达不到多数Web网站期望的安全性等级（直接发送明文密码BASE64编码），因此它并不常用。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_24.png?raw=true)

#### DIGEST认证（摘要认证）

<p>
采用质询响应方式，相比BASIC认证，密码泄露的可能性就降低了。相对HTTPS认证较弱，因此适用范围有限。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_25.png?raw=true)

#### SSL客户端认证

<p>
借助HTTPS的客户端证书完成认证，凭借客户端证书认证，服务器可确认访问是否来自已登录的客户端。
</p>

#### FormBase认证（表单认证）

<p>
将客户端发送到服务端的账号和密码与数据库中的信息进行匹配，再借助Cookie与Session的机制来完成认证。。  
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_26.png?raw=true)

## Web攻击技术

#### 攻击模式

##### 主动攻击

<p>
攻击者直接访问web应用，把攻击代码传入的攻击模式。主要有SQL注入攻击，OS命令攻击。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_22.png?raw=true)

##### 被动攻击

<p>
利用圈套策略执行攻击代码的攻击模式，在被动攻击过程中，攻击者不直接对目标Web应用访问发起攻击。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-06-24-tujie-http/tu_23.png?raw=true)

#### 攻击类型

##### SQL注入攻击(主动)

<p>
SQL注入（SQL Injection）是指针对Web应用使用的数据库，通过运行非法的SQL而产生的攻击。该安全隐患有可能引起极大的威胁，有时会直接导致个人信息及机密信息的泄露。
</p>

- 非法查看或篡改数据库内的数据
- 规避认证
- 执行和数据库服务器业务关联的程序等

##### OS命令注入攻击(主动)

<p>
OS命令注入攻击（OS Command Injection）是指通过Web应用，执行非法的操作系统命令达到攻击的目的。只要在能调用Shell函数的地方就有存在被攻击的风险。
</p>

##### Dos攻击(主动)

<p>
DoS攻击（Denial of Service attack）是一种让运行中的服务呈停止状态的攻击。有时也叫作服务停止或拒绝服务攻击
</p>

- 集中利用访问请求造成资源过载，资源用尽的同时，实际上也就呈停止状态
- 通过攻击安全漏洞使服务停止

##### 跨站脚本攻击(被动)

<p>
跨站脚本攻击（Cross-Site Scripting，XSS）是指通过存在安全漏洞的Web网站注册用户的浏览器内运行非法的HTML标签或者JavaScript脚本进行攻击的一种攻击。
</p>

- 利用虚假输入表单骗取用户个人信息
- 利用脚本窃取用户的Cookie值，被害者在不知情的情况下，帮助攻击者发送恶意请求
- 显示伪造的文章或者图片
 
##### HTTP首部注入攻击(被动)

<p>
HTTP首部注入攻击（HTTP Header Injection）是指攻击者通过在响应首部字段内插入换行，添加任意响应首部或主体的一种攻击。
</p>

##### 会话管理疏忽引发的漏洞(被动)

- 会话劫持（Session Hijack）：攻击者通过某种手段拿到了用户的会话ID，并非法使用此会话ID伪装成用户，达到攻击的目的
- 会话固定攻击（Session Fixation）：强制用户使用攻击者指定的会话ID
- 跨站点请求伪造（Cross-Site Request Forgeries，CSRF）：攻击者通过设置好的陷阱，强制对已完成认证的用户进行非预期的个人信息或设定信息等某些状态更新
 
## 参考资料

[图解HTTP](https://item.jd.com/11449491.html)

[HTTP首部Connection实践](https://www.jianshu.com/p/eba76cfc0424)

[浅谈php中使用websocket](https://www.cnblogs.com/jiangzuo/p/5896301.html)

[Swoole WebSocket](https://wiki.swoole.com/wiki/page/397.html)