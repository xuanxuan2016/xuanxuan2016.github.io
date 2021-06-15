---
layout:     post
title:      "Prometheus告警处理"
subtitle:   "Prometheus AlertManager"
date:       2021-06-11 10:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - prometheus
---

## 概要

<p>
Prometheus的告警处理分为两个部分，在PrometheusServer中定义告警规则以及产生告警，Alertmanager组件则用于处理这些由Prometheus产生的告警。Alertmanager即Prometheus体系中告警的统一处理中心。Alertmanager提供了多种内置第三方告警通知方式，同时还提供了对Webhook通知的支持，通过Webhook用户可以完成对告警更多个性化的扩展。
</p>

#### 架构

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_1.png?raw=true)

<p>
Prometheus中告警规则组成：
</p>

- 告警名称：用户需要为告警规则命名，当然对于命名而言，需要能够直接表达出该告警的主要内容
- 告警规则：告警规则实际上主要由PromQL进行定义，其实际意义是当表达式（PromQL）查询结果持续多长时间（During）后出发告警

#### 特性

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_2.png?raw=true)

##### 分组

<p>
分组机制可以将详细的告警信息合并成一个通知。在某些情况下，比如由于系统宕机导致大量的告警被同时触发，在这种情况下分组机制可以将这些被触发的告警合并为一个告警通知，避免一次性接受大量的告警通知，而无法对问题进行快速定位。
</p>

<p>
例如，当集群中有数百个正在运行的服务实例，并且为每一个实例设置了告警规则。假如此时发生了网络故障，可能导致大量的服务实例无法连接到数据库，结果就会有数百个告警被发送到Alertmanager。
</p>

<p>
而作为用户，可能只希望能够在一个通知中中就能查看哪些服务实例收到影响。这时可以按照服务所在集群或者告警名称对告警进行分组，而将这些告警内聚在一起成为一个通知。
</p>

<p>
告警分组，告警时间，以及告警的接受方式可以通过Alertmanager的配置文件进行配置。
</p>

##### 抑制

<p>
抑制是指当某一告警发出后，可以停止重复发送由此告警引发的其它告警的机制。
</p>

<p>
例如，当集群不可访问时触发了一次告警，通过配置Alertmanager可以忽略与该集群有关的其它所有告警。这样可以避免接收到大量与实际问题无关的告警通知。
</p>

<p>
抑制机制同样通过Alertmanager的配置文件进行设置。
</p>


##### 静默

<p>
静默提供了一个简单的机制可以快速根据标签对告警进行静默处理。如果接收到的告警符合静默的配置，Alertmanager则不会发送告警通知。
</p>

<p>
静默设置需要在Alertmanager的Web页面上进行设置。
</p>

## 安装AlertManager

<p>
AlertManager是基于Golang编写，编译后的软件包，不依赖于任何的第三方依赖。从<a href="https://prometheus.io/download/" target="_blank">官网</a>获取对应平台的最新二进制包，这里选择<code>alertmanager-0.22.2.linux-amd64.tar.gz</code>
</p>

```
curl -LO https://github.com/prometheus/alertmanager/releases/download/v0.22.2/alertmanager-0.22.2.linux-amd64.tar.gz
```

<p>
解压，可将AlertManager相关的命令，添加到系统环境变量方便使用。
</p>

```
tar -xvf alertmanager-0.22.2.linux-amd64.tar.gz
```

```
#在运行目录创建data目录用于存储数据
mkdir data
```

```
#默认参数启动
./alertmanager &

#查看可用启动参数
./alertmanager -h
```

<p>
查看alertmanager是否启动成功
</p>

```
[root@Dev-mHRO /]# ps -eaf|grep alertmanager
root     23785     1  0 6月10 ?       00:01:36 ./alertmanager
```

<p>
使用浏览器访问<code>http://{安装ip}:9093/</code>可查看网站信息。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_3.png?raw=true)

