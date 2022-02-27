<?php

return [
    'model' => 'list',//默认模式，非延时模式：list、 延时模式：delay
    'delay' => 0,//初始默认首次延时
    'max_attempts' => 100,//最大失败重试次数
    'driver' => 'redis',//驱动模式
    'delay_periods' => [30, 60, 180, 1800, 1800, 3600, 7200],
    /*
    |--------------------------------------------------------------------------
    | 队列驱动方式
    |--------------------------------------------------------------------------
    |
    | Drivers:  "mysql", "redis", "rabbitmq"
    |
    */
    'connections' => [
        'database' => [
            'driver' => 'mysql',
            'table' => 'jobs',//表
            'queue' => 'default',
            'retry_after' => 90,
        ],
        'redis' => [
            'driver' => 'redis',
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => '',
            'select' => 1,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => true,
            'prefix' => 'jobs_queue',
        ],
        'rabbitmq' => [
            'host' => '127.0.0.1',
            'vhost' => 'my_vhost',
            'port' => 5672,
            'login' => 'admin',
            'password' => 'admin'
        ],

    ],

    /*
    | 失败的队列
    */
    'failed' => [
        'database' => 'redis',
        'table' => 'failed_jobs',
    ],

];
