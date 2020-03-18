---
layout:     post
title:      "es common api"
subtitle:   "common api"
date:       2019-07-11 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍es中，索引，映射，文档的api。

## api约定

[API conventions](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/api-conventions.html)

<p>
ES的RestApi主要是使用JSON字符串与服务器进行交互的。
</p>

#### 多索引支持

<p>
大部分使用<code>index</code>参数的api，都支持跨多个索引处理。
</p>

- 多索引关联方式：
- 手动指定索引：test1,test2,test3
- 通配符索引：test*,\*test,\*test\*,te*t
- 排除索引：test*,-test2

<p>
所有支持多索引的api，都可在url中增加如下参数来控制相应的处理逻辑。
</p>

- ignore_unavailable:[true,false],控制是否忽视不可用的索引
- allow_no_indices:[true,false],控制是否允许当使用通配符索引时匹配不到任何索引
- expand_wildcards:[none,open,closed,all],控制通配符索引所能匹配的打开或关闭的索引

<p style='color:red;'>
Tips：对于<code>文档与索引别名</code>的单索引api，是不支持跨索引处理的。
</p>

#### 索引名称的日期数学支持

<p>
通过将索引名称日期数字话，可以方便的查询一个时间区间的索引，而不要查询所有的索引再过滤或者用索引别名来实现功能。因为限制了查询索引的数量可降低集群的负载，也提高了查询性能。
</p>

#### 通用选项

<p>
通过在请求uri后面增加一些参数来控制执行结果的显示。
</p>

##### 返回结果格式化

- pretty=true
- format=yaml

##### 返回结果人性化

- human=[true,false]

##### 日期数字

<p>
大部分能接受格式化日期的参数都支持日期数字，如<code>gt lt</code>或<code>from to</code>。
</p>

```
#日期数字表达式格式
日期([now]或[日期字符串||])+表达式(可多个)
```

- 表达式举例:
- +1h:加1小时
- -1d:减1天
- /d:最近的天
- /d:最近的月

##### 响应数据过滤

<p>
通过<code>filter_path</code>参数来对返回结果进行过滤，仅返回需要的项目。多个返回控制使用<code>,</code>分隔。
</p>

#### 基于URL的访问控制

<p>
为了防止用户在请求体中指定索引名，来覆盖url中索引，可以修改如下配置。
</p>

```
#elasticsearch.yml
rest.action.multi.allow_explicit_index: false
```

## 索引api

[Indices APIs](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices.html)

<p>
用于管理单个索引，索引设置，索引别名，映射，索引模板。
</p>

#### 索引管理

##### 索引创建

<p>
用于新建一个索引，索引名有格式限制（参见官网）。
</p>

```
PUT /test2
```

```
#增加settings配置
PUT /test2
{
  "settings":{
    "number_of_shards" : 3,
    "number_of_replicas" : 2
  }
}
```

```
#增加mappings配置
PUT /test2
{
  "settings":{
    "number_of_shards" : 3,
    "number_of_replicas" : 2
  },
  "mappings" : {
      "properties" : {
          "field1" : { "type" : "text" }
      }
  }
}
```

<p>
在创建索引时，还可以指定别名等其他信息。
</p>

##### 索引删除

<p>
用于删除索引，可以为一个或多个（使用通配符）。可设置<code>action.destructive_requires_name=true</code>来禁用_all与通配符匹配。
</p>

```
DELETE /test2
```

##### 索引信息查看

<p>
用于查看一个或多个索引的创建信息。
</p>

```
GET /test2
```

##### 索引是否存在

<p>
用于检查一个索引或别名是否存在。
</p>

```
HEAD /test2

存在：200 - OK
不存在：404 - Not Found
```

##### 索引开启或关闭

<p>
用于索引的关闭与开启。关闭索引可能会占用大量磁盘空间，可通过配置<code>cluster.indices.close.enable=false</code>来禁用关闭索引的功能。
</p>

```
POST /test2/_open
POST /test2/_close
```

##### 索引收缩

<p>
可将某个分片数量较多的索引A，收缩为分片较少的索引B。收缩后的分片数量需要为原来分片数量的<code>因子</code>，如果原来分片数量为<code>质数</code>，则只能收缩为1个分片。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-shrink-index.html)

