<?php
/**
 * Created by PhpStorm
 * User: Administrator
 * Date: 2020-04-22
 * Time: 15:14
 */
namespace app\index\controller;

use PhpAmqpLib\Message\AMQPMessage;

class RabbitMq extends \think\Controller{
    public function producter(){
        $queue_name = 'say_hello';
        $connect = connect_mq();
        $channel = $connect->channel();
        $channel->queue_declare($queue_name, false, false, false, false);
        //$data = implode(',',);
        $data = json_encode(['name'=>'gxk','sex'=>'man']);
        $msg = new AMQPMessage($data);
        $res = $channel->basic_publish($msg, '', $queue_name);
        echo ' [x] Sent ', $data, "\n";
        $channel->close();
        $connect->close();
    }

    public function customer(){
        $queue_name = 'say_hello';
        $connect = connect_mq();
        $channel = $connect->channel();
        $channel->queue_declare($queue_name, false, false, false, false);
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        $callback = function ($msg) {
            echo ' [x] Received ', $msg->body, "\n";
        };

        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }
        $channel->close();
        $connect->close();
    }
}