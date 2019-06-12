---
layout:     post
title:      "php中异常与错误处理"
subtitle:   "php Error and Exception"
date:       2018-08-16 20:50
author:     "BeautyMyth"
header-img: "img/post-bg-2015.jpg"
catalog: true
multilingual: false
tags:
    - php
---

> 介绍php中的异常与错误处理。

## 前言

<p>
在使用php时，总会遇到意想不到的异常与错误，为了在知道错误后可以对代码进行调整，需要将异常与错误进行收集，那么一般可以怎么做呢。
</p>

## web服务器处理

<p>
此方式是借助于web服务器来进行捕捉异常与错误信息。
</p>

#### 1.php.ini

<p>
通过修改如下配置，即可记录到错误日志。
</p>

<p>
tips:确保<code>error_log</code>配置的文件，apache用户（如nobody）有读写权限
</p>

```
#设置报错级别
error_reporting = E_ALL
#不向页面输出错误
display_errors = off
#将错误记录到日志
log_errors = On
#错误日志文件
error_log=/www/htdocs/error_log
#每条日志的长度，会影响日志的完整度
log_errors_max_len = 1024
```

#### 2.虚拟目录

<p>
根据上面的配置，部署在此服务器的所有web站点日志都会记录在一个文件中，不方便对不同的站点进行监控。
</p>

<p>
可在网站的虚拟目录配置文件<code>httpd-vhosts.conf</code>或<code>httpd-ssl.conf</code>中，配置各个站点记录错误日志的位置。
</p>

<p>
tips:需要将php.ini的中<code>error_log</code>注释或将其置为空，否则优先会使用此处的配置。
</p>

```
ErrorLog "/www/htdocs/project_name/storage/log/error_log"
```


## 程序代码处理

<p>
此方式是大多数框架的处理方式，在<code>index.php</code>的入口文件中，会引入一个文件来定义php的报错级别（error_reporting），异常处理（set_exception_handler），错误处理（set_error_handler），脚本终止处理（register_shutdown_function）。
</p>

#### 1.error_reporting

<p>
设置哪些php错误需要报告出来，这里设置所有的错误。
</p>

```
error_reporting(E_ALL);
```
<p>
php错误分类如下：
</p>

<table>
    <tr>
        <td colspan="2" style="text-align:center;font-weight:bold;">Fatal 致命错误(脚本终止运行)</td>
    </tr>
    <tr>
        <td>E_ERROR</td>
        <td>致命的运行时错误。这类错误一般是不可恢复的情况，例如内存分配导致的问题。后果是导致脚本终止不再继续运行</td>
    </tr>
    <tr>
        <td>E_CORE_ERROR</td>
        <td>在PHP初始化启动过程中发生的致命错误。该错误类似 E_ERROR，但是是由PHP引擎核心产生的</td>
    </tr>
    <tr>
        <td>E_COMPILE_ERROR</td>
        <td>致命编译时错误。类似E_ERROR, 但是是由Zend脚本引擎产生的</td>
    </tr>
    <tr>
        <td>E_USER_ERROR</td>
        <td>用户产生的错误信息。类似 E_ERROR, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;font-weight:bold;">Parse 解析错误(脚本终止运行)</td>
    </tr>
    <tr>
        <td>E_PARSE</td>
        <td>编译时语法解析错误。解析错误仅仅由分析器产生</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;font-weight:bold;">Warning 警告错误(脚本不终止运行)</td>
    </tr>
    <tr>
        <td>E_WARNING</td>
        <td>运行时警告 (非致命错误)。仅给出提示信息，但是脚本不会终止运行</td>
    </tr>
    <tr>
        <td>E_CORE_WARNING</td>
        <td>PHP初始化启动过程中发生的警告 (非致命错误) 。类似 E_WARNING，但是是由PHP引擎核心产生的</td>
    </tr>
    <tr>
        <td>E_COMPILE_WARNING</td>
        <td>编译时警告 (非致命错误)。类似 E_WARNING，但是是由Zend脚本引擎产生的</td>
    </tr>
    <tr>
        <td>E_USER_WARNING</td>
        <td>用户产生的警告信息。类似 E_WARNING, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的</td>
    </tr>
    <tr>
        <td colspan="2" style="text-align:center;font-weight:bold;">Notice 通知错误(脚本不终止运行)</td>
    </tr>
    <tr>
        <td>E_NOTICE</td>
        <td>运行时通知。表示脚本遇到可能会表现为错误的情况，但是在可以正常运行的脚本里面也可能会有类似的通知</td>
    </tr>
    <tr>
        <td>E_USER_NOTICE</td>
        <td>用户产生的通知信息。类似 E_NOTICE, 但是是由用户自己在代码中使用PHP函数 trigger_error()来产生的</td>
    </tr>
