<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 6/24/19
 * Time: 4:57 PM
 */
namespace app\index\controller;

use think\Controller;

class Worker extends Controller {
    public function index(){
        return $this->fetch();
    }
}