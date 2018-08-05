<?php

include getenv('InterviewRootPath') . 'ComModule/AutoLoader/Loader.php';

use BLL\swoole\BLLSendMsg;
use ComModule\Log\Log;

/**
 * 消息发送服务端
 */
class SwooleSendMsgServer {

    /**
     * 服务端实例
     */
    private $objServer = null;

    /**
     * 构造函数
     */
    public function __construct() {
        //实例化对象
        //swoole_get_local_ip()获取本机ip
        $this->objServer = new swoole_server(SWOOLE_SEND_MSG_HOST, SWOOLE_SEND_MSG_PORT);
        //设置运行参数
        $this->objServer->set(array(
            'daemonize' => 1, //以守护进程执行
            'max_request' => 10000, //worker进程在处理完n次请求后结束运行
            'worker_num' => 3,
            'task_worker_num' => 2, //task进程的数量
            "task_ipc_mode " => 3, //使用消息队列通信，并设置为争抢模式,
            'heartbeat_check_interval' => 5, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            'heartbeat_idle_time' => 10, //TCP连接的最大闲置时间，单位s , 如果某fd最后一次发包距离现在的时间超过则关闭
            'open_eof_split' => true,
            'package_eof' => "\r\n",
            "log_file" => LOG_PATH . "swoole.log"
        ));
        //设置事件回调
        $this->objServer->on('Connect', array($this, 'onConnect'));
        $this->objServer->on('Receive', array($this, 'onReceive'));
        $this->objServer->on('Finish', array($this, 'onFinish'));
        $this->objServer->on('Task', array($this, 'onTask'));
        //启动服务
        $this->objServer->start();
    }

    /**
     * 有新的连接进入时
     */
    public function onConnect($server, $fd, $from_id) {
        
    }

    /**
     * 接收到数据时
     */
    public function onReceive($serv, $fd, $reactor_id, $strData) {
        //模拟worker进程处理时间
        usleep(100 * 1000);

        $strData = trim($strData);
        $task_id = $this->objServer->task($strData);
        $strtmp = $serv->taskworker ? 'task进程' : 'worker进程';
        Log::getInstance()->log("worker[进程类别={$strtmp} worker_id={$serv->worker_id} worker_pid={$serv->worker_pid} task_id={$task_id}]接收数据，任务数据[{$strData}]");
    }

    /**
     * task任务完成时
     */
    public function onFinish($serv, $task_id, $strData) {
        $strtmp = $serv->taskworker ? 'task进程' : 'worker进程';
        Log::getInstance()->log("finish[进程类别={$strtmp} worker_id={$serv->worker_id} worker_pid={$serv->worker_pid} task_id={$task_id}]处理数据，任务数据[{$strData}]");
    }

    /**
     * 处理投递的任务
     */
    public function onTask($serv, $task_id, $src_worker_id, $strData) {
        $strtmp = $serv->taskworker ? 'task进程' : 'worker进程';
        Log::getInstance()->log("task[进程类别={$strtmp} worker_id={$serv->worker_id} worker_pid={$serv->worker_pid} src_worker_id={$src_worker_id} task_id={$task_id}]处理数据，任务数据[{$strData}]");
        $serv->finish($strData);
    }

}

//运行服务
$objSwooleSendMsgServer = new SwooleSendMsgServer();
