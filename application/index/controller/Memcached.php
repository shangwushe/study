<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/1/19
 * Time: 2:30 PM
 */
namespace app\index\controller;

class Memcached extends \think\Controller {
    /**
     * test memcached connect function
     */
    public function connect(){
        $memcached = memcached_connect();
        dump($memcached);
    }

    /**
     * test memcached has function
     */
    public function has($key='myname'){
        dump(memcached_has($key));
    }

    /**
     * test memcached set function
     */
    public function set(){
        $key = input('param.key');
        $value = input('param.value');
        $expire = input('param.expire/d',900);
        dump(memcached_set($key,$value,$expire));
    }
}
