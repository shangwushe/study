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

/**********start memcached**********/

/**
 * memcached_connect
 * connect memcached
 * @param $options array
 * @return object
 * @author gxk
 * @date 2019-07-01
 */
function memcached_connect($options=[]){
    return new \think\cache\driver\Memcached($options);
}

/**
 * memcached_has function
 * memcached has the key cache
 * @param $key string
 * @return  boolean
 * @author gxk
 * @date 2019-07-01
 */
function memcached_has($key){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->has($key);
    }else{
        $result = false;
    }
    return false;
}

/**
 * memcached_set function
 * set key=>value cache
 * @param $key string
 * @param $value
 * @param $expire int time out
 * @return boolean
 * @author gxk
 * @date 2019-07-01
 */
function memcached_set($key,$value,$expire=null){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->set($key,$value,$expire);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * memcached_get function
 * get the key=>value cache
 * @param $key
 * @return string
 */
function memcached_get($key){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->get($key);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * memcached_inc function
 * inc step of key , if the key not exist,reduce by 0
 * @param $key string
 * @param $step int
 * @return int|boolean
 */
function memcached_inc($key,$step=1){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->inc($key,$step);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * memcached_dec function
 * dec step of key , if the key not exist,reduce by 0
 * @param $key string
 * @param $step int
 * @return int|boolean
 */
function memcached_dec($key,$step=1){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->dec($key,$step);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * memcached_rm function
 * @param $key string
 * @param $ttl int
 * @return boolean
 */
function memcached_rm($key,$ttl=false){
    if($key&&$memcached=memcached_connect()){
        $result = $memcached->rm($key,$ttl);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * memcached_clear function
 * clean the tag cache
 * @param $tag = string
 * @return boolean
 */
function memcached_clean($tag=null){
    if($memcached=memcached_connect()){
        $result = $memcached->clean($tag);
    }else{
        $result = false;
    }
    return $result;
}

/**
 * 缓存标签
 * @access public
 * @param  string        $name 标签名
 * @param  string|array  $keys 缓存标识
 * @param  bool          $overlay 是否覆盖
 * @return $this
 */
function memcached_tag($name, $keys = null, $overlay = false){
    if($memcached=memcached_connect()){
        $result = $memcached->tag($name,$keys,$overlay);
    }else{
        $result = false;
    }
    return $result;
}

/**********end memcached**********/


/**********start workman**********/
function send_message($uid,$message){
    // 建立socket连接到内部推送端口
    $client = stream_socket_client('tcp://127.0.0.1:5678', $errno, $errmsg, 1);
// 推送的数据，包含uid字段，表示是给这个uid推送
    $data = array('uid'=>$uid, 'message'=>$message);
// 发送数据，注意5678端口是Text协议的端口，Text协议需要在数据末尾加上换行符
    fwrite($client, json_encode($data)."\n");
// 读取推送结果
    echo fread($client, 8192);
}
/**********end workman**********/