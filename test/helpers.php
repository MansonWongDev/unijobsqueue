<?php

/*
 * demo helper UniJobsQueue.
 *
 * (c) Mansonwong <260945307@qq.com>
 *
 * This source file is subject to the Apache License that is bundled.
 */

require __DIR__.'/../vendor/autoload.php';

use Mansonwong\UniJobsQueue\UniJobsQueue;

if (! function_exists('jobs_queue')) {

    /**
     * Notes:获取实例类（可在任意框架自行实现读取初始化config）
     * @return UniJobsQueue
     */
    function jobs_queue()
    {
        $config = [];//your config
        return UniJobsQueue::getInstance($config);
    }
}
