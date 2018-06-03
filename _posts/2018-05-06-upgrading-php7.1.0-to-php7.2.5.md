---
layout:     post
title:      "升级php版本从7.1.0到7.2.5"
subtitle:   "upgrading-php7.1.0-to-php7.2.5"
date:       2018-05-06 20:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - linux
---

> 最近需要学习lavavel框架，框架需要php7.1.10以上的版本，所以将阿里云机器上的php进行升级，这里记录一下升级的过程。

## 下载并安装php7.2.5

#### 1.下载安装文件

-O：表示将下载后的文件进行重命名
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# wget http://am1.php.net/get/php-7.2.5.tar.bz2/from/this/mirror -O php-7.2.5.tar.bz2
```

解压安装文件
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -xvf php-7.2.5.tar.bz2 
```

#### 2.获取7.1.0版本的configure
这次只是php版本的升级，希望所有的扩展需求同7.1.0版本的，所以这里获取一下7.1.0安装的配置信息。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# php -i|grep configure
Configure Command =>  './configure'  '--prefix=/usr/local/php '--with-apxs2=/www/bin/apxs' '--with-curl' '--with-freetype-dir' '--with-gd' '--with-gettext' '--with-iconv-dir' '--with-kerberos' '--with-libdir=lib64' '--with-libxml-dir' '--with-mysqli' '--with-openssl' '--with-pcre-regex' '--with-pdo-mysql' '--with-pdo-sqlite' '--with-pear' '--with-png-dir' '--with-xmlrpc' '--with-xsl' '--with-zlib' '--enable-fpm' '--enable-bcmath' '--enable-libxml' '--enable-inline-optimization' '--enable-gd-native-ttf' '--enable-mbregex' '--enable-mbstring' '--enable-opcache' '--enable-pcntl' '--enable-shmop' '--enable-soap' '--enable-sockets' '--enable-sysvsem' '--enable-xml' '--enable-zip'
```

#### 3.完成安装
通过上面获取到的配置可知，7.1.0版本的安装位置为<code>/usr/local/php</code>，所以将新版本安装到<code>/usr/local/php7.2.5</code>。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cd php-7.2.5
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]#  ./configure --prefix=/usr/local/php7.2.5 --with-apxs2=/www/bin/apxs  --with-curl  --with-freetype-dir  --with-gd  --with-gettext  --with-iconv-dir  --with-kerberos  --with-libdir=lib64  --with-libxml-dir   --with-mysqli  --with-openssl  --with-pcre-regex  --with-pdo-mysql  --with-pdo-sqlite  --with-pear  --with-png-dir  --with-xmlrpc  --with-xsl  --with-zlib  --enable-fpm  --enable-bcmath  --enable-libxml  --enable-inline-optimization  --enable-gd-native-ttf  --enable-mbregex  --enable-mbstring  --enable-opcache  --enable-pcntl  --enable-shmop  --enable-soap  --enable-sockets  --enable-sysvsem  --enable-xml  --enable-zip
[root@iZwz9i8fd8lio2yh3oerizZ php-7.2.5]#  make && make install
```

## 修改配置
#### 1.php.ini
因为在configure的时候加了<code>--with-apxs2=/www/bin/apxs</code>配置，所以php安装完成后会自动更新<code>/www/modules/libphp7.so</code>。
这时新安装的目录中还没有php.ini配置文件，可以将原来的配置文件复制过来。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ php-7.2.5]# cp /usr/local/php/lib/php.ini /usr/local/php7.2.5/lib/php.ini
```
#### 2.环境变量
使用php -v查看版本，发现还是之前的版本，此时需要修改PATH变量，将php路径修改为新版本的路径。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ ~]# php -v
PHP 7.1.0 (cli) (built: May  5 2018 15:38:09) ( ZTS )
Copyright (c) 1997-2018 The PHP Group
Zend Engine v3.2.0, Copyright (c) 1998-2018 Zend Technologies
```
查看当前PATH变量信息。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# env |grep PATH
PATH=/usr/local/php/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin
```
方法1：export直接设置，注意这里是替换原来php的目录
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# export PATH='/usr/local/php7.2.5/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/root/bin'
```
方法2：如果以前环境变量还没有，可以修改<code>/etc/profile</code>文件，在最后一行加入配置<code>export PATH="/usr/local/php7.2.5/bin:$PATH"</code>，表示在原有设置的基础上增加新的设置。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# vim /etc/profile
```
## 重新编译第三方扩展
如果使用了原php版本编译的扩展，因为php升级了，所以相应的扩展也需要重新编译。
#### 1.redis扩展
注意：phpize与php-config需要使用新版本的。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -xvf redis-3.1.2.tgz 
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cd redis-3.1.2
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# phpize
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# ./configure --with-php-config=/usr/local/php7.2.5/bin/php-config
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# make && make install
```
将新编译的扩展，复制到php加载的扩展目录。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# cp /usr/local/php7.2.5/lib/php/extensions/no-debug-zts-20170718/redis.so /www/modules/redis7.2.5.so
```
修改php.ini使用新的扩展，<code>extension=redis7.2.5.so</code>。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# vim /usr/local/php7.2.5/lib/php.ini 
```
#### 2.mcrypt扩展
注意：phpize与php-config需要使用新版本的。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2] cd /bmsource/
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# wget http://pecl.php.net/get/mcrypt-1.0.1.tgz
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# tar -xvf mcrypt-1.0.1.tgz
[root@iZwz9i8fd8lio2yh3oerizZ bmsource]# cd mcrypt-1.0.1
[root@iZwz9i8fd8lio2yh3oerizZ mcrypt-1.0.1]# phpize
[root@iZwz9i8fd8lio2yh3oerizZ mcrypt-1.0.1]# ./configure --with-php-config=/usr/local/php7.2.5/bin/php-config
[root@iZwz9i8fd8lio2yh3oerizZ mcrypt-1.0.1]# make && make install
```
将新编译的扩展，复制到php加载的扩展目录。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ mcrypt-1.0.1]# cp /usr/local/php7.2.5/lib/php/extensions/no-debug-zts-20170718/mcrypt.so /www/modules/mcrypt7.2.5.so
```
修改php.ini使用新的扩展，<code>extension=mcrypt7.2.5.so</code>。
```linux
[root@iZwz9i8fd8lio2yh3oerizZ redis-3.1.2]# vim /usr/local/php7.2.5/lib/php.ini 
```


