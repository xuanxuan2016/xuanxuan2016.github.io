---
layout:     post
title:      "es search api"
subtitle:   "common api"
date:       2019-07-11 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍es中，查询相关的api。

## 查询语句

[Query DSL](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/query-dsl.html)

#### 概述

<p>
查询DSL由2种类型的子句组成：
</p>

- 简单查询：指定字段查询，如match、term、range
- 复合查询：由检查查询+复合查询组成，如bool、dis_max

#### 查询与过滤上下文

- 查询上下文：文档与查询子句的匹配程度，会计算评分，用于模糊匹配
- 过滤上下文：文档与查询子句是否完全匹配，不计算评分，用于绝对匹配

#### 复合查询

##### bool

<p>
用于组合must、should、must_not、filter组成的子查询。
</p>

- must：文档需要满足所有查询子句
- filter：文档需要满足所有查询子句
- should：文档需要满足任一查询子句
- must_not：文档需要不满足所有查询子句

#### 全文查询

##### match

<p>
将match条件使用分析器进行拆分，然后使用<code>bool</code>规则进行匹配，<code>operator</code>可为<code>[or(默认),and]</code>
</p>

```
#默认or
GET /test222/_search
{
  "query": {
    "match": {
      "user":"xiao houzi"
    }
  }
}

#指定operator
GET /test222/_search
{
  "query": {
    "match": {
      "user": {
        "query": "xiao houzi",
        "operator": "or",
        "minimum_should_match": 2
      }
    }
  }
}
```

<p>
如果match条件中都是<code>stop</code>符，可通过<code>zero_terms_query[none,all]</code>来控制返回的结果。
</p>

##### match_phrase

<p>
短语搜索，要求所有的分词必须同时出现在文档中，同时位置必须紧邻一致。
</p>

```
GET /test222/_search
{
  "query": {
    "match_phrase": {
      "user":"xiao houzi"
    }
  }
}
```

##### match_phrase_prefix

<p>
功能同match_phrase，但可以对最后一个term执行前缀匹配，可用于实现自动完成功能。
</p>

```
GET /test222/_search
{
  "query": {
    "match_phrase_prefix": {
      "user": {
        "query": "xiao h",
        "max_expansions": 10
      }
    }
  }
}
```

##### multi_match

<p>
对多个字段同时进行查询，按type设置进行返回。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/query-dsl-multi-match-query.html)

##### match_all

```
#全匹配
GET /test222/_search
{
  "query": {
    "match_all": {}
  }
}

#全不匹配
GET /test222/_search
{
  "query": {
    "match_none": {}
  }
}
```

#### term查询

<p>
与match不同的是，不会对查询条件进行分词，而是要与字段中的存储的值完全匹配。
</p>

##### exists

<p>
文档存在某字段，且字段值不为<code>null或[]</code>。
</p>

```
GET /test222/_search
{
  "query": {
    "exists": {
      "field":"user"
    }
  }
}
```

##### ids

<p>
根据id查询文档。
</p>

```
GET /test222/_search
{
  "query": {
    "ids":{
      "values": ["1"]
    }
  }
}
```

##### prefix

<p>
用于匹配字段的前缀。
</p>

```
#因为是text类型字段，需要使用keyword匹配
GET /test222/_search
{
  "query": {
    "prefix":{
      "user.keyword":"xiao "
    }
  }
}
```

##### range

<p>
用于数值或日期的范围查询。
</p>

```
GET /test222/_search
{
  "query": {
    "range": {
      "age": {
        "gte": 10,
        "lte": 20
      }
    }
  }
}
```

##### regexp

<p>
使用正则表达式匹配。
</p>

[详情](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/query-dsl-regexp-query.html)

```
GET /test222/_search
{
  "query": {
    "regexp": {
      "user.keyword": ".*houzi"
    }
  }
}
```

##### term

<p>
用于对字段的精确查询。
</p>

<p style='color:red;'>
Tips：不要用于对text类型字段的查询，如果要用需要配置keyword。
</p>

```
GET /test222/_search
{
  "query": {
    "term": {
      "user.keyword": "xiao pangzi"
    }
  }
}
```

##### terms

<p>
同term，满足任一搜索条件即可。
</p>

```
GET /test222/_search
{
  "query": {
    "terms": {
      "user.keyword": ["xiao pangzi","xiao houzi"]
    }
  }
}
```

##### terms_set

<p>
同terms，可指定需要满足多少个条件。
</p>

- minimum_should_match_field：使用数值字段，需要事先定义
- minimum_should_match_script：使用脚本，满足动态设置

```
GET /test222/_search
{
  "_source": ["age","user"], 
  "query": {
    "terms_set":{
      "user.keyword":{
        "terms":["a","b"],
        "minimum_should_match_script":{
          "source":"params.num_terms"
        }
      }
    }
  }
}
```

## 查询api

[Search APIs](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/query-dsl.html)

#### 请求主体查询

<p>
在<code>_search</code>接口中结合查询语句进行查询。
</p>

- timeout：默认没有，查询超时时间
- from：默认0，数据开始位置
- size：默认10，返回的hits数
- search_type(url)：默认query_then_fetch，要执行的查询操作的类型
- request_cache(url)：当执行携带参数size=0的请求时，是否使用缓存
- allow_partial_search_results(url)：默认true，当集群出现故障时，是否允许返回部分结果
- terminate_after：默认没有，设置每个分片可获取到的最大文档数，如果达到设置则查询终止
- batched_reduce_size：当查询结果数量较大时，用来减少的数量



##### search_after

<p>
当进行分页查询时，如果需要获取很后面的数据（如10000之后），可以使用search_after告知es从哪里开始查询，提高效率。
</p>

```
#from:0或-1
GET /test222/_search
{
  "from": 0, 
  "size": 2, 
  "search_after":["1"],
  "query": {
    "match_all": {}
  },
  "sort": [
    {
      "_id": {
        "order": "asc"
      }
    }
  ]
}
```

##### sort

<p>
用于对查询结果按字段进行排序。
</p>

- 特殊字段:
- _score：文档分值
- _doc：文档id

##### _source

<p>
用于从_source中筛选需要返回的字段。
</p>

##### stored_fields

<p>
用于从_source中筛选需要返回的字段，字段需要标记<code>store=true</code>。
</p>

##### track_total_hits

<p>
是否要获取文档的总命中数。
</p>

- true：返回总命中数
- false：不返回命中数信息
- 具体数值：最多命中数量

##### version

<p>
返回文档的版本。
</p>

## 聚合

[Aggregations](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/search-aggregations.html)


## 参考资料

[es中的term和match的区别](https://www.jianshu.com/p/d5583dff4157)