## 配置Prometheus将告警给AlertManager处理

<p>
在Prometheus的架构中被划分成两个独立的部分。Prometheus负责产生告警，而Alertmanager负责告警产生后的后续处理。因此Alertmanager部署完成后，需要在Prometheus中设置Alertmanager相关的信息。
</p>

<p>
修改Prometheus配置文件prometheus.yml，增加如下配置。
</p>

```
# Alertmanager configuration
alerting:
  alertmanagers:
  - static_configs:
    - targets: ['127.0.0.1:9093']
```

<p>
重启Prometheus，在<code>http://{安装ip}:9090/config</code>查看alerting配置是否生效。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_4.png?raw=true)

## 配置Prometheus中告警触发规则

<p>
Prometheus中的告警规则允许你基于PromQL表达式定义告警触发条件，Prometheus后端对这些触发规则进行周期性计算，当满足触发条件后则会触发告警通知。默认情况下，用户可以通过Prometheus的Web界面查看这些告警规则以及告警的触发状态。当Promthues与Alertmanager关联之后，可以将告警发送到外部服务如Alertmanager中并通过Alertmanager可以对这些告警进行进一步的处理。
</p>

#### 定义告警规则

<p>
配置案例：
</p>

```
groups:
- name: example
  rules:
  - alert: HighRequestLatency
    expr: job:request_latency_seconds:mean5m{job="myjob"} > 0.5
    for: 10m
    labels:
      severity: page
    annotations:
      summary: High request latency
```

<p>
在告警规则文件中，我们可以将一组相关的规则设置定义在一个group下。在每一个group中我们可以定义多个告警规则(rule)。一条告警规则主要由以下几部分组成：
</p>

- alert：告警规则的名称。
- expr：基于PromQL表达式告警触发条件，用于计算是否有时间序列满足该条件。
- for：评估等待时间，可选参数。用于表示只有当触发条件持续一段时间后才发送告警。在等待期间新产生告警的状态为pending，触发报警时为firing。
- labels：自定义标签，允许用户指定要附加到告警上的一组附加标签。
- annotations：用于指定一组附加信息，比如用于描述告警详细信息的文字等，annotations的内容在告警产生时会一同作为参数发送到Alertmanager。

<p>
告警规则文件通过<code>rule_files</code>加载。
</p>

```
# Load rules once and periodically evaluate them according to the global 'evaluation_interval'.
rule_files:
  - rules/*.rules
```

<p>
默认情况下Prometheus会每分钟对这些告警规则进行计算，如果用户想定义自己的告警计算周期，则可以通过<code>evaluation_interval</code>来覆盖默认的计算周期：
</p>

```
global:
  [ evaluation_interval: <duration> | default = 1m ]
```

#### 模板化

<p>
一般来说，在告警规则文件的annotations中使用<code>summary</code>描述告警的概要信息，<code>description</code>用于描述告警的详细信息。同时Alertmanager的UI也会根据这两个标签值，显示告警信息。为了让告警信息具有更好的可读性，Prometheus支持模板化label和annotations的中标签的值。
</p>

<p>
通过<code>$labels.labelname</code>变量可以访问当前告警实例中指定标签的值。$value则可以获取当前PromQL表达式计算的样本值。
</p>

```
# To insert a firing element's label values:
{{ $labels.labelname }}
# To insert the numeric expression value of the firing element:
{{ $value }}
```

<p>
例如，可以通过模板化优化summary以及description的内容的可读性：
</p>

```
groups:
- name: example
  rules:

  # Alert for any instance that is unreachable for >5 minutes.
  - alert: InstanceDown
    expr: up == 0
    for: 5m
    labels:
      severity: page
    annotations:
      summary: "Instance {{ $labels.instance }} down"
      description: "{{ $labels.instance }} of job {{ $labels.job }} has been down for more than 5 minutes."

  # Alert for any instance that has a median request latency >1s.
  - alert: APIHighRequestLatency
    expr: api_http_request_latencies_second{quantile="0.5"} > 1
    for: 10m
    annotations:
      summary: "High request latency on {{ $labels.instance }}"
      description: "{{ $labels.instance }} has a median request latency above 1s (current value: {{ $value }}s)"
```