##### 索引拆分

<p>
可将某个分片数量较少的索引A，拆分为分片较多的索引B。索引可拆分的次数以及每个原始分片可拆分到的分片数，依赖于<code>index.number_of_routing_shards</code>。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-split-index.html)

##### 索引翻滚

<p>
用于将索引别名指向一个新索引，当旧索引满足一定的条件时（如存在时间，文档数量，文档大小）。可用于淘汰太旧或太大的索引。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-rollover-index.html#indices-rollover-index)

#### 映射管理

##### 映射设置

<p>
用于向现有索引中添加新的字段，或者更改已存在字段的查询设置。
</p>

```
PUT /test2/_mapping
{
  "properties": {
    "email":{
      "type": "keyword"
    }
  }
}
```

<p>
通常是不能修改已存在字段的设置，除了下列情况：
</p>

- 向对象类型的字段添加新属性
- 为字段添加多用途fields
- 修改ignore_above属性

##### 映射获取

<p>
用于获取索引的映射信息。
</p>

```
GET /test2/_mapping
```

##### 字段映射获取

<p>
用于获取索引的一个或多个字段的映射信息。
</p>

```
GET /test2/_mapping/field/email
GET /test2/_mapping/field/email,phone
```

#### 别名管理

##### 索引别名

<p>
通过此api可将一个或多个索引关联到一个别名，当使用es通过别名进行相关操作时，会自动映射到实际的索引。别名还可以与过滤器进行关联，当执行查询与路由值时会自动应用。
</p>

<p style='color:red;'>
Tips：别名不能与索引名重复。
</p>

```
#新建别名
#单个
POST /_aliases
{
  "actions": [
    {
      "add": {
        "index": "test2",
        "alias": "alias2"
      }
    }
  ]
}
#多个
POST /_aliases
{
  "actions": [
    {
      "add": {
        "index": "test2",
        "alias": "alias2"
      }
    },
    {
      "add": {
        "index": "test1",
        "alias": "alias2"
      }
    }
  ]
}
```

```
#删除别名
POST /_aliases
{
  "actions": [
    {
      "remove": {
        "index": "test2",
        "alias": "alias2"
      }
    }
  ]
}
```

```
#重命名别名，同时执行add与remove（原子操作）
POST /_aliases
{
  "actions": [
    {
      "remove": {
        "index": "test2",
        "alias": "alias2"
      }
    },
    {
      "add": {
        "index": "test2",
        "alias": "alias3"
      }
    }
  ]
}
```

##### 过滤器别名

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-aliases.html#filtered)

<p>
为索引的不同过滤条件设置不同的别名，好比SQL中的视图。
</p>

##### 路由别名

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-aliases.html#aliases-routing)

<p>
为索引的特定操作指定分片，避免无意义的路由。
</p>

##### 别名中的写索引

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-aliases.html#aliases-write-index)

<p>
可为在别名关联的索引中，指定一个索引为写入入口，当对别名进行更新操作时都会指向该索引。
</p>

#### 索引设置

##### 更新索引设置

<p>
用于实时的修改索引的某些设置。
</p>

```
PUT /test2/_settings
{
  "index":{
    "number_of_replicas":3
  }
}
```

[可动态修改的设置](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/index-modules.html)

##### 查看索引设置

<p>
用于查看索引的相关设置。
</p>

```
GET /test2/_settings
```

##### 分析器

<p>
对给定的文本执行相应的解析器，返回解析后的结果。
</p>

```
#单个文本
GET /_analyze
{
  "analyzer": "standard",
  "text": "this is a test"
}

#多个文本
GET /_analyze
{
  "analyzer": "standard",
  "text": ["this is a test", "the second text"]
}
```

<p>
可通过<code>tokenizer</code>,<code>filter</code>,<code>tokenizer</code>来自定义一个解析器。
</p>

```
GET _analyze
{
  "tokenizer" : "whitespace",
  "filter" : ["lowercase"],
  "char_filter" : ["html_strip"],
  "text" : "this is a <b>test</b>"
}
```

[控制解析器返回的深入信息](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/_explain_analyze.html)

##### 索引模板

