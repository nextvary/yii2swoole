<?php
/**
 * Created by renfei.
 * User: vary
 * Date: 2018/1/16
 * Time: 10:10
 */
namespace myswoole;
use Yii;
require_once ('./SwooleYiiApplication.php');

class SwooleServer
{
    private $serv;
    private $swooleapp;
    public function __construct() {
        $this->serv = new \swoole_server("127.0.0.1", 9501);
        $this->serv->set(array(
            'worker_num' => 1,
            'task_worker_num' => 1,
            'daemonize' => 1,
            'max_request' => 1000,
            'dispatch_mode' => 2,
            'debug_mode'=> 1,
            'user' => 'v114',
            'group' => 'v114',
            'heartbeat_check_interval' => 5, //每5秒侦测一次心跳，一个TCP连接如果在10秒内未向服务器端发送数据，将会被切断
//            'heartbeat_idle_time' => 10,
            'log_file' => __DIR__."/runtime/swoole.log",
        ));
        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        // bind callback

        //任务
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('Task',array($this,'onTask'));
        $this->serv->on('Finish',array($this,'onFinish'));

        $this->app = (new SwooleYiiApplication())->app;

        $this->serv->start();
    }
    public static function Log($info='',$file=''){
        $file=!empty($file)?$file:\Yii::getAlias('@myswoole/runtime/console.log');
        if (!file_exists($file)){
            file_put_contents($file,"\r\n");
        }
        $request=json_encode(\Yii::$app->request->params??[],JSON_UNESCAPED_SLASHES);
        $rh=fopen($file,'a+');
        fwrite($rh,'TIME: '.date('Y-m-d H:i:s',time()) ." INFO : ".$info." ACTION: ".$request. "\r\n" );
        fclose($rh);
    }
    public function onStart( $serv ) {
        swoole_set_process_name("swoole Master");
        echo "Server Start\n";
    }
    public function onWorkerStart( $serv , $worker_id) {
        swoole_set_process_name("swoole Child");
        // 在Worker进程开启时绑定定时器
        // 只有当worker_id为0时才添加定时器,避免重复添加
//        if( $worker_id == 0 ) {
//            //1.7.19版本
//            //$serv->addtimer(1000);
//            //1.8.2版本
//            swoole_timer_tick(1000, function(){
//                SwooleServer::$timerCounter += 1;
//                $this->app->runAction('swoole-tick',[ 1000, SwooleServer::$timerCounter]);
//            });
//        }
    }

    public function onConnect( $serv, $fd, $from_id ) {
        echo json_encode($serv,320)."Client  {$fd} connect from_id:{$from_id}\n";
        echo "共有链接:". count($serv->connections)."个 \n";
    }

    /**
     * @param \swoole_server $serv
     * @param $fd
     * @param $from_id
     * @param $data string
     */
    public function onReceive(\swoole_server $serv, $fd, $from_id, $data ) {
        echo "Get Message From Client {$fd} from_id {$from_id}:{$data}\n";
//        $str = json_encode( $param );
//        $serv->after( 1000 , array($this, 'onAfter') , $str );
        echo "当前服务器共有 ".count($serv->connections). " 个连接\n";

        $arrdata=json_decode($data,true);
        if (isset($arrdata['controller'])){
//            $serv->task($data);
            $this->app->runAction("$arrdata[controller]/$arrdata[action]",[$data]);
//            $serv->after(2000,$serv->send($fd, json_encode(['来来来,给你'],320)) );
        }else{
            self::Log($data);
        }


    }
    public function onClose( $serv, $fd, $from_id ) {
        echo "Client {$fd} form_id:{$from_id} close connection\n";
        $serv->after(1000,function () use($serv){
            echo "共有链接:". count($serv->connections)."个 \n";
        });

    }
    public function onAfter( $data ) {
        $param = json_decode( $data, true );
        for ($i=0;$i<10;$i++){
            sleep(1);
            echo '执行'.$i."\n";
            $this->serv->send( $param['fd'] ,"输入的是:". $param['msg']."-------执行第 {$i} 次");
        }
    }

    //任务 只能执行同步
    public function onTask($serv,$task_id,$from_id,$data){
        echo "执行任务 task_id :$task_id from_id :$from_id data:$data";
        $data=json_decode($data,true);
        $this->app->runAction("$data[controller]/$data[action]",[ $data ]);
    }

    //任务结束
    public function onFinish($serv,$task_id,$data){
        echo "Task {$task_id} Finish!!\n";
        echo "Result: {$data}\n ";

    }

    public function clearFd(){
        $data=$this->serv->heartbeat(false);
        echo "检测链接状态:".json_encode($data,320);
    }
    public function getAllClient(){
        foreach($this->serv->connections as $thisfd)
        {
            echo "client fd:{$thisfd} \n";
//            $serv->send($thisfd, "hello $thisfd");
//            $serv->close($thisfd,true);
        }
        echo "共有链接:". count($this->serv->connections)."个 \n";

    }

}
new SwooleServer();