## 配置AlertManager中告警处理规则

<p>
Alertmanager通过路由(Route)来定义告警的处理方式。路由是一个基于标签匹配的树状匹配结构。根据接收到告警的标签匹配相应的处理方式。
</p>

#### 配置概述

<p>
Alertmanager主要负责对Prometheus产生的告警进行统一处理，因此在Alertmanager配置中一般会包含以下几个主要部分：
</p>

- 全局配置（global）：用于定义一些全局的公共参数，如全局的SMTP配置，Slack配置等内容；
- 模板（templates）：用于定义告警通知时的模板，如HTML模板，邮件模板等；
- 告警路由（route）：根据标签匹配，确定当前告警应该如何处理；
- 接收人（receivers）：接收人是一个抽象的概念，它可以是一个邮箱也可以是微信，Slack或者Webhook等，接收人一般配合告警路由使用；
- 抑制规则（inhibit_rules）：合理设置抑制规则可以减少垃圾告警的产生

```
global:
  [ resolve_timeout: <duration> | default = 5m ]
  [ smtp_from: <tmpl_string> ] 
  [ smtp_smarthost: <string> ] 
  [ smtp_hello: <string> | default = "localhost" ]
  [ smtp_auth_username: <string> ]
  [ smtp_auth_password: <secret> ]
  [ smtp_auth_identity: <string> ]
  [ smtp_auth_secret: <secret> ]
  [ smtp_require_tls: <bool> | default = true ]
  [ slack_api_url: <secret> ]
  [ victorops_api_key: <secret> ]
  [ victorops_api_url: <string> | default = "https://alert.victorops.com/integrations/generic/20131114/alert/" ]
  [ pagerduty_url: <string> | default = "https://events.pagerduty.com/v2/enqueue" ]
  [ opsgenie_api_key: <secret> ]
  [ opsgenie_api_url: <string> | default = "https://api.opsgenie.com/" ]
  [ hipchat_api_url: <string> | default = "https://api.hipchat.com/" ]
  [ hipchat_auth_token: <secret> ]
  [ wechat_api_url: <string> | default = "https://qyapi.weixin.qq.com/cgi-bin/" ]
  [ wechat_api_secret: <secret> ]
  [ wechat_api_corp_id: <string> ]
  [ http_config: <http_config> ]

templates:
  [ - <filepath> ... ]

route: <route>

receivers:
  - <receiver> ...

inhibit_rules:
  [ - <inhibit_rule> ... ]
```

<p>
在全局配置中需要注意的是resolve_timeout，该参数定义了当Alertmanager持续多长时间未接收到告警后标记告警状态为resolved（已解决）。该参数的定义可能会影响到告警恢复通知的接收时间，可根据自己的实际场景进行定义，其默认值为5分钟。
</p>

#### 告警路由

<p>
在Alertmanager的配置中会定义一个基于标签匹配规则的告警路由树，以确定在接收到告警后Alertmanager需要如何对其进行处理：
</p>

```
route: <route>
```

<p>
其中route中则主要定义了告警的路由匹配规则，以及Alertmanager需要将匹配到的告警发送给哪一个receiver，一个最简单的route定义如下所示：
</p>

```
route:
  group_by: ['alertname']
  receiver: 'web.hook'
receivers:
- name: 'web.hook'
  webhook_configs:
  - url: 'http://127.0.0.1:5001/'
```