<p>
可创建一个模板，模板包括设置和映射，并设置索引的模糊匹配条件。当创建索引时，如果索引名匹配到了模板则会自动应用模板里的设置。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-templates.html)

#### 监控

##### 索引统计信息

<p>
获取索引使用的监控统计信息。
</p>

```
#所有信息
GET /test2/_stats

#指定信息
GET /test2/_stats/docs,store
```

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-stats.html)

##### 索引深入统计信息

<p>
用于获取分片和索引状态的更多信息，如优化信息等。
</p>

```
GET /test2/_segments
```

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-segments.html#indices-segments)

##### 索引恢复信息

<p>
用于查看正在恢复的索引分片的信息。
</p>

```
GET /test2/_recovery?human
```

##### 索引分片存储信息

<p>
用于查看分片存储的信息。
</p>

```
GET /test2/_recovery?human
```

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/indices-shards-stores.html)

#### 状态管理

## 映射关系

[Mapping](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/mapping.html)

#### 概要

<p>
映射是定义文档及其包含的字段如何存储和索引的过程。
</p>

##### 字段数字类型

- 简单类型：text,keyword,date,long,double,boolean,ip
- JSON格式的复杂类型：object,nested
- 特殊类型：geo_point,geo_shape,completion

<p>
可通过<a target='_blank' href='https://www.elastic.co/guide/en/elasticsearch/reference/7.1/multi-fields.html'>multi-fields</a>功能，将一个字段定义为服务不同用途的类型。
</p>

##### 控制映射数量

<p>
如果索引中字段太多或字段的深度太深，可能会导致es服务异常。可通过如下这些设置控制：
</p>

- index.mapping.total_fields.limit：索引的最多字段数，默认为1000
- index.mapping.depth.limit：字段的最大深度，以内部对象的数量来衡量，默认为20
- index.mapping.nested_fields.limit：索引的最大嵌套映射数，默认为50
- index.mapping.nested_objects.limit：嵌套类型中单个文档中的最大JSON对象数，默认为10000

```
#设置方式
PUT /test222
{
  "settings":{
    "index.mapping.total_fields.limit":1
  }
}
```

##### 映射创建方式

- 动态创建：如果在使用字段前没有手动创建，则es会自动创建一个字段
- 手动指定：根据需求创建合适的字段类型

##### 更改映射

<p>
映射一旦创建后是不能修改的，如果需要修改可重新创建索引并通过reindex迁移数据。
</p>

#### 字段类型

大类 |小类 | 类型 | 说明
---|---|---|---|
core | string | text,keyword | 
core | numeric | long,integer,short,byte,double,float,half_float,scaled_float | 
core | date | date | 
core | date_nanos | date_nanos | 
core | boolean | boolean | 
core | binary | binary | 
core | range | integer_range,float_range,long_range,double_range,date_range | 
complex | object | object | 单个json对象
complex | nested | nested | json对象数组
geo | geo-point | geo_point | 经纬度坐标
geo | geo-shape | geo_shape | 经纬度组成的区域
special | ip | ip | IPv4或IPv6地址
special | completion  | completion  | 提供自动完成建议
special | token count  | token_count | 计算字符串中的token数量
special | mapper-murmur3 | murmur3 | 在索引时计算值的哈希值并将它们存储在索引中
special | mapper-annotated-text | annotated-text | 包含特殊标记的索引文本(通常用于标识命名实体)
special | Percolator type |  | 接受来自query-dsl的查询
special | join datatype |  | 为同一索引中的文档定义父/子关系
special | Alias datatype |  | 为字段定义别名
special | Rank feature datatype |  | 记录数字特性以提高查询时的点击率
special | Rank features datatype |  | 记录数字特性以提高查询时的点击率
special | Dense vector datatype |  | 记录浮点值的密集向量
special | Sparse vector datatype |  | 记录浮点值的稀疏向量
array |  |  | es中数组不需要的专用的数据类型，任何字段都可以为数组，但数组里值的类型必须相同
Multi-fields |  |  | 为了不同的目的(全文搜索；排序，聚合)以不同的方式索引同一个字段

##### text

<p>
用于索引全文值的字段，如邮件正文或产品描述。字段会被分析器解析用于倒排索引。
</p>

