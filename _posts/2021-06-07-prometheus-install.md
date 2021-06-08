---
layout:     post
title:      "Prometheus安装"
subtitle:   "Prometheus Install"
date:       2021-06-08 10:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - prometheus
---

> 介绍Prometheus的架构及主要组件，Prometheus与Grafana的安装，常用监控的配置。

## 概要

#### 基本架构

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-07-prometheus-install/tu_3.png?raw=true)

#### 组件

##### Prometheus Server

<p>
Prometheus Server是Prometheus组件中的核心部分，负责实现对监控数据的获取，存储以及查询。 Prometheus Server可以通过静态配置管理监控目标，也可以配合使用Service Discovery的方式动态管理监控目标，并从这些监控目标中获取数据。其次Prometheus Server需要对采集到的监控数据进行存储，Prometheus Server本身就是一个时序数据库，将采集到的监控数据按照时间序列的方式存储在本地磁盘当中。最后Prometheus Server对外提供了自定义的PromQL语言，实现对数据的查询以及分析。
</p>

<p>
Prometheus Server内置的Express Browser UI，通过这个UI可以直接通过PromQL实现数据的查询以及可视化。
</p>

<p>
Prometheus Server的联邦集群能力可以使其从其他的Prometheus Server实例中获取数据，因此在大规模监控的情况下，可以通过联邦集群以及功能分区的方式对Prometheus Server进行扩展。
</p>

##### Exporters

<p>
Exporter将监控数据采集的端点通过HTTP服务的形式暴露给Prometheus Server，Prometheus Server通过访问该Exporter提供的Endpoint端点，即可获取到需要采集的监控数据。
</p>

<p>
一般来说可以将Exporter分为2类：
</p>

- 直接采集：这一类Exporter直接内置了对Prometheus监控的支持，比如cAdvisor，Kubernetes，Etcd，Gokit，RabbitMQ等，都直接内置了用于向Prometheus暴露监控数据的端点。
- 间接采集：间接采集，原有监控目标并不直接支持Prometheus，因此我们需要通过Prometheus提供的Client Library编写该监控目标的监控采集程序。例如： Mysql Exporter，JMX Exporter，Consul Exporter等。

##### AlertManager

<p>
在Prometheus Server中支持基于PromQL创建告警规则，如果满足PromQL定义的规则，则会产生一条告警，而告警的后续处理流程则由AlertManager进行管理。在AlertManager中我们可以与邮件，Slack等等内置的通知方式进行集成，也可以通过Webhook自定义告警处理方式。AlertManager即Prometheus体系中的告警处理中心。
</p>

##### PushGateway

<p>
由于Prometheus数据采集基于Pull模型进行设计，因此在网络环境的配置上必须要让Prometheus Server能够直接与Exporter进行通信。 当这种网络需求无法直接满足时，就可以利用PushGateway来进行中转。可以通过PushGateway将内部网络的监控数据主动Push到Gateway当中。而Prometheus Server则可以采用同样Pull的方式从PushGateway中获取到监控数据。
</p>

## 安装

#### Prometheus

<p>
Prometheus是基于Golang编写，编译后的软件包，不依赖于任何的第三方依赖。从<a href="https://prometheus.io/download/" target="_blank">官网</a>获取对应平台的最新二进制包，这里选择<code>prometheus-2.27.1.linux-amd64.tar.gz</code>
</p>

```
curl -LO https://github.com/prometheus/prometheus/releases/download/v2.27.1/prometheus-2.27.1.linux-amd64.tar.gz
```

<p>
解压，可将Prometheus相关的命令，添加到系统环境变量方便使用。
</p>

```
tar -xzf prometheus-2.27.1.linux-amd64.tar.gz
```

<p>
Promtheus作为一个时间序列数据库，其采集的数据会以文件的形似存储在本地中，默认的存储路径为data/，因此需要手动在可执行位置创建该目录：
</p>

```
mkdir -p data
```

<p>
也可以通过参数--storage.tsdb.path="data/"修改本地数据存储的路径。
启动prometheus服务，其会默认加载当前路径下的prometheus.yml文件：
</p>

```
./prometheus &
```

<p>
查看prometheus是否启动成功
</p>

```
[root@Dev-mHRO /]# ps -eaf|grep prometheus
root     31013 29226  0 5月31 ?       00:41:14 ./prometheus --web.enable-lifecycle
```

<p>
使用浏览器访问<code>http://{安装ip}:9090/</code>可查看网站信息。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-07-prometheus-install/tu_1.png?raw=true)