<p>
如上所示：在Alertmanager配置文件中，我们只定义了一个路由，那就意味着所有由Prometheus产生的告警在发送到Alertmanager之后都会通过名为web.hook的receiver接收。这里的web.hook定义为一个webhook地址。当然实际场景下，告警处理可不是这么简单的一件事情，对于不同级别的告警，我们可能会不完全不同的处理方式，因此在route中，我们还可以定义更多的子Route，这些Route通过标签匹配告警的处理方式，route的完整定义如下：
</p>

```
[ receiver: <string> ]
[ group_by: '[' <labelname>, ... ']' ]
[ continue: <boolean> | default = false ]

match:
  [ <labelname>: <labelvalue>, ... ]

match_re:
  [ <labelname>: <regex>, ... ]

[ group_wait: <duration> | default = 30s ]
[ group_interval: <duration> | default = 5m ]
[ repeat_interval: <duration> | default = 4h ]

routes:
  [ - <route> ... ]
```

##### 路由匹配

<p>
每一个告警都会从配置文件中顶级的route进入路由树，需要注意的是顶级的route必须匹配所有告警(即不能有任何的匹配设置match和match_re)，每一个路由都可以定义自己的接受人以及匹配规则。默认情况下，告警进入到顶级route后会遍历所有的子节点，直到找到最深的匹配route，并将告警发送到该route定义的receiver中。但如果route中设置continue的值为false，那么告警在匹配到第一个子节点之后就直接停止。如果continue为true，报警则会继续进行后续子节点的匹配。如果当前告警匹配不到任何的子节点，那该告警将会基于当前路由节点的接收器配置方式进行处理。
</p>

<p>
其中告警的匹配有两种方式可以选择。一种方式基于字符串验证，通过设置match规则判断当前告警中是否存在标签labelname并且其值等于labelvalue。第二种方式则基于正则表达式，通过设置match_re验证当前告警标签的值是否满足正则表达式的内容。
</p>

<p>
如果警报已经成功发送通知,如果想设置再次发送告警通知之前要等待时间，则可以通过repeat_interval参数进行设置。
</p>

##### 路由匹配

<p>
Alertmanager可以对告警通知进行分组，将多条告警合合并为一个通知。这里我们可以使用group_by来定义分组规则。基于告警中包含的标签，如果满足group_by中定义标签名称，那么这些告警将会合并为一个通知发送给接收器。
</p>

<p>
有的时候为了能够一次性收集和发送更多的相关信息时，可以通过group_wait参数设置等待时间，如果在等待时间内当前group接收到了新的告警，这些告警将会合并为一个通知向receiver发送。
</p>

<p>
而group_interval配置，则用于定义相同的Group之间发送告警通知的时间间隔。
</p>

<p>
例如，当使用Prometheus监控多个集群以及部署在集群中的应用和数据库服务，并且定义以下的告警处理路由规则来对集群中的异常进行通知。
</p>

```
# The root route with all parameters, which are inherited by the child
# routes if they are not overwritten.
route:
  receiver: 'default-receiver'
  group_wait: 30s
  group_interval: 5m
  repeat_interval: 4h
  group_by: [cluster, alertname]
  # All alerts that do not match the following child routes
  # will remain at the root node and be dispatched to 'default-receiver'.
  routes:
  # All alerts with service=mysql or service=cassandra
  # are dispatched to the database pager.
  - receiver: 'database-pager'
    group_wait: 10s
    matchers:
    - service=~"mysql|cassandra"
  # All alerts with the team=frontend label match this sub-route.
  # They are grouped by product and environment rather than cluster
  # and alertname.
  - receiver: 'frontend-pager'
    group_by: [product, environment]
    matchers:
    - team="frontend"
```

<p>
默认情况下所有的告警都会发送给集群管理员default-receiver，因此在Alertmanager的配置文件的根路由中，对告警信息按照集群以及告警的名称对告警进行分组。
</p>

