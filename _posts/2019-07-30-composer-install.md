---
layout:     post
title:      "composer安装"
subtitle:   "composer install"
date:       2019-07-30 20:30
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
    - linux
---

## 安装

```
[root@localhost bmsource]# php -r "copy('https://install.phpcomposer.com/installer', 'composer-setup.php');"
[root@localhost bmsource]# php composer-setup.php
#移动到bin目录，供全局调用
[root@localhost bmsource]# mv composer.phar /usr/local/bin/composer
```

## 添加国内镜像

```
#全局配置
[root@localhost bmsource]# composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
#取消全局配置
[root@localhost bmsource]# composer config -g --unset repos.packagist

#项目配置
[root@localhost bmsource]# composer config repo.packagist composer https://mirrors.aliyun.com/composer/
#取消项目配置
[root@localhost bmsource]# composer config --unset repos.packagist
```

## 常用命令

#### 安装依赖包

##### install

<p>
需要事先创建composer.json文件,来描述项目的依赖关系。
</p>

```
{
    "require": {
        "monolog/monolog": "1.2.*"
    }
}
```

```
[root@localhost test]# composer install
```

##### require

<p>
可以使用require命令快速的安装一个依赖而不需要手动在composer.json里添加依赖信息
</p>

```
[root@localhost test]# composer require monolog/monolog
```

#### update

<p>
update 命令用于更新项目里所有的包，或者指定的某些包：
</p>

```
# 更新所有依赖
$ composer update

# 更新指定的包
$ composer update monolog/monolog

# 更新指定的多个包
$ composer update monolog/monolog symfony/dependency-injection

# 还可以通过通配符匹配包
$ composer update monolog/monolog symfony/*
```

#### remove

<p>
移除一个包及其依赖（在依赖没有被其他包使用的情况下），如果依赖被其他包使用，则无法移除：
</p>

```
$ composer remove monolog/monolog
```

#### search

<p>
查找包，输出包及其描述信息
</p>

```
$ composer search monolog
```

<p>
只输出包名可以使用 --only-name 参数：
</p>

```
$ composer search --only-name monolog
```

#### show

<p>
列出当前项目使用到包的信息：
</p>

```
# 列出所有已经安装的包
$ composer show

# 可以通过通配符进行筛选
$ composer show monolog/*

# 显示具体某个包的信息
$ composer show monolog/monolog
```

## 参考资料

[Composer 安装与使用](https://www.runoob.com/w3cnote/composer-install-and-usage.html)

[阿里云、腾讯云推出 Composer 全量镜像了](https://laravelacademy.org/post/19806.html)

