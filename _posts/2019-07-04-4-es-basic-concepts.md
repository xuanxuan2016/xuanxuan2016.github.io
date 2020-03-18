---
layout:     post
title:      "es- 基本概念"
subtitle:   "problems"
date:       2019-07-04 18:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - es
---

> 介绍es中基本概念

## 集群

<p>
集群是一个或多个节点(服务器)的集合，保存整个数据，并提供跨节点的联合索引和搜索功能。集群拥有唯一的标识名称，默认为<code>elasticsearch</code>，此标识名称用于在节点加入集群时使用。
</p>

<p>
为了避免节点加入错误的集群，不同环境的集群名称最好不一样，如<code>cluster-dev</code>、<code>cluster-qa</code>、<code>cluster-product</code>。
</p>

## 节点

<p>
节点是集群中的一个服务器，存储数据并参与集群的索引和搜索功能。如果不指定节点名称的话，在启动节点的时候会自动生成一个GUID的名称。此名称用于识别网络中的哪些服务器与Elasticsearch集群中的哪些节点相对应。
</p>

<p>
节点可通过配置集群名称，从而加入某个集群。默认情况下节点会加入一个名为<code>elasticsearch</code>的集群。
</p>

## 索引

<p>
索引（数据库的表）是具有某些类似特征的文档（表里的行）集合。例如，可以为客户数据、产品目录、订单数据建立索引。索引由名称标识（<span style='color:red;'>必须为小写</span>），此名称用于对其中的文档执行索引、搜索、更新、删除操作。
</p>

## 文档

<p>
文档是索引中的基本数据单元，使用JSON格式表示，如具体的客户、产品、订单文档。
</p>

## 分片

<p>
一个索引上存储的数据量可能会超过磁盘的上限。如一个索引上有1亿个文档（容量有1TB），这个索引就不适合放在单节点上，即使容量够但是查询数据也会很慢。
</p>

<p>
为了解决上述问题，Elasticsearch可将索引拆分为多个片。创建索引时，可以指定分片的数量。每个分片本身都是一个功能齐全且独立的“索引”，可以托管在集群中的任何节点上。
</p>

- 分片的好处：
- 在水平方向进行收缩/扩展
- 跨分片(可能在多个节点上)分布和并行化操作，从而提高性能/吞吐量

<p>
分片的分布方式以及如何将其文档聚合回搜索请求的机制完全由Elasticsearch管理，对用户而言是透明的。
</p>

## 复制

<p>
在随时可能出现问题的网络/云环境中，如果分片/节点由于某种原因离线或消失，是有必要拥有故障转移机制的。在es中可以为索引的分片设置一个或多个副本。
</p>

- 复制的好处：
- 提高分片/节点的高可用性，为此副本与原始数据是不会出现在一个节点上的
- 扩展搜索量/吞吐量，因为搜索可在所有副本上进行

<p>
总而言之，一个索引可以有多个分片，每个分片可以有多个副本（主分片/从分片）。
</p>

<p>
在创建索引时可以指定分片数（默认5）与副本数量（默认1）。虽然可以在之后通过<code>_shrink与_split</code>api进行动态修改，但最好还是在规划的时候就确定合适的数量。
</p>

## 参考资料

[Basic Concepts
](https://www.elastic.co/guide/en/elasticsearch/reference/current/getting-started-concepts.html)

[Removal of mapping types](https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html)