<p>
如果告警时来源于数据库服务如MySQL或者Cassandra，此时则需要将告警发送给相应的数据库管理员(database-pager)。这里定义了一个单独子路由，如果告警中包含service标签，并且service为MySQL或者Cassandra,则向database-pager发送告警通知，由于这里没有定义group_by等属性，这些属性的配置信息将从上级路由继承，database-pager将会接收到按cluster和alertname进行分组的告警通知。
</p>

<p>
而某些告警规则来源可能来源于开发团队的定义，这些告警中通过添加标签team来标示这些告警的创建者。在Alertmanager配置文件的告警路由下，定义单独子路由用于处理这一类的告警通知，如果匹配到告警中包含标签team，并且team的值为frontend，Alertmanager将会按照标签product和environment对告警进行分组。此时如果应用出现异常，开发团队就能清楚的知道哪一个环境(environment)中的哪一个应用程序出现了问题，可以快速对应用进行问题定位。
</p>

## 告警使用案例

<p>
这里来配置一个监控RabbitMQ队列中消息数量的告警规则。
</p>

#### 配置

##### /rules/rabbitmq-alert.rules

```
groups:
- name: rabbitmqAlert
  rules:
  - alert: rabbitmqMessagesReadyAlert
    expr: sum(rabbitmq_queue_messages_ready * on(instance) group_left(rabbitmq_cluster) rabbitmq_identity_info{rabbitmq_cluster="rabbit@10.100.2.234"}) > 210
    for: 1m
    labels:
      severity: page
      test1: aaa
      test2: bbb
    annotations:
      summary: "消息队列消息数量超过指定阈值"
      description: "10.100.2.234,消息队列集群消息数量:{{ $value }}"
```

##### /rules/rabbitmq-alert.rules

```
global:
route:
  group_by: ['alertname']
  group_wait: 35s
  group_interval: 5m
  repeat_interval: 5m
  receiver: 'web.hook'
receivers:
- name: 'web.hook'
  webhook_configs:
  - url: 'http://iworkserver2dev.51job.com/web/test/test/testone'

inhibit_rules:
  - source_match:
      severity: 'critical'
    target_match:
      severity: 'warning'
    equal: ['alertname', 'dev', 'instance']

```

#### 效果

<p>
向队列中插入超过规则阈值的消息，等待一会就会出现如下图的告警。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_5.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_6.png?raw=true)

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-11-prometheus-alertmanager/tu_7.png?raw=true)

<p>
在webapi中可接受如下规则的参数。
</p>

```
{
  "version": "4",
  "groupKey": <string>,              // key identifying the group of alerts (e.g. to deduplicate)
  "truncatedAlerts": <int>,          // how many alerts have been truncated due to "max_alerts"
  "status": "<resolved|firing>",
  "receiver": <string>,
  "groupLabels": <object>,
  "commonLabels": <object>,
  "commonAnnotations": <object>,
  "externalURL": <string>,           // backlink to the Alertmanager.
  "alerts": [
    {
      "status": "<resolved|firing>",
      "labels": <object>,
      "annotations": <object>,
      "startsAt": "<rfc3339>",
      "endsAt": "<rfc3339>",
      "generatorURL": <string>,      // identifies the entity that caused the alert
      "fingerprint": <string>        // fingerprint to identify the alert
    },
    ...
  ]
}
```

```
{
	"receiver": "web\\.hook",
	"status": "firing",
	"alerts": [{
		"status": "firing",
		"labels": {
			"alertname": "rabbitmqMessagesReadyAlert",
			"severity": "page",
			"test1": "aaa",
			"test2": "bbb"
		},
		"annotations": {
			"description": "10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214",
			"summary": "\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"
		},
		"startsAt": "2021-06-11T08:43:26.078Z",
		"endsAt": "0001-01-01T00:00:00Z",
		"generatorURL": "http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1",
		"fingerprint": "3be6339e818c13e9"
	}],
	"groupLabels": {
		"alertname": "rabbitmqMessagesReadyAlert"
	},
	"commonLabels": {
		"alertname": "rabbitmqMessagesReadyAlert",
		"severity": "page",
		"test1": "aaa",
		"test2": "bbb"
	},
	"commonAnnotations": {
		"description": "10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214",
		"summary": "\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"
	},
	"externalURL": "http:\/\/Dev-mHRO:9093",
	"version": "4",
	"groupKey": "{}:{alertname=\"rabbitmqMessagesReadyAlert\"}",
	"truncatedAlerts": 0,
	"is_time_task": 0
}
```

