<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/3/19
 * Time: 1:58 PM
 */
namespace app\index\controller;

use think\facade\Cache;

class Test extends \think\Controller{
    public function tag(){
        Cache::tag('personal')->set('name','gxk');
    }
    public function getTagValue(){
        echo Cache::tag('personal')->get('name');
    }
    public function testWsWorker(){
        return $this->fetch();
    }
}