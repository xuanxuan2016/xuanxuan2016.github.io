---
layout:     post
title:      "mysql安装"
subtitle:   "Install Mysql"
date:       2018-08-14 16:20
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - mysql
---

> 介绍在linux上使用二进制文件来安装mysql服务。

## 依赖安装

#### 1.libaio

```
[root@vagrant bmsource]# yum install libaio
```

#### 2.numactl

```
[root@vagrant local]# yum install numactl
```

## mysql安装

#### 1.下载

[下载地址](https://dev.mysql.com/downloads/mysql/)

<p>
查看本机操作系统，64位的，所以选择下载64位的安装包。
</p>

```
[root@vagrant bmsource]# uname -a
Linux vagrant.localhost 2.6.32-573.el6.x86_64 #1 SMP Thu Jul 23 15:44:03 UTC 2015 x86_64 x86_64 x86_64 GNU/Linux
```

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-14-install-mysql/20180815103751.png?raw=true)

#### 2.安装

<p>
解压tar文件
</p>

```
[root@vagrant bmsource]# cp /www/htdocs/mysql-8.0.12-linux-glibc2.12-x86_64.tar /bmsource/mysql-8.0.12-linux-glibc2.12-x86_64.tar
[root@vagrant bmsource]# tar -zxf mysql-8.0.12-linux-glibc2.12-x86_64.tar
[root@vagrant bmsource]# tar xvf mysql-8.0.12-linux-glibc2.12-x86_64.tar.xz
```

<p>
将解压后的文件移到<code>/usr/local/mysql</code>
</p>

```
[root@vagrant bmsource]# mv mysql-8.0.12-linux-glibc2.12-x86_64 /usr/local/mysql
```

## 环境配置

#### 1.用户组与用户

<p>
为了可以运行mysql，需要创建mysql组与mysql用户。
</p>

```
[root@vagrant bmsource]# groupadd mysql
[root@vagrant bmsource]# useradd mysql -g mysql 
```

#### 2.mysql目录组与用户

<p>
修改mysql目录所属的组与用户。
</p>

```
[root@vagrant local]# chown -R mysql:mysql mysql/
```

#### 3.mysql运行目录

<p>
mysql运行目录<code>/mysql</code>用于存放数据文件，pid，log等信息。
</p>

```
[root@vagrant etc]# cd /
[root@vagrant /]# mkdir mysql
[root@vagrant /]# chown -R mysql:mysql mysql/
```

<p>
数据目录<code>/mysql/data</code>
</p>

```
[root@vagrant etc]# cd /mysql
[root@vagrant mysql]# mkdir data
[root@vagrant mysql]# chown -R mysql:mysql data
```

<p>
日志目录<code>/mysql/logs</code>
</p>

```
[root@vagrant etc]# cd /mysql
[root@vagrant mysql]# mkdir logs
[root@vagrant mysql]# chown -R mysql:mysql logs
```

<p>
临时目录<code>/mysql/tmpdir</code>
</p>

```
[root@vagrant etc]# cd /mysql
[root@vagrant mysql]# mkdir tmpdir
[root@vagrant mysql]# chown -R mysql:mysql tmpdir
```

#### 4.配置文件

<p>
mysql的良好运行与参数配置至关重要，这里创建my.cnf文件，然后进行一些初始化的修改。
</p>

[my.cnf](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2018-08-14-install-mysql/my.cnf)

```
[root@vagrant etc]# cd /etc
[root@vagrant etc]# touch my.cnf
[root@vagrant etc]# chown mysql:mysql my.cnf
```

#### 5.环境变量

<p>
为了使用mysql命令方便，可以将其添加到PATH环境变量中。
</p>

```
[root@vagrant /]# vim /etc/profile

--add
export PATH=$PATH:/usr/local/mysql/bin

[root@vagrant /]# source /etc/profile
```

## 使用数据库

#### 1.初始化数据库

<p>
建立系统库与表等。
</p>

<p>
初始化过程中如果报<code>unknown variable</code>，先把相关的变量注释掉。
</p>

