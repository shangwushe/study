<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * get_system_config function
 * get system environment config
 * @param $key string|null
 * @return string|array
 * @author gxk
 * @date 2019-06-29
 */
function get_system_config($key=null){
    $systemConfig = config(config('system_config'));
    $result = null;
    if (!empty($systemConfig) && !empty($key)) {
        $result = $systemConfig[$key];
    }
    return $result;
}

/**********start redis**********/

/**
 * redis_connect function
 * @param $options array
 * @return boolean|object
 * @author gxk
 * @date 2019-06-29
 */
function redis_connect($options=[]){
    if (!extension_loaded('redis')) {
        throw new \BadFunctionCallException('not support: redis');
    }

    $defaultOptions = get_system_config('redis')??[
            'host'       => '127.0.0.1',
            'port'       => 6379,
            'password'   => '',
            'select'     => 0,
            'timeout'    => 0,
            'expire'     => 0,
            'persistent' => false,
            'prefix'     => '',
        ];

    $options = empty($options)?$defaultOptions:array_merge($defaultOptions, $options);

    $func = $options['persistent'] ? 'pconnect' : 'connect';
    $redis = new \Redis;
    try {
        $redis->$func($options['host'], $options['port'], $options['timeout']);

        if ('' != $options['password']) {
            $redis->auth($options['password']);
        }

        if (0 != $options['select']) {
            $redis->select($options['select']);
        }
    } catch (Exception $e) {
        $redis = false;
    }
    return $redis;
}


/**
 * redis_ping function
 * redis ping
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_ping(){
    $result = false;
    if ($redis = redis_connect()) {
        $result = $redis->ping();
    }
    return $result;
}

/**
 * redis_quit function
 * redis quit
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_quit(){
    $result = false;
    if ($redis = redis_connect()) {
        $result = $redis->close();
    }
    return $result;
}

/**
 * redis_expire function
 * redis expire
 * @param $key string|null
 * @param $second int 秒数
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_expire($key='',$second=0){
    $result = false;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->expire($key,$second);
    }
    return $result;
}

/**
 * redis_expire_at function
 * redis expireAt
 * @param string $key 键
 * @param int $timestamp 时间戳
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_expire_at($key='',$timestamp=null){
    $result = false;
    isset($timestamp) || $timestamp = time() + 86400;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->expireAt($key,$timestamp);
    }
    return $result;
}

/**
 * redis_ttl function
 * redis ttl
 * @param string $key 键
 * @return mixed
 * @author gxk
 * @date 2019-06-29
 */
function redis_ttl($key=''){
    $result = false;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->ttl($key);
    }
    return $result;
}

/**
 * redis_persist function
 * redis persist
 * @param string $key 键
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_persist($key=''){
    $result = false;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->persist($key);
    }
    return $result;
}

/**
 * redis_del function
 * redis del
 * @param string $key 键
 * @return int
 * @author gxk
 * @date 2019-06-29
 */
function redis_del($key=''){
    $result = false;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->del($key);
    }
    return $result;
}

/**
 * redis_set function
 * redis set
 * @param string $key 键
 * @param string $value 值
 * @return boolean|int
 * @author gxk
 * @date 2019-06-29
 */
function redis_set($key='',$value=''){
    $result = false;
    if (!empty($key) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->set($key,$value);
    }
    return $result;
}

/**
 * redis_get function
 * redis get
 * @param string $key 键
 * @return string|boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_get($key=''){
    $result = null;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->get($key);
    }
    return $result;
}

/**
 * redis_hset function
 * redis hSet
 * @param string $key 键
 * @param string $field 字段
 * @param array $value 值
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_hset($key='',$field='',$value=''){
    $result = false;
    if (!empty($key) && !empty($field) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->hSet($key,$field,$value);
    }
    return $result;
}

/**
 * redis_hget function
 * redis hGet
 * @param string $key 键
 * @param string $field 字段
 * @return string|null
 * @author gxk
 * @date 2019-06-29
 */
function redis_hget($key='',$field=''){
    $result = null;
    if (!empty($key) && !empty($field) && $redis = redis_connect()) {
        $result = $redis->hGet($key,$field);
    }
    return $result;
}

/**
 * redis_hmset function
 * redis hMSet
 * @param $key string 键
 * @param $value array 值
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_hmset($key='',$value=[]){
    $result = false;
    if (!empty($key) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->hMSet($key,$value);
    }
    return $result;
}

/**
 * redis_hmget function
 * redis hMGet
 * @param $key string 键
 * @param $field array 字段数组
 * @return array
 * @author gxk
 * @date 2019-06-29
 */
function redis_hmget($key='',$field=[]){
    $result = null;
    if (!empty($key) && !empty($field) && $redis = redis_connect()) {
        $result = $redis->hMGet($key,$field);
    }
    return $result;
}

/**
 * redis_hgetall function
 * redis hGetAll
 * @param string $key 键
 * @return array
 * @date 2019-06-29
 */
function redis_hgetall($key=''){
    $result = null;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->hGetAll($key);
    }
    return $result;
}

/**
 * redis_lpush function
 * redis lPush
 * @param $key string 键
 * @param $value array|string 值
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_lpush($key='',$value=''){
    $result = false;
    if (!empty($key) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->lPush($key,$value);
    }
    return $result;
}

/**
 * redis_rpush function
 * redis rPush
 * @param $key string 键
 * @param $value array 值
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_rpush($key='',$value=''){
    $result = false;
    if (!empty($key) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->rPush($key,$value);
    }
    return $result;
}

/**
 * redis_lset function
 * redis lSet
 * @param $key string 键
 * @param $index int 索引
 * @param $value array 值
 * @return boolean|null
 * @autor gxk
 * @date 2019-06-29
 */
function redis_lset($key='',$index=0,$value=''){
    $result = false;
    $index = intval($index);
    if (!empty($key) && !empty($value) && $redis = redis_connect()) {
        $result = $redis->lSet($key,$index,$value);
    }
    return $result;
}

/**
 * redis_lget function
 * redis lGet
 * @param string $key 键
 * @param int $index 索引
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 */
function redis_lget($key='',$index=-1){
    $result = false;
    $index = intval($index);
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->lGet($key,$index);//TODO::deprecated
    }
    return $result;
}

/**
 * redis_lgetrange function
 * redis lGetRange
 * @param string $key 键
 * @param int $start 起始
 * @param int $end 末尾
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 **/
function redis_lgetrange($key='',$start=0,$end=-1){
    $result = false;
    $start = intval($start);
    $end = intval($end);
    if (!empty($key) && $start >= 0 && $redis = redis_connect()) {
        $result = $redis->lGetRange($key,$start,$end);
    }
    return $result;
}

/**
 * redis_lsize function
 * redis lSize
 * @param string $key 键
 * @return boolean
 * @author gxk
 * @date 2019-06-29
 **/
function redis_lsize($key=''){
    $result = false;
    if (!empty($key) && $redis = redis_connect()) {
        $result = $redis->lSize($key);
    }
    return $result;
}

/**********end redis**********/