[参数](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/text.html#text-params)

##### keyword

<p>
用于索引结构化内容的字段，如电子邮件地址，主机名，状态，邮政编码或标签。通常用于过滤，排序，和聚合。keyword字段只能按确定值进行搜索。
</p>

[参数](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/keyword.html#keyword-params)

##### number

- long：带符号的64位整数，其最小值为-2^63，最大值为2^63-1
- integer：带符号的32位整数，其最小值为-2^31，最大值为2^31-1
- short：带符号的16位整数，其最小值为-32,768，最大值为32,767
- byte：带符号的8位整数，其最小值为-128，最大值为127
- double：双精度64位IEEE 754浮点数，限制为有限值
- float：单精度32位IEEE 754浮点数，限制为有限值
- half_float：半精确的16位IEEE 754浮点数，限制为有限值
- scaled_float：浮点数，它由一个长的、由一个固定的双比例因子缩放而成

<p style='color:red;'>
Tips：对于double,float,half_float，在应用term查询时-0.0与+0.0是不一样的。
</p>

<p>
对于整数类型（long,integer,short,byte）尽量选择满足使用要求的最小类型，有助于索引和查询。在存储时使用的是实际值与类型无关。
</p>

[参数](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/number.html#number-params)

##### date

<p>
es中的日期格式：
</p>

- 格式化日期的字符串，如“2015-01-01”或“2015/01/01 12:10:30”
- 使用long表示的毫秒数
- 使用integer表示的秒数

<p>
在内部，日期被转换为UTC(如果指定了时区)，并使用毫秒来存储。对日期执行查询时，将转换为毫秒进行查询，但是聚合或返回的结果，将按照字段设置的格式化要求进行返回。
</p>

```
#日期字段默认格式
strict_date_optional_time||epoch_millis
```

[日期格式](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/mapping-date-format.html#strict-date-time)

[参数](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/date.html#date-params)

##### bool

<p>
bool字段可接收true与false，或者可以转换为true与false的值。
</p>

[参数](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/boolean.html#boolean-params)

##### array

<p>
es中数组不需要的专用的数据类型，任何字段都可以为数组，但数组里值的类型必须相同。如：
</p>

- 字符串数组： [ "one", "two" ]
- 整数数组： [ 1, 2 ]
- 对象数组： [ { "name": "Mary", "age": 12 }, { "name": "John", "age": 10 }]

<p>
如果是动态创建字段的话，那么数组中的一个值将被用来确定字段类型，其它值的类型需要与第一个一致，或者可以被强制转换成第一个值的类型。
</p>

#### 元字段

<p>
每个文档都有与之关联的元字段，比如_index、_id等。在创建映射时，可自定义设置。
</p>

归类 | 类型 | 说明
---|---|---
identity  | _index | 文档所属的索引
identity  | _type  | 文档的映射类型
identity  | _id  | 文档的id
document  | _source  | 文档内容的json
document  | _size  | _source字段的大小（字节）
index  | _field_names  |文档中包含非空值的所有字段
index  | _ignored  |在索引时被忽略的文档中的所有字段
route  | _routing  |将文档路由到特定碎片的自定义路由值
other  | _meta  | 特定应用元数据

##### _routing

<p>
可用于自定义文档使用的路由值，默认使用<code>_id</code>。
</p>

```
#路由分片规则
shard_num = hash(_routing) % num_primary_shards
```

<p>
在执行查询时可指定路由值，用于减少查询的分片数量，提高查询效率。
</p>

```
GET /test222/_search?routing=haha
{
  "query": {
    "match_all": {
    }
  },
  "version":true
}
```

#### 映射参数

##### analyzer

<p>
分词器用于按照分词规则，将字符串拆分为tokens与terms，用于倒排索引时使用。
</p>

<p>
创建索引时，分词规则查找顺序：
</p>

- 字段定义时的analyzer规则
- 创建索引时指定的default规则
- standard规则

<p>
查询时，分词规则查找顺序：
</p>

- 全文查询时定义的analyzer规则
- 字段定义时的search_analyzer规则
- 字段定义时的analyzer规则
- 创建索引时指定的default_search规则
- 创建索引时指定的default规则
- standard规则

<p>
es自带了很多常用的分词规则，如果有需要，也可以自定义分词规则。
</p>

```
PUT my_index
{
   "settings":{
      "analysis":{
         "analyzer":{
            "my_analyzer":{ 
               "type":"custom",
               "tokenizer":"standard",
               "filter":[
                  "lowercase"
               ]
            }
      }
   },
   "mappings":{
       "properties":{
          "title": {
             "type":"text",
             "analyzer":"my_analyzer"
         }
      }
   }
}
```

##### normalizer

<p>
keyword类型字段的标准化规则，用于按照规则将字符串处理成另外一个字符串。标准化处理逻辑在字段索引之前执行，可用于match或term查询。
</p>

##### coerce

<p>
用于将输入文档值强制转换为字段设置的类型。字段级别>索引级别，如果<code>coerce=false</code>，文档中如果字段类型不符合设置的话，插入会失败。
</p>

```
#字段级别
PUT my_index
{
  "mappings": {
    "properties": {
      "number_two": {
        "type": "integer",
        "coerce": false
      }
    }
  }
}

#索引级别
PUT my_index
{
  "settings": {
    "index.mapping.coerce": false
  }
}
```

##### doc_values

<p>
在文档插入时，存储在磁盘上的数据结构，与_source有相同的值，但是以面向列的方式存储，偏于排序与聚合。如果可确定字段不会进行排序、聚合、脚本的使用，可设置为false，以节省磁盘空间。
</p>

```
PUT my_index
{
  "mappings": {
    "properties": {
      "session_id": { 
        "type":       "keyword",
        "doc_values": false
      }
    }
  }
}
```

##### dynamic

<p>
用于控制是否可自动创建字段映射。
</p>

- true(默认)：会自动创建
- false：不会自动创建，文档插入成功，不能用于查询，会存储到_source中
- strict：不会自动创建，文档插入失败，需要显式创建映射

##### enabled

<p>
只能设置在最上层的mapping或object字段中，es会跳过对此类字段内容的解析.
</p>

##### index

<p>
用于控制字段是否建立索引，不索引的字段不能用于查询。
</p>

##### format

<p>
用于date类型字段的格式控制。
</p>

##### ignore_above

<p>
对于字符串类型的字段（字符串数组），如果长度超过设置的值，则不会被索引与存储(_source中会存在)。
</p>

##### ignore_malformed

<p>
当输入文档某些字段不满足字段类型时，控制不拒绝整个文档，而忽略类型错误的字段。
</p>

##### fields

<p>
为不同的目的(全文搜索；排序，聚合)以不同的方式索引同一个字段。
</p>

##### null_value

<p>
用指定的值替换空值，以便对其进行索引和搜索。
</p>

##### store

<p>
用于不通过_source，就可以通过<code>stored_fields</code>查询部分字段。
</p>

#### 动态映射

## 文档api

[Document APIs](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/docs.html)

#### 概要

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/docs-replication.html)

<p>
主（写）分片操作流程：
</p>

- 验证操作的有效性（如数据格式是否正确），验证不通过返回失败
- 在主分片上执行操作，同时也有一些校验（如字符串过长）
- 将操作分发到所有从分片上执行，如果有多个从分片则并行执行
- 当所有从分片执行操作成功，会告知主分片，最后主分片告知客户端命令执行成功

<p>
从（读）分片操作流程：
</p>

- 将读请求路由到相关的分片
- 从每个分片中选取一个活动的副本（主或从），es一般会在副本中进行轮询
- 将读请求发送到选中的副本
- 合并所有副本的结果，返回给客户端

#### 单文档api

##### index

<p>
用于新增文档。
</p>

```
#指定id
PUT test222/_doc/1
{
    "user" : "kimchy",
    "post_date" : "2009-11-15T14:12:12",
    "message" : "trying out Elasticsearch"
}

#自动生成id
POST test222/_doc
{
    "user" : "kimchy2",
    "post_date" : "2009-11-15T14:12:12",
    "message" : "trying out Elasticsearch"
}
```

<p>
如果希望es中已存在某个id的文档，则不插入，可通过如下api：
</p>

```
PUT test222/_doc/1?op_type=create
PUT test222/_create/1
```

<p>
写之前需要等待的活动的分片副本数（主+从）。
</p>

```
index.write.wait_for_active_shards
```

<p>
超时时间控制。
</p>

```
PUT test222/_doc/1?timeout=5m
```

##### get

<p>
用于根据id获取文档。
</p>

```
GET /test222/_doc/1
```

<p>
可通过<code>_source_includes</code>与<code>_source_excludes</code>对_source进行过滤。
</p>

```
#包含+排除，同时使用
GET test222/_doc/1?_source_includes=*.id&_source_excludes=entities
#仅使用包含
GET test222/_doc/1?_source=*.id,retweeted
```

<p>
用于根据id获取文档，仅返回_source。
</p>

```
GET /test222/_source/1
```

##### delete

<p>
用于根据id删除文档。
</p>

```
DELETE /test222/_doc/1
```

##### update

<p>
用于根据id更新文档。
</p>

- 先根据id查询文档
- 更新相关字段
- 重新插入文档

```
#完全替换文档
PUT test222/_doc/1
{
    "user" : "kimchy22",
    "post_date" : "2009-11-15T14:12:12",
    "message" : "trying out Elasticsearch"
}
```

<p>
使用脚本执行更新
</p>

```
#更新部分字段
POST test222/_update/1
{
    "script" : {
        "source": "ctx._source.user += params.name",
        "lang": "painless",
        "params" : {
            "name" : "wang"
        }
    }
}
```

<p>
更新需要的字段
</p>

```
#通过doc参数
POST test222/_update/1
{
    "doc" : {
        "user" : "xiao pangzi"
    }
}
```

<p>
如果更新前后的结果一致将返回<code>result:noop</code>，可通过参数<code>"detect_noop": false</code>来禁用此特性。
</p>

```
{
  "_index" : "test222",
  "_type" : "_doc",
  "_id" : "1",
  "_version" : 5,
  "result" : "noop",
  "_shards" : {
    "total" : 0,
    "successful" : 0,
    "failed" : 0
  }
}
```

#### 多文档api

##### multi get

<p>
可同时获取多个文档，并可对返回字段进行过滤及设置路由。
</p>

```
GET /test222/_doc/_mget
{
  "ids":["1","2"]
}
GET /test222/_doc/_mget
{
  "docs": [
    {
      "_id": "1"
    },
    {
      "_id": "2"
    }
  ]
}
```

##### bulk

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/docs-bulk.html)

<p>
用于执行批量的index, create, delete, update操作。
</p>

```
#数据格式
action_and_meta_data\n
optional_source\n
action_and_meta_data\n
optional_source\n
....
action_and_meta_data\n
optional_source\n
```

```
#批量增加
POST test222/_bulk
{"index":{"_id":"3"}}
{"user" : "name3","age":28}
{"index":{"_id":"4"}}
{"user" : "name4","age":28}
```

```
#批量删除
POST test222/_bulk
{"delete":{"_id":"3"}}
{"delete":{"_id":"4"}}
```

```
#curl执行基于数据文件的批量操作
[root@DEV-interview1 escluster]# curl -u elastic:123456 -H "Content-Type: application/json" -XPOST "http://10.100.3.83:6200/bank/_bulk?pretty&refresh" --data-binary "@accounts.json"
```

##### delete by query

<p>
用于对符合条件的文档进行批量删除。
</p>

```
POST test222/_delete_by_query
{
  "query": {
    "term":{
      "user.keyword" : "kimchy2"
    }
  }
}
```

##### update by query

<p>
用于对符合条件的文档进行批量更新。
</p>

```
POST test222/_update_by_query?conflicts=proceed
{
  "script": {
    "source": "ctx._source.age=200",
    "lang": "painless"
  },
  "query": {
    "term":{
      "user.keyword" : "xiao houzi"
    }
  }
}
```

##### reindex

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/docs-reindex.html)

<p>
用于将源索引里的数据复制到目标索引。
</p>

<p style="color:red;">
Tips：<code>reindex</code>不会创建目标索引，所以在使用此api前，需要手动创建好目标索引，如映射、分片数、副本数等。
</p>

```
POST _reindex
{
  "source": {
    "index": "test222"
  },
  "dest": {
    "index": "test333"
  }
}
```

## 参考资料