```
[root@vagrant etc]# /usr/local/mysql/bin/mysqld --initialize-insecure --user=mysql --basedir=/usr/local/mysql --datadir=/mysql/data
```

#### 2.启动服务

<p>
将mysql服务复制到<code>/etc/init.d</code>
</p>

```
[root@vagrant init.d]# cp /usr/local/mysql/support-files/mysql.server /etc/init.d/mysqld
[root@vagrant init.d]# service mysqld start
Starting MySQL... SUCCESS! 
[root@vagrant init.d]# netstat -anp|grep 3306
tcp        0      0 :::33060                    :::*                        LISTEN      10138/mysqld        
tcp        0      0 :::3306                     :::*                        LISTEN      10138/mysqld
```

<p>
添加开机启动
</p>

```
[root@vagrant /]# chkconfig --add mysqld
[root@vagrant /]# chkconfig --list mysqld
mysqld          0:off   1:off   2:on    3:on    4:on    5:on    6:off
```

#### 3.更新root密码

<p>
初始化mysql时生成的root用户，默认是没有密码的，为了安全需要为其设置一个密码。
</p>

```
mysql> alter user 'root'@'localhost' IDENTIFIED BY '123456';
```

#### 4.添加用户

<p>
为了给外部使用，需要创建其他的用户。
</p>

```
create user test@127.0.0.1 identified by '123456';
```

<p>
在使用test账号登录的时候，出现了此错误。
</p>

```
[root@vagrant ~]# mysql -h 127.0.0.1 -u test -p
Enter password: 
ERROR 2061 (HY000): Authentication plugin 'caching_sha2_password' reported error: Authentication requires secure connection.
```

<p>
经过查询，发现密码加密的方式默认为<code>caching_sha2_password</code>，此种方式的加密需要安全连接。删除此账号，重新创建账号使用加密方式为<code>mysql_native_password</code>。
</p>

```
mysql> create user test@127.0.0.1 identified with mysql_native_password by '123456';
```

<p>
为账号增加数据库权限。
</p>

```
mysql> grant all privileges on devmanager.* to test@127.0.0.1;
Query OK, 0 rows affected (0.09 sec)

mysql> show grants for test@127.0.0.1;
+--------------------------------------------------------------+
| Grants for test@127.0.0.1                                    |
+--------------------------------------------------------------+
| GRANT USAGE ON *.* TO `test`@`127.0.0.1`                     |
| GRANT ALL PRIVILEGES ON `devmanager`.* TO `test`@`127.0.0.1` |
+--------------------------------------------------------------+
```


## 程序连接数据库

<p>
当使用php代码连接mysql8.0时，遇到了如下2个问题。
</p>

#### 1.PDO::__construct(): Server sent charset (255) unknown to the client

<p>
修改<code>my.cnf</code>中如下编码配置
</p>

```
[client]
default-character-set=utf8

[mysql]
default-character-set=utf8

[mysqld]
collation-server = utf8_unicode_ci
character-set-server = utf8
```

#### 2.The server requested authentication method unknown to the client

<p>
因为php版本为5.6.18还不支持mysql8.0默认的<code>caching_sha2_password</code>密码插件，所以php连接mysql的用户需要以<code>mysql_native_password</code>插件创建，同时my.cnf的mysqld下需要有如下配置
</p>

```
default-authentication-plugin=mysql_native_password
```

<p>
重启mysql服务后，测试正常
</p>

```
[root@vagrant ~]# service mysqld restart
```

## 参考资料

[Installing MySQL on Unix/Linux Using Generic Binaries](https://dev.mysql.com/doc/refman/8.0/en/binary-installation.html)

[mysql之my.cnf](https://blog.csdn.net/qing_gee/article/details/49507817)

[阿里数据库内核月报](http://mysql.taobao.org/monthly/)

[mysql8.0初探](http://blog.51cto.com/arthur376/2108183?utm_source=oschina-app)

[mysql 用户及权限管理](https://www.cnblogs.com/SQL888/p/5748824.html)

[PHP错误：SQLSTATE[HY000] [2054] The server requested authentication method unknown to the client](https://www.e-learn.cn/content/php/991006)
