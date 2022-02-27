<?php

/*
 * This file is part of the UniJobsQueue.
 *
 * (c) Mansonwong <260945307@qq.com>
 *
 * This source file is subject to the Apache License that is bundled.
 */

require __DIR__.'/../vendor/autoload.php';

use Mansonwong\UniJobsQueue\UniJobsQueue;

UniJobsQueue::getInstance()->model(UniJobsQueue::MODEL_DELAY)->subscribe('demo',function ($rawData){
    // TODO: 执行成功请返回return true，执行失败返回return false,否则会认为job任务执行失败，队列回滚，用try catch
    $res = true;//假设任务执行成功true，失败为false
    if ($rawData){
        try {
            print_r($rawData);
            if (!$res){
                throw new \Exception('模拟执行失败！');
            }
            return true;
        } catch (\Exception $e) {
            print $e->getMessage();
            return false;
        }
    }
});
