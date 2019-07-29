<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019-07-06
 * Time: 9:51
 */
namespace app\workman\controller;

class Server extends \think\worker\Server {
    protected $socket = '';
    protected $host = '';
    protected $uidConnections = [];

    public function __construct() {
        $this->socket = 'websocket://'.config('worker.host').':2346';
        $this->host = config('worker.host');
        parent::__construct();
    }

    // 向所有验证的用户推送数据
    function broadcast($message){
        foreach($this->uidConnections as $uidConnection){
            foreach($uidConnection as $connection){
                $connection->send($message);
            }
        }
    }

    // 针对uid推送数据
    function sendMessageByUid($uid, $message){
        if(isset($this->uidConnections[$uid])){
            $connections = $this->uidConnections[$uid];
            foreach($connections as $connection){
                $connection->send($message);
            }
            return true;
        }
        return false;
    }

    /**
     * 收到信息
     * @param $connection
     * @param $data
     */
    public function onMessage($connection, $data){
        $data = json_decode($data,true);
        //$connection->send('receive:'.json_encode($data));

        // 判断当前客户端是否已经验证,既是否设置了uid
        if(!empty($data) && 'init' == $data['type'] && !empty($data['user_id']) && !isset($connection->uid)){
            // 没验证的话把第一个包当做uid（这里为了方便演示，没做真正的验证）
            $connection->uid = $data['user_id'];
            //保存uid到connection的映射，这样可以方便的通过uid查找connection，
            //实现针对特定uid推送数据
            $this->uidConnections[$connection->uid][] = $connection;
            $connection->index = count($this->uidConnections[$connection->uid])-1;
            //dump($connection->index);
            //dump(array_keys($this->uidConnections[$connection->uid]));
        }
        //$connection->send('receive:'.json_encode($data));
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection){
        echo "onConnect\n";
    }

    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    public function onClose($connection){
        if(isset($connection->uid)){
            // 连接断开时删除映射
            //dump($connection->index);
            //dump(array_keys($this->uidConnections[$connection->uid]));
            unset($this->uidConnections[$connection->uid][$connection->index]);
            $this->uidConnections[$connection->uid] = array_values($this->uidConnections[$connection->uid]);
            foreach ($this->uidConnections[$connection->uid] as $key => $value) {
                $this->uidConnections[$connection->uid][$key]->index = $key;
            }
            //dump(array_keys($this->uidConnections[$connection->uid]));
        }
        echo "onClose\n";
    }

    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    public function onError($connection, $code, $msg){
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    public function onWorkerStart($worker){
        // 开启一个内部端口，方便内部系统推送数据，Text协议格式 文本+换行符
        $inner_text_worker = new \Workerman\Worker('Text://0.0.0.0:5678');
        $inner_text_worker->onMessage = function($connection, $buffer){
            global $worker;
            // $data数组格式，里面有uid，表示向那个uid的页面推送数据
            $data = json_decode($buffer, true);
            $uid = $data['uid'];
            // 通过workerman，向uid的页面推送数据
            $ret = $this->sendMessageByUid($uid, json_encode(array_merge($data['data'],['message_type'=>$data['message_type'],'handle_type'=>$data['handle_type']])));
            // 返回推送结果
            $connection->send($ret ? 'success' : 'fail');
        };
        $inner_text_worker->listen();
        echo "onWorkerStart\n";
    }
}