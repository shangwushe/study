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

    /**
     * test memcached get function
     */
    public function get($key='myname'){
        dump(memcached_get($key));
    }

    /**
     * test memcached inc function
     */
    public function inc(){
        $key = input('param.key','age');
        $step = input('param.step/d',2);
        dump(memcached_inc($key,$step));
    }

    /**
     * test memcached dec function
     */
    public function dec(){
        $key = input('param.key','age');
        $step = input('param.step/d',2);
        dump(memcached_dec($key,$step));
    }

    /**
     * test memcached rm function
     */
    public function rm(){
        $key = input('param.key','myname');
        $ttl = input('param.ttl');
        dump(memcached_rm($key,$ttl));
    }

    /**
     * test memcached tag function
     */
    public function tag(){
        $key = input('param.key','age');
        $name = input('param.name','tagname');
        $overlay = input('param.overlay',false);
        dump(memcached_tag($key,$name,$overlay));
    }
}