##### 热重启

<p>
如果需要修改配置文件，可通过如下命令热重启。
</p>

```
curl -XPOST http://127.0.0.1:9090/-/reload
```

##### 历史数据清理

<p>
prometheus中的数据并不是永久存储，会在一定时间后自动清除，默认为15天，可以通过如下参数控制。
</p>

```
--storage.tsdb.retention=10d
```

#### Grafana

<p>
Grafana使用rpm的方式来安装
</p>

```
wget https://dl.grafana.com/oss/release/grafana-7.5.7-1.x86_64.rpm
sudo yum install grafana-7.5.7-1.x86_64.rpm
```

<p>
使用systemctl来运行grafana
</p>

```
sudo systemctl daemon-reload
sudo systemctl start grafana-server
sudo systemctl status grafana-server
```

<p>
查看grafana是否启动成功
</p>

```
[root@Dev-mHRO /]# ps -eaf|grep grafana
grafana   7917     1  0 5月31 ?       00:08:54 /usr/sbin/grafana-server --config=/etc/grafana/grafana.ini --pidfile=/var/run/grafana/grafana-server.pid --packaging=rpm cfg:default.paths.logs=/var/log/grafana cfg:default.paths.data=/var/lib/grafana cfg:default.paths.plugins=/var/lib/grafana/plugins cfg:default.paths.provisioning=/etc/grafana/provisioning
```

<p>
使用浏览器访问<code>http://{安装ip}:3000/</code>可查看网站信息。
</p>

