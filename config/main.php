<?php
$db=require_once (Yii::getAlias('@data/config/db.php'));

return [
    'id' => 'myconsole',
    'language' => 'zh-CN',
    'basePath' => dirname(__DIR__),
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'controllerNamespace' => 'myswoole\controllers',
    'bootstrap' => ['log'],
    'components' => [
//        'user' => [
//            'identityClass' => 'source\models\User',
//            'enableAutoLogin' => false,
//            'loginUrl' => null,
//        ],

        'db' =>$db,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','info'],
                    'logFile' => '@myswoole/runtime/error.log',
                ],
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['info'],
                    'categories' => ['access'],
                    'logFile' => '@myswoole/runtime/access.log',
                    'maxLogFiles' => 1000,
                ]
            ],
        ],
    ],
];
