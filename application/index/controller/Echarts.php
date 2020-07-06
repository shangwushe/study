<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-12-27
 * Time: 18:06
 */
namespace app\index\controller;
use think\Controller;

class Echarts extends Controller {
    public function index(){
        return $this->fetch();
    }
}