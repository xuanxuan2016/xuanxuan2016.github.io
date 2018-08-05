<?php

include getenv('InterviewRootPath') . 'ComModule/AutoLoader/Loader.php';

use ComModule\Log\Log;

/**
 * 消息发送客户端
 */
class SwooleSendMsgClient {

    /**
     * 消息结束符
     */
    private $strEof = "\r\n";

    /**
     * 客户端实例
     */
    private $objClient = null;

    /**
     * 构造函数
     */
    public function __construct() {
        $this->objClient = new swoole_client(SWOOLE_SOCK_TCP);
    }

    /**
     * 服务端连接
     */
    public function connect() {
        if ($this->objClient->connect(SWOOLE_SEND_MSG_HOST, SWOOLE_SEND_MSG_PORT) === false) {
            Log::getInstance()->log('swoole_sendmsg服务器connect错误', Log::LOG_SWOOLEERR);
            $this->objClient = null;
        }
    }

    /**
     * 消息发送
     */
    public function send() {
        //服务器是否成功连接
        if (is_null($this->objClient)) {
            return;
        }
        for ($i = 1; $i <= 100; $i++) {
            $blnFlag = $this->objClient->send('aa_' . $i . $this->strEof);
        }
    }

    /**
     * 开始操作
     */
    public function run() {
        $this->connect();
        $this->send();
    }

}

$objSwooleSendMsgClient = new SwooleSendMsgClient();
$objSwooleSendMsgClient->run();
