<?php
namespace myswoole;
use yii\console\Application;
use yii\helpers\ArrayHelper;

/**
 * Created by renfei.
 * User: vary
 * Date: 2018/1/16
 * Time: 10:11
 */
class SwooleYiiApplication{
    public $app;
    public function __construct()
    {

        require (__DIR__ . '/../vendor/autoload.php');
        require (__DIR__ . '/../vendor/yiisoft/yii2/Yii.php');
        require (__DIR__ . '/../source/override.php');
        require (__DIR__ . '/../data/config/bootstrap.php');



        defined('YII_DEBUG') or define('YII_DEBUG', false);
        defined('YII_ENV') or define('YII_ENV', 'prod');
        defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));
        defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
        $config = ArrayHelper::merge(
            require(__DIR__ . '/config/main.php'),
//            require(__DIR__ . '/config/params.php'),
            require(__DIR__ . '/config/params-local.php')
        );
        $this->app=new Application($config);
    }

}

