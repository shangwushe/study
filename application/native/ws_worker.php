<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/3/19
 * Time: 3:21 PM
 */
use Workerman\Worker;
require_once __DIR__ . '/../../vendor/workerman/workerman/Autoloader.php';

// 注意：这里与上个例子不同，使用的是websocket协议
$ws_worker = new Worker("websocket://0.0.0.0:2000");

// 启动4个进程对外提供服务
$ws_worker->count = 4;

// 当收到客户端发来的数据后返回hello $data给客户端
$ws_worker->onMessage = function($connection, $data)
{
    // 向客户端发送hello $data
    $connection->send('hello ' . $data);
};

// 运行worker
Worker::runAll();