#### 其他发现

<p>
在测试接收报警消息时，预想是每5分钟(repeat_interval)收到调用api的信息，但是实际为10分钟，猜想是(group_interval+repeat_interval)。
</p>

```
----------------------------------------------------------------------
Date:[2021-06-11 16:44:01.098]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[9773bac772aebf1e79ef45dfd73a32c2]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:212","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:212","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:212","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:212","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
----------------------------------------------------------------------
Date:[2021-06-11 16:54:01.102]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[49772a399056ae72323ce2223c51ee8d]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:214","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
----------------------------------------------------------------------
Date:[2021-06-11 17:04:01.104]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[54fb8c774aadbc6c2ae0f5dc60f1c828]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
----------------------------------------------------------------------
```

<p>
测试将<code>group_interval=1s(不能为0)</code>，则接收消息间隔为5分钟。
</p>

```
----------------------------------------------------------------------
Date:[2021-06-11 17:30:15.988]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[dc21d978ded8ce296135352265f648d4]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
----------------------------------------------------------------------
Date:[2021-06-11 17:35:16.353]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[f819860918399f2eb2849d7b6b5830ee]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
----------------------------------------------------------------------
Date:[2021-06-11 17:40:16.535]
ClientIP:[127.0.0.1]
ServerIP:[{"ens18":"10.100.2.234"}]
Referer:[]
Url:[web/test/test/testone]
UserID:[]
RequestID:[eae80215ca6bf490bd92797951cd89ae]
AllParams:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Memo:[{"receiver":"web\\.hook","status":"firing","alerts":[{"status":"firing","labels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"annotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"startsAt":"2021-06-11T08:43:26.078Z","endsAt":"0001-01-01T00:00:00Z","generatorURL":"http:\/\/Dev-mHRO:9090\/graph?g0.expr=sum%28rabbitmq_queue_messages_ready+%2A+on%28instance%29+group_left%28rabbitmq_cluster%29+rabbitmq_identity_info%7Brabbitmq_cluster%3D%22rabbit%4010.100.2.234%22%7D%29+%3E+210&g0.tab=1","fingerprint":"3be6339e818c13e9"}],"groupLabels":{"alertname":"rabbitmqMessagesReadyAlert"},"commonLabels":{"alertname":"rabbitmqMessagesReadyAlert","severity":"page","test1":"aaa","test2":"bbb"},"commonAnnotations":{"description":"10.100.2.234,\u6d88\u606f\u961f\u5217\u96c6\u7fa4\u6d88\u606f\u6570\u91cf:218","summary":"\u6d88\u606f\u961f\u5217\u6d88\u606f\u6570\u91cf\u8d85\u8fc7\u6307\u5b9a\u9608\u503c"},"externalURL":"http:\/\/Dev-mHRO:9093","version":"4","groupKey":"{}:{alertname=\"rabbitmqMessagesReadyAlert\"}","truncatedAlerts":0,"is_time_task":0}]
Trace:[]
```

## 参考资料

[Prometheus告警处理](https://www.prometheus.wang/alert/)

[Alertmanager官网](https://prometheus.io/docs/alerting/latest/overview/)

[Alerting rules官网](https://prometheus.io/docs/prometheus/latest/configuration/alerting_rules/)

[Configuring rules官网](https://prometheus.io/docs/prometheus/latest/configuration/recording_rules/)