</table>


#### 2.set_exception_handler

<p>
设置异常处理函数，当程序中产生的异常没有进行自己捕捉的话，会统一由此处的函数处理。特别在使用第三方类库的时候，如果出现使用上的问题，可以方便的定位问题。
</p>

```
//设置处理异常函数
set_exception_handler([$this, 'handleException'])
```

```
//处理异常函数
public function handleException($objException) {
    //非Exception异常，进行处理
    if (!$objException instanceof Exception) {
        if (method_exists($objException, 'getMessage')) {
            throw new Exception($objException->getMessage());
        } else {
            throw new Exception('未知错误');
        }
    }

    $this->getHandleException()->report($objException);

    if (!$this->objApp->runningInConsole()) {
        //非控制台运行，生成http响应并发送
        $this->getHandleException()->render($objException)->send();
    }
}
```

#### 3.set_error_handler

<p>
设置错误处理函数，此函数可以捕捉php产生的Warning、Notice级别错误。这些错误不会影响程序的正常执行，对于捕获的错误，可用于分类记录。
</p>

```
//设置处理错误函数
set_error_handler([$this, 'handleError'])
```

```
//错误处理函数
public function handleError($strErrNo, $strErrStr, $strErrFile, $intErrLine) {
    if (!(error_reporting() & $strErrNo)) {
        //如果出现的错误不在定义接受的错误范围内，则转交给php自身处理
        return false;
    }

    //日志记录
    $strLog = sprintf("\n errno:%s \n errstr:%s \n errfile:%s \n errline:%s \n", $strErrNo, $strErrStr, $strErrFile, $intErrLine);
    $this->objApp->make('log')->log($strLog, Config::get('const.Log.LOG_ERR'));
}
```

#### 4.register_shutdown_function

<p>
设置php在终止执行时的处理函数，也可以看作是脚本执行结束前最后一个函数。如下情况都可认为脚本执行结束：
</p>

- 脚本正常结束
- 异常
- die
- exit
- 脚本错误

<p>
可通过<code>error_get_last</code>获取脚本运行产生的最后一个错误，通过<code>type</code>来判断，是否为致命错误，再进行需要的逻辑处理。
</p>

```
//设置程序结束处理函数
register_shutdown_function([$this, 'handleShutDown'])
```

```
//自定义程序结束处理
public function handleShutDown() {
    if (!is_null($arrError = error_get_last()) && $this->isFatal($arrError['type'])) {
        //日志记录
        $strLog = sprintf("\n errno:%s \n errstr:%s \n errfile:%s \n errline:%s \n", $arrError['type'], $arrError['message'], $arrError['file'], $arrError['line']);
        $strLog = $this->objApp->make('log')->log($strLog, Config::get('const.Log.LOG_ERR'));
    }
}

//是否为致命错误
protected function isFatal($strType) {
    return in_array($strType, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_PARSE]);
}
```

## 参考资料

[php错误预定义常量](http://php.net/manual/zh/errorfunc.constants.php)

[php运行时配置](http://php.net/manual/zh/errorfunc.configuration.php)

[php异常处理](https://www.cnblogs.com/zyf-zhaoyafei/p/6928149.html)

[PHP7中的异常与错误处理](https://novnan.github.io/PHP/throwable-exceptions-and-errors-in-php7/)

[apache访问日志access_log](https://blog.csdn.net/zonghua521/article/details/78240038?locationNum=9&fps=1)
