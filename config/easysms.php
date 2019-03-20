<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/26
 * Time: 22:05
 */

return [

    //http请求的超时时间
    'timeout' => 5.0,

    //默认发送配置
    'default' => [
        //网关调用策咯，默认：顺序调用
        'strategy' => \Overtrue\EasySms\Strategies\OrderStrategy::class,

        //默认可用的发送网关
        'gateways' => [
            'yunpian',
        ],
    ],

    //可用的网关配置
    'gateways' => [
        'errorlog' => [
            'file' => '/tmp/easy-sms.log',
        ],
        'yunpian' => [
            'api_key' => env('YUNPIAN_API_KEY'),
        ],
    ],
];