![image](https://github.com/xuanxuan2016/xuanxuan2016.github.io/blob/master/img/2021-06-07-prometheus-install/tu_2.png?raw=true)

## 常用监控

#### RabbitMQ

##### exporter配置

<p>
为RabbitMQ集群设置一个区分性的名称
</p>

```
#查看集群信息
rabbitmq-diagnostics -q cluster_status
#设置集群名称
rabbitmqctl -q set_cluster_name {集群名}
```

<p>
所有节点上启用rabbitmq_prometheus插件
</p>

```
rabbitmq-plugins enable rabbitmq_prometheus
```

<p>
使用浏览器访问<code>http://{安装ip}:15692/metrics/</code>可查看网站信息。
</p>

##### prometheus配置

```
#prometheus.yml
- job_name: 'rabbitmq'
    static_configs:
      - targets: ['10.100.2.235:15692','10.100.3.83:15692','10.100.2.234:15692']
```

```
#热重启prometheus
curl -XPOST http://127.0.0.1:9090/-/reload
```

##### grafana配置

<p>
使用<a href="https://grafana.com/grafana/dashboards/10991" target="_blank">dashboard=10991</a>来显示数据。
</p>

#### Redis

##### exporter配置

<p>
从<a href="https://github.com/oliver006/redis_exporter/releases" target="_blank">git</a>上下载最新的exporter文件，这里选择<code>redis_exporter-v1.23.1.linux-amd64.tar.gz
</code>。
</p>

```
#解压
tar -xvf redis_exporter-v1.23.1.linux-amd64.tar.gz
#将redis_exporter移动到合适位置
/usr/local/exporter/redis_exporter
```

<p>
配置使用systemctl维护，<code>--redis.addr=</code>为了可以从多个ip抓取数据，注意密码需要一致。
</p>

```
#/etc/systemd/system/redis_exporter.service
[Unit]
Description=redis_exporter
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/exporter/redis_exporter --redis.addr=  --redis.password=123456
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```
#维护命令
systemctl start redis_exporter  开启服务
systemctl stop redis_exporter   关闭服务
systemctl restart redis_exporter    重启服务
systemctl status redis_exporter    查看服务状态
systemctl enable redis_exporter    将服务设置为开机自启动
systemctl disable redis_exporter    禁止服务开机自启动
systemctl is-enabled redis_exporter    查看服务是否开机启动
systemctl list-unit-files|grep enabled    查看开机启动的服务列表
systemctl --failed    查看启动失败的服务列表
```

##### prometheus配置

```
#prometheus.yml
## config for the multiple Redis targets that the exporter will scrape
- job_name: 'redis_exporter_targets'
    static_configs:
      - targets:
        - redis://10.100.3.106:6380
        - redis://10.100.2.234:6380
        - redis://10.100.2.234:6381
    metrics_path: /scrape
    relabel_configs:
      - source_labels: [__address__]
        target_label: __param_target
      - source_labels: [__param_target]
        target_label: instance
      - target_label: __address__
        replacement: 127.0.0.1:9121

## config for scraping the exporter itself
- job_name: 'redis_exporter'
    static_configs:
      - targets:
        - 127.0.0.1:9121
```

```
#热重启prometheus
curl -XPOST http://127.0.0.1:9090/-/reload
```

##### grafana配置

<p>
使用<a href="https://grafana.com/grafana/dashboards/763" target="_blank">dashboard=763</a>来显示数据。
</p>

#### MySql

##### exporter配置

<p>
mysql添加exporter用户
</p>

```
CREATE USER 'mysqld_exporter'@'localhost' IDENTIFIED BY '12345678' WITH MAX_USER_CONNECTIONS 3;
GRANT PROCESS, REPLICATION CLIENT, SELECT ON *.* TO 'mysqld_exporter'@'localhost';
flush privileges;
```

<p>
从<a href="https://prometheus.io/download/#mysqld_exporter" target="_blank">官网</a>上下载最新的exporter文件，这里选择<code>mysqld_exporter-0.13.0.linux-amd64.tar.gz
</code>。
</p>

```
#解压
tar -xvf mysqld_exporter-0.13.0.linux-amd64.tar.gz
#将mysqld_exporter移动到合适位置
/usr/local/exporter/mysqld_exporter
```

<p>
配置使用systemctl维护
</p>

```
#/etc/systemd/system/mysql_exporter.service
[Unit]
Description=mysql_exporter
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/exporter/mysqld_exporter --config.my-cnf="/usr/local/exporter/234_db.cnf" --web.listen-address=":9105"
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```
#维护命令
systemctl start mysql_exporter  开启服务
systemctl stop mysql_exporter   关闭服务
systemctl restart mysql_exporter    重启服务
systemctl status mysql_exporter    查看服务状态
systemctl enable mysql_exporter    将服务设置为开机自启动
systemctl disable mysql_exporter    禁止服务开机自启动
systemctl is-enabled mysql_exporter    查看服务是否开机启动
systemctl list-unit-files|grep enabled    查看开机启动的服务列表
```

##### prometheus配置

```
#prometheus.yml
- job_name: 'mysqld'
    static_configs:
      - targets: ['127.0.0.1:9104']
```

```
#热重启prometheus
curl -XPOST http://127.0.0.1:9090/-/reload
```

##### grafana配置

<p>
使用<a href="https://grafana.com/grafana/dashboards/7362" target="_blank">dashboard=7362</a>来显示数据。
</p>

#### Linux

##### exporter配置

<p>
从<a href="https://prometheus.io/download/#node_exporter" target="_blank">官网</a>上下载最新的exporter文件，这里选择<code>node_exporter-1.1.2.linux-amd64.tar.gz
</code>。
</p>

```
#解压
tar -xvf node_exporter-1.1.2.linux-amd64.tar.gz
#将node_exporter移动到合适位置
/usr/local/exporter/node_exporter
```

<p>
配置使用systemctl维护
</p>

```
#/etc/systemd/system/node_exporter.service
[Unit]
Description=node_exporter
After=network.target

[Service]
Type=simple
User=root
ExecStart=/usr/local/exporter/node_exporter
Restart=on-failure

[Install]
WantedBy=multi-user.target
```

```
#维护命令
systemctl start node_exporter  开启服务
systemctl stop node_exporter   关闭服务
systemctl restart node_exporter    重启服务
systemctl status node_exporter    查看服务状态
systemctl enable node_exporter    将服务设置为开机自启动
systemctl disable node_exporter    禁止服务开机自启动
systemctl is-enabled node_exporter    查看服务是否开机启动
systemctl list-unit-files|grep enabled    查看开机启动的服务列表
systemctl --failed    查看启动失败的服务列表
```

##### prometheus配置

```
#prometheus.yml
- job_name: 'node'
    static_configs:
      - targets: ['127.0.0.1:9100']
```

```
#热重启prometheus
curl -XPOST http://127.0.0.1:9090/-/reload
```

##### grafana配置

<p>
使用<a href="https://grafana.com/grafana/dashboards/13978" target="_blank">dashboard=13978</a>来显示数据。
</p>

## 参考资料

[Prometheus官网](https://prometheus.io/docs/introduction/overview/)

[Grafana官网](https://grafana.com/)

[Prometheus中文文档](https://www.prometheus.wang/)

[Grafana官网安装](https://grafana.com/docs/grafana/latest/installation/rpm/#install-manually-with-yum)

[Monitoring with Prometheus & Grafana](https://www.rabbitmq.com/prometheus.html#overview-grafana)
