---
layout:     post
title:      "es cluster api"
subtitle:   "cluster api"
date:       2019-07-11 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍es中，集群，统计的api。

## 集群api

[Cluster APIs](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/cluster.html)

##### health

<p>
获取集群或节点的健康信息。
</p>

```
#集群
GET /_cluster/health
#节点
GET /_cluster/health/test222,test22
```

##### state

<p>
获取集群状态的所有元数据信息。
</p>

- 节点的集合
- 集群级别的设置
- 索引的信息，如映射和设置
- 集群中所有分片的位置

```
#所有信息
GET /_cluster/state
#哪些信息
GET /_cluster/state/{metrics}
#哪些节点的哪些信息
GET /_cluster/state/{metrics}/{indices}
```

##### stats

<p>
从集群范围的角度获取统计信息。
</p>

```
#所有信息
GET /_cluster/stats
#指定节点
GET /_cluster/stats/nodes/node-1
```

##### pending_tasks

<p>
获取集群级别上未执行的任务。
</p>

```
GET /_cluster/pending_tasks
```


## 统计api

[cat APIs](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/cat.html)

##### aliases

<p>
显示当前索引别名的设置，过滤及路由信息。
</p>

```
#全部别名
GET /_cat/aliases?v

#指定别名
GET /_cat/aliases/alias2/?v
```

##### allocation

<p>
显示每个节点的分片数，磁盘的使用情况。
</p>

```
GET /_cat/allocation?v
```

##### count

<p>
显示集群或指定索引中的文档数。
</p>

```
#集群
GET /_cat/count?v
#指定索引
GET /_cat/count/test222?v
```

##### fielddata

<p>
显示集群中field字段所占用的内存。
</p>

```
#所有
GET /_cat/fielddata?v
#指定字段
GET /_cat/fielddata?v&fields=_id
```

##### health

<p>
显示集群运行情况。
</p>

```
GET /_cat/health?v
```

##### indices

<p>
显示索引的分片数量，文档数，已删除文档数，总存储大小，主分片存储大小。
</p>

```
GET /_cat/indices?v
```

##### master

<p>
显示主节点的信息。
</p>

```
GET /_cat/master?v
```

##### nodeattrs

<p>
显示节点的属性。
</p>

```
#默认字段
GET /_cat/nodeattrs?v
#指定字段
GET /_cat/nodeattrs?v&h=node,id,pid,host,ip,port,attr,value
```

##### nodes

<p>
显示集群的拓扑信息。
</p>

[可用字段](https://www.elastic.co/guide/en/elasticsearch/reference/7.1/cat-nodes.html)

```
GET /_cat/nodes?v
```

##### pending_tasks

<p>
等待处理的任务。
</p>

```
GET /_cat/pending_tasks?v
```

##### plugins

<p>
显示节点上运行的插件。
</p>

```
GET /_cat/plugins?v
```

##### recovery

<p>
显示索引分片的恢复状态。
</p>

```
GET /_cat/recovery?v
```

##### recovery

<p>
显示在集群中注册的快照存储库。
</p>

```
GET /_cat/repositories?v
```

##### shards

<p>
显示节点分片的详细信息。
</p>

```
#所有索引
GET /_cat/shards?v
#指定索引
GET /_cat/shards/test*?v
```

## 参考资料
