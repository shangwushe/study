<?php
/**
 * Created by PhpStorm
 * User: Administrator
 * Date: 2020-06-16
 * Time: 0:20
 */
namespace app\editor\controller;

use think\facade\View;

class Ckeditor{
    public function index(){
        return View::fetch(__FUNCTION__);
    }
}