<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/29/19
 * Time: 2:35 PM
 */
namespace app\index\controller;

class SendMessage{
    function send(){
        send_message('uid1','hello');
    }
}