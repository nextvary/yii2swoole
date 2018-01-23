<?php

namespace myswoole\controllers;

use Yii;
use yii\console\Controller;

class SiteController extends Controller
{
    public static function Log($info='',$file=''){
        $file=!empty($file)?$file:Yii::getAlias('@myswoole/runtime/console.log');
        if (!file_exists($file)){
            file_put_contents($file,"\r\n");
        }
        $request=json_encode(\Yii::$app->request->params??[],JSON_UNESCAPED_SLASHES);
        $rh=fopen($file,'a+');
        fwrite($rh,'TIME: '.date('Y-m-d H:i:s',time()) ." INFO : ".$info." ACTION: ".$request. "\r\n" );
        fclose($rh);
    }

    /**
     * 接收的是json格式的字符串
     * @param string $params
     */
    public function actionIndex($params=''){
        self::Log($params);
    }

    public function actionSendmail($params=''){

    }
    /**
     * 心跳
     * @param string $params
     */
    public function actionHeart($params=''){
        $client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC); //异步非阻塞
        $client->on("connect", function(\swoole_client $cli){});
        $client->on("receive", function(\swoole_client $cli, $data){});
        $client->on("error", function(\swoole_client $cli){});
        $client->on("close", function(\swoole_client $cli){});
        $client->connect('127.0.0.1', 9501);

        $timer=swoole_timer_tick(1000, function () use ($client) {
            $client->send('beatheart');
        });
        swoole_timer_after(5000,function() use ($client,$timer){
            swoole_timer_clear($timer);
            $client->close();
        });
    }
}