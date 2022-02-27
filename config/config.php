<?php

return [
    'model' => 'delay',//模式
    'delay' => 0,
    'attempts' => 0,
    'max_attempts' => 100,
    'driver' => 'redis',
    'delay_periods' => [30, 60, 180, 1800, 1800, 3600, 7200],
    /*
    |--------------------------------------------------------------------------
    | 队列驱动方式
    |--------------------------------------------------------------------------
    |
    | Drivers:  "database", "redis", "rabbitmq"
    |
    */
    'connections' => [
        'database' => [
            'driver' => 'database',
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
