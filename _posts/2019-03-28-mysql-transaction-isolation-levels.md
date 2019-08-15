---
layout:     post
title:      "mysql-事务隔离级别"
subtitle:   "transaction isolation levels"
date:       2019-03-28 14:10
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - mysql
---

> 介绍mysql中事务并发时可能出现的问题及事务隔离级别。

## 概述

<p>
mysql事务的四大特性ACID，即原子性（automicity）、一致性(insistency)、隔离性(isolation)、持久性(durability)。隔离性最为重要，如果没有设置隔离级别，就可能会出现脏读、不可重复读、幻读。MySQL默认是innodb引擎，默认的隔离级别是repeatable read。
</p>

## 事务并发的问题

#### 脏读

##### 定义

<p>
当前事务读取了其他事务未提交的数据。
</p>

##### 测试

```
#A窗口

#设置隔离级别
set @@session.tx_isolation = 'read-uncommitted';
select @@session.tx_isolation,@@global.tx_isolation;

#第一次查询
start transaction;
select * from test;
id	cname
2	a

#第二次查询，B窗口事务不提交，结果为未提交的数据
select * from test;
id	cname
2	aa

#第三次查询，B窗口事务回滚，结果恢复到第一次查询
select * from test;
id	cname
2	a
```

```
#B窗口
start transaction;
update test set cname='aa' where id=2;

rollback;
commit;
```

#### 不可重复读

##### 定义


- 可重复读：当前事务读取了数据后，其他事务对数据的修改，不管有无提交，都不会影响当前事务多次读取的结果。
- 不可重复读：当前事务读取了数据后，其他事务对数据进行了修改会影响当前事务多次读取的结果。

##### 可重复读测试

```
#A窗口

#设置隔离级别
set @@session.tx_isolation = 'repeatable-read';
select @@session.tx_isolation,@@global.tx_isolation;

#第一次查询
start transaction;
select * from test;
id	cname
2	a

#第二次查询，B窗口事务不提交，查询结果不变
select * from test;
id	cname
2	a

#第三次查询，B窗口事务提交，查询结果不变
select * from test;
id	cname
2	a
```

```
#B窗口
start transaction;
update test set cname='aa' where id=2;

rollback;
commit;
```

##### 不可重复读测试

```
#A窗口

#设置隔离级别
set @@session.tx_isolation = 'read-committed';
select @@session.tx_isolation,@@global.tx_isolation;

#第一次查询
start transaction;
select * from test;
id	cname
2	a

#第二次查询，B窗口事务不提交，查询结果不变
select * from test;
id	cname
2	a

#第三次查询，B窗口事务提交，查询结果变化
select * from test;
id	cname
2	aa
```

```
#B窗口
start transaction;
update test set cname='aa' where id=2;

rollback;
commit;
```

#### 幻读

##### 定义

<p>
当前事务读取了数据行后，其他事务对数据行进行新增或删除，会影响当前事务多次读取的数据行。
</p>

##### 幻读测试

```
#A窗口

#设置隔离级别
set @@session.tx_isolation = 'read-committed';
select @@session.tx_isolation,@@global.tx_isolation;

#第一次查询
start transaction;
select * from test;
id	cname
2	aa

#第二次查询，B窗口事务不提交，查询结果不变
select * from test;
id	cname
2	a

#第三次查询，B窗口事务提交，查询结果变化
select * from test;
id	cname
2	aa
4   b
```

```
#B窗口
start transaction;
insert into test(cname) values('b');

rollback;
commit;
```

##### 不幻读测试

```
#A窗口

#设置隔离级别
set @@session.tx_isolation = 'serializable';
select @@session.tx_isolation,@@global.tx_isolation;

#第一次查询
start transaction;
select * from test;
id	cname
2	aa

#第二次查询，B窗口事务不能提交，查询结果不变
select * from test;
id	cname
2	aa
```

```
#B窗口
start transaction;
insert into test(cname) values('b');

#此时不能回滚或提交
rollback;
commit;
```

#### 不可重复读与幻读的区别

##### 结果

<p>
2种问题都会出现事务中多次读取数据与第一次读取的数据不一致的现象。
</p>

##### 控制的数据

<p>
控制不可重复读，需要锁定的是满足条件（<code>可理解为行锁</code>）的数据；控制幻读，需要锁定的是满足条件及附近的数据（<code>可理解为表锁</code>）。
</p>

##### 执行的操作

<p>
不可重复读针对的是<code>update</code>，幻读针对的是<code>insert与delete</code>。
</p>

## 事务隔离级别

<p>
不同隔离级别对应的并发处理问题：
</p>


隔离级别 | 脏读 | 不可重复读(结果是否发生变化) | 幻读
---|---|---|---
READ UNCOMMITTED | Y | Y | Y
READ COMMITTED | N | Y | Y
REPEATABLE READ | N | N | Y
SERIALIZABLE | N | N | N

#### read uncommitted

<p>
读未提交，select语句以非锁定方式执行，会读取到其他事务未提交的数据，此隔离级别会导致脏读。
</p>

#### read committed

<p>
读已提交，值会读取到其他事务已提交的数据。
</p>

#### repeatable read

<p>
该事务隔离级别只会读取已提交的结果，与read committed不同的是，repeatable-read在开启事务的情况下，同一条件的查询返回的结果永远是一致的，无论其它事物是否提交了新的数据。
</p>

#### serializable

<p>
Serializable隔离级别 ，读用读锁，写用写锁，读锁和写锁互斥，这么做可以有效的避免幻读、不可重复读、脏读等问题，但会极大的降低数据库的并发能力。
</p>

## 修改隔离级别

<p>
Tips：如果是mysql8及以后版本，需要将<code>tx_isolation</code>修改为<code>transaction_isolation</code>。
</p>

##### 查看

```
#session
mysql> select @@session.tx_isolation;
+------------------------+
| @@session.tx_isolation |
+------------------------+
| READ-UNCOMMITTED       |
+------------------------+

#global
mysql> select @@global.tx_isolation;
+-----------------------+
| @@global.tx_isolation |
+-----------------------+
| READ-UNCOMMITTED      |
+-----------------------+
```

##### 修改

```
#session
mysql> set @@session.tx_isolation = 'read-committed';

#global
mysql> set @@global.tx_isolation = 'read-uncommitted';
```

## 参考资料

[Transaction Isolation Levels](https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html)

[SET TRANSACTION Syntax](https://dev.mysql.com/doc/refman/5.7/en/set-transaction.html)

[innodb下的记录锁，间隙锁，next-key锁](https://www.jianshu.com/p/bf862c37c4c9)

[MySQL字符集及校对规则的理解](https://www.cnblogs.com/geaozhang/p/6724393.html?utm_source=itdadao&utm_medium=referral)