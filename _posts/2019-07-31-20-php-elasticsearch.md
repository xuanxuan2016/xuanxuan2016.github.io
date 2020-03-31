---
layout:     post
title:      "php-elasticsearch"
subtitle:   "php elasticsearch"
date:       2019-07-31 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
    - php
---

> 介绍php-elasticsearch中的类，类之间的关系，常见的使用方法。

## 概述

[项目git地址。](https://github.com/elastic/elasticsearch-php)

<p>
官方提供的用于在中php操作es的库。
</p>

## 安装

#### 标准安装

<p>
通过composer获取库代码，可在标准的laravel项目根目录或自定义文件夹获取。
</p>

```
#composer.json
#根据实际情况，获取需要的版本
{
    "require": {
        "elasticsearch/elasticsearch": "^6.0"
    }
}
```

```
# composer install
```

#### 自定义框架修改

<p>
因为使用的框架非标准laravel框架，es库需要集成到框架的<code>Framework\Service\Lib</code>命名空间下，需要进行如下的修改：
</p>

<p style='color:red;'>
1.后续框架支持了psr4的加载，可以不做如下命名空间的修改，只需要进行配置即可
</p>

##### 复制类库

```
#Elasticsearch
\vendor\elasticsearch\elasticsearch\src\Elasticsearch -> \framework\Service\Lib\Elasticsearch
```

```
#GuzzleHttp
#注意文件夹大小写需要修改
\vendor\guzzlehttp\ringphp\src - >\framework\Service\Lib\GuzzleHttp\Ring
\vendor\guzzlehttp\streams\src - >\framework\Service\Lib\GuzzleHttp\Stream
```

```
#Psr
\vendor\psr\log\Psr -> \framework\Service\Lib\Psr
```

```
#Monolog
\vendor\monolog\monolog\src\Monolog -> \framework\Service\Lib\Monolog
```

```
#React
\vendor\react\promise\src -> \framework\Service\Lib\React\Promise
```

##### 修改类库

```
#Elasticsearch

#1.替换命名空间的定义
namespace Elasticsearch -> namespace Framework\Service\Lib\Elasticsearch

#2.替换命名空间的使用
use Elasticsearch\ -> use Framework\Service\Lib\Elasticsearch\

#3.替换ClientBuilder中$connectionPool,$serializer,$selector类的路径
private $connectionPool = '\Framework\Service\Lib\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool';
private $serializer = '\Framework\Service\Lib\Elasticsearch\Serializers\SmartSerializer';
private $selector = '\Framework\Service\Lib\Elasticsearch\ConnectionPool\Selectors\RoundRobinSelector';
$fullPath = '\\Framework\\Service\\Lib\\Elasticsearch\\Endpoints\\' . $class;
```

<p style='color:red;'>
Promise类库需要使用一些通用的方法，在<code>ClientBuilder.php->Elasticsearch->create</code>中引入。
</p>

```
public static function create()
{
    //加载Promise中的函数
    include_once App::make('path.framework') . '/Service/Lib/React/Promise/functions_include.php';
    
    return new static();
}
```

```
#GuzzleHttp

#1.替换命名空间的定义
namespace GuzzleHttp -> namespace Framework\Service\Lib\GuzzleHttp

#2.替换命名空间的使用
use GuzzleHttp\ -> use Framework\Service\Lib\GuzzleHttp\
```

```
#Psr

#1.替换命名空间的定义
namespace Psr -> namespace Framework\Service\Lib\Psr

#2.替换命名空间的使用
use Psr\ -> use Framework\Service\Lib\Psr\
```

```
#Monolog

#1.替换命名空间的定义
namespace Monolog -> namespace Framework\Service\Lib\Monolog

#2.替换命名空间的使用
use Monolog\ -> use Framework\Service\Lib\Monolog\
```

```
#React

#1.替换命名空间的定义
namespace React -> namespace Framework\Service\Lib\React

#2.替换命名空间的使用
use React\ -> use Framework\Service\Lib\React\
```

## 使用方法

[官方文档](https://www.elastic.co/guide/en/elasticsearch/client/php-api/6.7.x/indexing_documents.html)

<p style='color:red;'>
Tips：示例使用的是6.7版本，其它版本规则可到官网查询。
</p>

#### 增

##### 单个文档

```
//构造client,hosts可在每次请求时变换顺序，以分摊访问热点
$objClient = ClientBuilder::create()->setHosts([
                ['host' => '10.100.3.83', 'port' => '6200', 'user' => 'elastic', 'pass' => '123456'],
                ['host' => '10.100.3.83', 'port' => '7200', 'user' => 'elastic', 'pass' => '123456'],
                ['host' => '10.100.3.83', 'port' => '8200', 'user' => 'elastic', 'pass' => '123456']
        ])->build();

//指定id插入
$arrRst = $objClient->index([
    'index' => 'test222',
    'type' => '_doc',
    'id' => 11,
    'body' => ['user' => 'test1', 'age' => '12', 'postdate' => '2019-01-01']
]);

//不指定id插入
$arrRst = $objClient->index([
    'index' => 'test222',
    'type' => '_doc',
    'body' => ['user' => 'test2', 'age' => '13', 'postdate' => '2019-01-02']
]);

//输出
var_dump($arrRst);
```

##### 多个文档

```
//数组中的顺序为按[index,body]重复
$arrRst = $objClient->bulk([
    'body' => [
            ['index' => ['_index' => 'test222', '_type' => '_doc']],
            ['body' => 'test3', 'age' => '14', 'postdate' => '2019-01-03'],
            ['index' => ['_index' => 'test222', '_type' => '_doc']],
            ['body' => 'test4', 'age' => '15', 'postdate' => '2019-01-04']
    ]
]);
```

#### 删

##### 删除单个文档

```
//根据id删除文档
$arrRst = $objClient->delete([
    'index' => 'test222',
    'type' => '_doc',
    'id' => '11'
]);
```

#### 改

##### 局部更新

```
#更新或添加field
$arrRst = $objClient->update([
    'index' => 'test222',
    'type' => '_doc',
    'id' => 11,
    'body' => [
        'doc' => ['user' => 'test1_1', 'name' => 'haha']
    ]
]);
```

```
#脚本更新
$arrRst = $objClient->update([
    'index' => 'test222',
    'type' => '_doc',
    'id' => 44,
    'body' => [
        'script' => [
            'source' => 'ctx._source.age2 += params.count',
            'params' => [
                'count' => 4
            ]
        ]
    ]
]);
```

##### 全部更新

```
//再次插入可实现全量更新
$arrRst = $objClient->index([
    'index' => 'test222',
    'type' => '_doc',
    'id' => 11,
    'body' => ['user' => 'test1', 'age' => '12', 'postdate' => '2019-01-01']
]);
```

#### 查

##### 获取单个文档

```
//根据id获取文档
$arrRst = $objClient->get([
    'index' => 'test222',
    'type' => '_doc',
    'id' => '1'
]);
```

##### 查询全部

```
//查询所有数据
$arrRst = $objClient->search([
    'index' => 'test222',
    'body' => [
        'query' => [
            'match_all' => new \stdClass()
        ]
    ]
]);
```

##### 模糊匹配

```
$arrRst = $objClient->search([
    'index' => 'test222',
    'body' => [
        'query' => [
            'match' => [
                'user' => 'a'
            ]
        ]
    ]
]);
```

## 代码结构

#### 类的归类

[戳这里看](http://naotu.baidu.com/file/361c25971393fe626aba481691a79686?token=fc831e2f612b07db)

#### UML

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-31-20-php-elasticsearch/tu_1.png?raw=true)

## 代码流程

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2019-07-31-20-php-elasticsearch/tu_2.png?raw=true)

## 代码注释

[戳这里看代码注释]()

## 参考资料

[monolog使用](https://blog.csdn.net/l754539910/article/details/53931433)



