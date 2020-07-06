<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 6/28/19
 * Time: 4:51 PM
 */
namespace app\index\controller;

class Redis extends \think\Controller{
    /**
     * test the redis connect status
     */
    public function connect(){
        $result = redis_connect();
        dump($result);
    }

    /**
     * test redis ping
     */
    public function ping(){
        $result = redis_ping();
        dump($result);
    }

    /**
     * test redis quit
     */
    public function quit(){
        $result = redis_quit();
        dump($result);
    }

    /**
     * test redis del
     */
    public function del(){
        $key = input('param.key','myname');
        $result = redis_del($key);
        dump($result);
    }

    /**
     * test redis set
     */
    public function set(){
        $key = input('param.key','myname');
        $value = input('param.value','gxk');
        $result = redis_set($key,$value);
        dump($result);
    }


    /**
     * test redis del
     */
    public function get(){
        $key = input('param.key','myname');
        $result = redis_get($key);
        dump($result);
    }

    /**
     * test redis hset
     */
    public function hset(){
        $key = input('param.key','myname');
        $field = input('param.field','firstname');
        $value = input('param.value','g');
        $result = redis_hset($key,$field,$value);
        dump($result);
    }


    /**
     * test redis hget
     */
    public function hget(){
        $key = input('param.key','myname');
        $field = input('param.field','firstname');
        $result = redis_hget($key,$field);
        dump($result);
    }

    /**
     * test redis hmset
     */
    public function hmset(){
        $key = input('param.key','scores');
        $field = input('param.field/a',[1=>[1,2],2=>60]);
        $result = redis_hmset($key,$field);
        dump($result);
    }


    /**
     * test redis hmget
     */
    public function hmget(){
        $key = input('param.key','myname');
        $field = input('param.field/d',[1,2]);
        $result = redis_hmget($key,$field);
        dump($result);
    }

    /**
     * test redis hgetall
     */
    public function hgetall(){
        $key = input('param.key','myname');
        $result = redis_hgetall($key);
        dump($result);
    }

    /**
     * test redis lpush
     */
    public function lpush(){
        $key = input('param.key','list_key');
        $value = input('param.value/d',[0=>'nihao',1=>'zheshi']);
        $result = redis_lpush($key,$value);
        dump($result);
    }


    /**
     * test redis lget
     */
    public function lget(){
        $key = input('param.key','list_key');
        $index = input('param.index/d',-1);
        $result = redis_lget($key,$index);
        dump($result);
    }
}