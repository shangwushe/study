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

/**
 * @Notes:
 * @Function get_config
 * @param $type
 * @param null $value
 * @return bool|mixed|string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 17:47
 */
function get_config($type,$key=null){
    $dictionary = [
        //操作状态编码
        'code'=>[0=>'操作成功',11=>'操作失败',12=>'系统错误',13=>'请求URL不存在',14=>'用户未登录',15=>'用户无权限',16=>'重放攻击',21=>'参数错误',22=>'参数无效',31=>'数据错误',999=>'自定义错误'],

    ];
    return isset($key)?$dictionary[$type][$key]??'':$dictionary[$type]??false;
}

/**
 * @Notes:
 * @Function create_result_data
 * 处理返回结果
 * @param int $code
 * @param array $data
 * @param null $msg
 * @param bool $isJson
 * @return array|string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 17:52
 */
function create_result_data($code=0,$data=[],$msg=null,$isJson=false) {
    $result = ['status'=>$code,'msg'=>$msg??get_code($code),'data'=>$data];
    return $isJson?json_encode($result):$result;
}

/**
 * @Notes:
 * @Function get_code
 * 获取状态码对应的信息
 * @param $code
 * @return bool|mixed|string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 17:52
 */
function get_code($code){
    return get_config('code',$code);
}

/**
 * @Notes:
 * @Function curl
 * @param $url
 * @param $params
 * @param string $method
 * @param array $auth
 * @param array $header
 * @param bool $isJson
 * @param int $timeout
 * @return mixed
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 17:59
 */
function curl($url,$params,$method='GET',$auth=[],$header=["Content-Type:application/json;charset=utf-8"],$isJson=true,$timeout=30){
    //初始化CURL句柄
    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, $url);//设置请求的URL
    #curl_setopt($curl, CURLOPT_HEADER, false);// 不要http header 加快效率
    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出

    //SSL验证
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);    // https请求时要设置为false 不验证证书和hosts  FALSE 禁止 cURL 验证对等证书（peer's certificate）, 自cURL 7.10开始默认为 TRUE。从 cURL 7.10开始默认绑定安装。
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);//检查服务器SSL证书中是否存在一个公用名(common name)。

    //$header[] = "Content-Type:application/json;charset=utf-8";
    if(!empty($header)){
        curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header );//设置 HTTP 头字段的数组。格式： ['Content-type: text/plain', 'Content-length: 100']
    }

    //请求时间
    curl_setopt ($curl, CURLOPT_CONNECTTIMEOUT, $timeout);//设置连接等待时间

    //不同请求方法的数据提交
    switch ($method){
        case "GET" :
            curl_setopt($curl, CURLOPT_HTTPGET, true);//TRUE 时会设置 HTTP 的 method 为 GET，由于默认是 GET，所以只有 method 被修改时才需要这个选项。
            break;
        case "POST":
            if(is_array($params)){
                if ($isJson) {
                    $params = json_encode($params,320);
                }else {
                    $params = http_build_query($params);
                }
            }
            #curl_setopt($curl, CURLOPT_POST,true);//TRUE 时会发送 POST 请求，类型为：application/x-www-form-urlencoded，是 HTML 表单提交时最常见的一种。
            #curl_setopt($curl, CURLOPT_NOBODY, true);//TRUE 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 FALSE 时不会变成 GET。
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");//HTTP 请求时，使用自定义的 Method 来代替"GET"或"HEAD"。对 "DELETE" 或者其他更隐蔽的 HTTP 请求有用。 有效值如 "GET"，"POST"，"CONNECT"等等；
            //设置提交的信息
            curl_setopt($curl, CURLOPT_POSTFIELDS,$params);//全部数据使用HTTP协议中的 "POST" 操作来发送。
            break;
        case "PUT" :
            curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($curl, CURLOPT_POSTFIELDS,json_encode($params,320));
            break;
        case "DELETE":
            curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($curl, CURLOPT_POSTFIELDS,$params);
            break;
    }

    //传递一个连接中需要的用户名和密码，格式为："[username]:[password]"。
    if (!empty($auth) && isset($auth['username']) && isset($auth['password'])) {
        curl_setopt($curl, CURLOPT_USERPWD, "{$auth['username']}:{$auth['password']}");
    }

    $data = curl_exec($curl);//执行预定义的CURL
    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);//获取http返回值,最后一个收到的HTTP代码
    curl_close($curl);//关闭cURL会话
    $res = $data;
    if ($isJson) {
        $res = json_decode($data,true);
    }

    return $res;
}

/**
 * @Notes:
 * @Function wlog
 * 输出日志
 * @param $str
 * @param string $fileName
 * @return bool
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 18:25
 */
function wlog($str,$fileName = ''){
    if (is_array($str)) {
        $str = print_r($str,true);
    }
    //\Think\Log::write($str);
    $path = env('runtime_path').'log/'.date('Ym').'/';
    if (create_dir($path)) {
        try {
            $filePath = $path.($fileName?$fileName:('log_'.date('Y-m-d').'.txt'));
            $fp = fopen($filePath,'a+');
            fwrite($fp, "".date('Y-m-d H:i:s').":\n".$str."\n\n");
            fclose($fp);
        } catch (Exception $e) {
            return false;
        }
    }
}

/**
 * @Notes:
 * @Function create_dir
 * @param string $path
 * @param int $mode
 * @return bool
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 18:33
 */
function create_dir($path='',$mode = 0777) {
    return is_dir($path) || (version_compare(PHP_VERSION,'5.0.0','>=')?mkdir($path,$mode,true):(create_dir(dirname($path)) && mkdir($path, $mode)));
}

