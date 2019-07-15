<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 6/24/19
 * Time: 4:57 PM
 */
namespace app\index\controller;

use think\Controller;
use Workerman\Worker;


class WorkerMessage extends Controller {
    public function index(){
        return $this->fetch();
    }
    public function hello(){
        echo 'hello';
    }

    /**
     * http socket for web
     */
    public function httpWeb(){
        // 创建一个Worker监听2345端口，使用http协议通讯
        $http_worker = new Worker("http://0.0.0.0:2345");

        /***********param name***********/
        //设置当前Worker实例的名称，方便运行status命令时识别进程。不设置时默认为none
        //$this->name = 'MyWebsocketWorker';

        /***********param count***********/
        //设置当前Worker实例启动多少个进程，不设置时默认为1。
        // 启动4个进程对外提供服务
        $http_worker->count = 4;

        /***********param id***********/
        //当前worker进程的id编号，范围为0到$worker->count-1。
        /*
        //windows系统由于不支持进程数count的设置，只有id只有一个0号。windows系统下不支持同一个文件初始化两个Worker监听，所以windows系统这个示例无法运行
        // worker实例1有4个进程，进程id编号将分别为0、1、2、3
        $worker1 = new Worker('tcp://0.0.0.0:8585');
        // 设置启动4个进程
        $worker1->count = 4;
        // 每个进程启动后打印当前进程id编号即 $worker1->id
        $worker1->onWorkerStart = function($worker1)
        {
            echo "worker1->id={$worker1->id}\n";
        };

        // worker实例2有两个进程，进程id编号将分别为0、1
        $worker2 = new Worker('tcp://0.0.0.0:8686');

        // 设置启动2个进程
        $worker2->count = 2;
        // 每个进程启动后打印当前进程id编号即 $worker2->id
        $worker2->onWorkerStart = function($worker2)
        {
            echo "worker2->id={$worker2->id}\n";
        };
        */

        /***********param protocol***********/
        //设置当前Worker实例的协议类
        //$worker->protocol = 'Workerman\\Protocols\\Http';

        /***********param transport***********/
        //设置当前Worker实例所使用的传输层协议，目前只支持3种(tcp、udp、ssl)。不设置默认为tcp。
        //$worker->transport = 'udp';

        /***********param reusePort***********/
        //设置当前worker是否开启监听端口复用(socket的SO_REUSEPORT选项)，默认为false，不开启。
        //如果你的 Linux 内核版本是 3.9 及以上的话，那么在使用 Workerman 时，可以将 reusePort 设置为 true 提升程序运行效率
        //$worker->reusePort = true;

        /***********param connections***********/
        //格式为array(id=>connection, id=>connection, ...)，此属性中存储了当前进程的所有的客户端连接对象，其中id为connection的id编号，详情见手册TcpConnection的id属性。

        /***********param stdoutFile***********/
        //此属性为全局静态属性，如果以守护进程方式(-d启动)运行，则所有向终端的输出(echo var_dump等)都会被重定向到stdoutFile指定的文件中。
        //如果不设置，并且是以守护进程方式运行，则所有终端输出全部重定向到/dev/null。注意：此属性必须在Worker::runAll();运行前设置才有效。windows系统不支持此特性。
        //Worker::$stdoutFile = '/tmp/stdout.log';

        /***********param logFile***********/
        //用来指定workerman日志文件位置。此文件记录了workerman自身相关的日志，包括启动、停止等。如果没有设置，文件名默认为workerman.log，文件位置位于Workerman的上一级目录中。
        //Worker::$logFile = '/tmp/workerman.log';

        /***********param user***********/
        //设置当前Worker实例以哪个用户运行。此属性只有当前用户为root时才能生效。不设置时默认以当前用户运行。建议$user设置权限较低的用户，例如www-data、apache、nobody等。注意：此属性必须在Worker::runAll();运行前设置才有效。windows系统不支持此特性。
        // 设置实例的运行用户
        //$worker->user = 'www-data';

        /***********param reloadable***********/
        //设置当前Worker实例是否可以reload，即收到reload信号后是否退出重启。不设置默认为true，收到reload信号后自动重启进程。
        //有些进程维持着客户端连接，例如Gateway/Worker模型中的gateway进程，当运行reload重新载入业务代码时，却又不想客户端连接断开，则设置gateway进程的reloadable属性为false
        // 设置此实例收到reload信号后是否reload重启
        //$worker->reloadable = false;

        /***********param daemonize***********/
        //此属性为全局静态属性，表示是否以daemon(守护进程)方式运行。如果启动命令使用了 -d参数，则该属性会自动设置为true。也可以代码中手动设置。
        //注意：此属性必须在Worker::runAll();运行前设置才有效。windows系统不支持此特性。
        //Worker::$daemonize = true;

        /***********param globalEvent***********/
        //此属性为全局静态属性，为全局的eventloop实例，可以向其注册文件描述符的读写事件或者信号事件。
        // 当进程收到SIGALRM信号时，打印输出一些信息
        /*Worker::$globalEvent->add(SIGALRM, EventInterface::EV_SIGNAL, function()
        {
            echo "Get signal SIGALRM\n";
        });*/

        /***********param onWorkerStart***********/
        //设置Worker子进程启动时的回调函数，每个子进程启动时都会执行。
        /*$worker->onWorkerStart = function($worker)
        {
            echo "Worker starting...\n";
        };*/

        /***********param onWorkerReload***********/
        //设置Worker收到reload信号后执行的回调。可以利用onWorkerReload回调做很多事情，例如在不需要重启进程的情况下重新加载业务配置文件。

        /***********param onConnect***********/
        //当客户端与Workerman建立连接时(TCP三次握手完成后)触发的回调函数。每个连接只会触发一次onConnect回调。
        //注意：onConnect事件仅仅代表客户端与Workerman完成了TCP三次握手，这时客户端还没有发来任何数据，此时除了通过$connection->getRemoteIp()获得对方ip，没有其他可以鉴别客户端的数据或者信息，所以在onConnect事件里无法确认对方是谁。要想知道对方是谁，需要客户端发送鉴权数据，例如某个token或者用户名密码之类，在onMessage回调里做鉴权。
        /*$worker->onConnect = function($connection)
        {
            echo "new connection from ip " . $connection->getRemoteIp() . "\n";
        };*/

        /***********param onMessage***********/
        //当客户端通过连接发来数据时(Workerman收到数据时)触发的回调函数
        /*$worker->onMessage = function($connection, $data)
        {
            var_dump($data);
            $connection->send('receive success');
        };*/

        /***********param onClose***********/
        //当客户端连接与Workerman断开时触发的回调函数。不管连接是如何断开的，只要断开就会触发onClose。每个连接只会触发一次onClose。
        //注意：如果对端是由于断网或者断电等极端情况断开的连接，这时由于无法及时发送tcp的fin包给workerman，workerman就无法得知连接已经断开，也就无法及时触发onClose。这种情况需要通过应用层心跳来解决。workerman中连接的心跳实现参见这里。如果使用的是GatewayWorker框架，则直接使用GatewayWorker框架的心跳机制即可，参见这里。
        /*$worker->onClose = function($connection)
        {
            echo "connection closed\n";
        };*/

        /***********param onBufferFull***********/
        /*$worker->onBufferFull = function($connection)
        {
            echo "bufferFull and do not send again\n";
        };*/

        /***********param onBufferDrain***********/
        //每个连接都有一个单独的应用层发送缓冲区，缓冲区大小由TcpConnection::$maxSendBufferSize决定，默认值为1MB，可以手动设置更改大小，更改后会对所有连接生效。
        //该回调在应用层发送缓冲区数据全部发送完毕后触发。一般与onBufferFull配合使用，例如在onBufferFull时停止向对端继续send数据，在onBufferDrain恢复写入数据。
        /*$worker->onBufferDrain = function($connection)
        {
            echo "buffer drain and continue send\n";
        };*/

        /***********param onError***********/
        //当客户端的连接上发生错误时触发。
        //目前错误类型有
        //1、调用Connection::send由于客户端连接断开导致的失败（紧接着会触发onClose回调） (code:WORKERMAN_SEND_FAIL msg:client closed)
        //2、在触发onBufferFull后(发送缓冲区已满)，仍然调用Connection::send，并且发送缓冲区仍然是满的状态导致发送失败(不会触发onClose回调)(code:WORKERMAN_SEND_FAIL msg:send buffer full and drop package)
        //3、使用AsyncTcpConnection异步连接失败时(紧接着会触发onClose回调) (code:WORKERMAN_CONNECT_FAIL msg:stream_socket_client返回的错误消息)
        /*$worker->onError = function($connection, $code, $msg)
        {
            echo "error $code $msg\n";
        };*/

        /***********param runAll***********/
        //运行所有Worker实例。
        //Worker::runAll()执行后将永久阻塞，也就是说位于Worker::runAll()后面的代码将不会被执行。所有Worker实例化应该都在Worker::runAll()前进行。
        //注意：windows版本的workerman不支持在同一个文件中实例化多个Worker。 上面的例子无法在windows版本的workerman下运行。windows版本的workerman需要将多个Worker实例初始化放在不同的文件中

        /***********param stopAll***********/
        //停止当前进程（子进程）的所有Worker实例并退出。此方法用于安全退出当前子进程，作用相当于调用exit/die退出当前子进程。与直接调用exit/die区别是，直接调用exit或者die无法触发onWorkerStop回调，并且会导致一条WORKER EXIT UNEXPECTED错误日志。

        /***********param param_name***********/
        /***********param param_name***********/

        // 接收到浏览器发送的数据时回复hello world给浏览器
        $http_worker->onMessage = function($connection, $data)
        {
            // 向浏览器发送hello world
            $connection->send('hello world');
        };


        // 运行worker
        Worker::runAll();
    }
}