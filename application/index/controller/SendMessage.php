<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/29/19
 * Time: 2:35 PM
 */
namespace app\index\controller;

class SendMessage extends \think\Controller{
    function send(){
        send_message('gxk','hello');

    }
    function show_message(){
        return $this->fetch();
    }
}