/**
 * @Notes:
 * @Function unknow_to_str
 * @param string $unknow
 * @return array|string
 * @Author: gxk
 * @Date: 2020-03-23
 * @Time: 0:39
 */
function unknow_to_str($unknow=''){
    $result = false;//不支持转换类型
    if(is_object_id($unknow)){//MongoDB Object类型
        $result = object_id_to_str($unknow);
    }else if(is_array($unknow)){//数组
        $result = array_to_string($unknow);
    }else if(is_object($unknow)){
        $result = json_encode($unknow,JSON_FORCE_OBJECT);
    }else if(is_base_type($unknow)){
        switch (gettype($unknow)){
            case 'boolean':
            case 'integer':
            case 'double':
            case 'array':
            case 'string':
            case 'NULL':
                $result = (string)$unknow;
                break;
            /*case 'resource':
                $result = false;
                break;*/
        }
    }
    return $result;
}

function is_base_type($check){
    return 'unknown type'==gettype($check)?false:true;
}
/**
 * @Notes:
 * @Function array_to_string
 * @param array $data
 * @return array|string
 * @Author: gxk
 * @Date: 2020-03-23
 * @Time: 0:32
 */
function array_to_string($data = []){
    if (is_array($data)) {
        $result = [];
        foreach ($data as $key => $value) {
            $result[] = (string)$key.':'.(is_array($value)?array_to_string($value):$value);
        }
        $result = implode(',',$result);
    }else {
        $result = (string)$result;
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


/**********start email**********/
/**
 * @Notes:
 * @Function send_mail
 * @param $user_email
 * @param $user_name
 * @param $content
 * @param string $subject
 * @throws \PHPMailer\PHPMailer\Exception
 * @Author: gxk
 * @Date: 2019-11-15
 * @Time: 15:34
 */
function send_mail($user_email,$user_name,$content,$subject=''){
    $config = $subject ? ['Subject'=>$subject]:[];
    $email = new \app\index\controller\Mail();
    $email->sendEmail(['user_email'=>$user_email,'user_name'=>$user_name,'content'=>$content]);
}
/**********end email**********/

/**********start upload big file**********/
function upload_file(){
    $upload = new \app\index\controller\File($_FILES['file']['tmp_name'],$_POST['blob_num'],$_POST['total_blob_num'],$_POST['file_name'],$_POST['md5_file_name']);
    return 0;
}
/**********end upload big file**********/

/**********start face recognition**********/
/**
 * @Notes:
 * @Function get_access_token
 * @param bool $refresh
 * @return array|mixed|string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 18:49
 */
function get_access_token($refresh=false){
    $orc = new \app\facerecognition\controller\Ocr();
    return $orc->getAccessToken($refresh);
}

/**
 * @Notes:
 * @Function get_nonce_ticket
 * @param $userId
 * @param bool $refresh
 * @return array|mixed|string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 19:20
 */
function get_nonce_ticket($userId,$refresh=false){
    //判断用户的有效性
    $orc = new \app\facerecognition\controller\Ocr();
    return $orc->getNonceTicket($userId,$refresh);
}

/**
 * @Notes:
 * @Function create_nonce_str
 * @param $userId
 * @param bool $refresh
 * @param int $length
 * @return string
 * @Author: gxk
 * @Date: 2020-03-22
 * @Time: 23:55
 */
function create_nonce_str($userId,$refresh=false,$length=32){
    is_string($userId) || $userId = unknow_to_str($userId);//转为字符串
    $redis_key = 'app:nonce_str:'.$userId;
    $nonce_str = redis_get($redis_key);
    if($refresh||!$nonce_str){
        $userId_length = strlen($userId);
        $nonce_str = $userId.padding(rand(1,time()),$length-$userId_length-3).sprintf('%03d',rand(0,999));
    }
    return $nonce_str;
}
/**********end face recognition**********/

/**********start mongodb**********/
/**
 * @Notes:
 * @Function is_object_id
 * @param null $objectId
 * @return bool
 * @Author: gxk
 * @Date: 2020-03-23
 * @Time: 0:25
 */
function is_object_id($objectId=null) {
    return $objectId instanceof \MongoDB\BSON\ObjectId;
}

/**
 * @Notes:
 * @Function is_object_id_str
 * @param string $str
 * @return bool|false|int
 * @Author: gxk
 * @Date: 2020-03-23
 * @Time: 0:25
 */
function is_object_id_str($str='') {
    return empty($str)?false:preg_match('/^[0-9a-fA-F]{24}$/',(string)$str);
}

/**
 * @Notes:
 * @Function object_id_to_str
 * @param null $objectId
 * @return string
 * @Author: gxk
 * @Date: 2020-03-23
 * @Time: 0:25
 */
function object_id_to_str($objectId=null) {
    return (empty($objectId) || !is_object_id($objectId))?(is_string($objectId) && is_object_id_str($objectId)?$objectId:''):$objectId->__toString();
}
/**********end mongodb**********/

/**********start mq*************/
/**
 * @Notes:
 * @Function connect_mq
 * @param array $config
 * @return \PhpAmqpLib\Connection\AMQPStreamConnection
 * @Author gxk
 * @Date 2020-04-23
 * @Time 10:31
 */
function connect_mq($config=[]){
    $default_config = get_system_config('rabbitmq');
    $config = array_merge($default_config,$config);
    $connection = new PhpAmqpLib\Connection\AMQPStreamConnection($config['host'],$config['port'],$config['user'],$config['password'],$config['vhost']);
    return $connection;
}
/**********end mq**************/
