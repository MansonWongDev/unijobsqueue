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

/**
 * //你的配置文件，参考 config/uniqueue.php，
 * 你可以简单做一个封装用在您的框架里面去，
 * 比如在laravel框架 可以使用服务提供者，助手函数、门面模式、Helpers等方式做一个简单初始化配置封装
 * 或者不需要封装直接在你的业务类中初始化
 */
$config = [];


/****************** 非延时模式demo ******************/
$res1 = UniJobsQueue::getInstance($config)->model(UniJobsQueue::MODEL_LIST)->subscribe('demo',function ($rawData){
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

//判断是否有错误信息
if ($res1->getError()){
    echo '队列事务执行有错误';
    var_dump($res1->getError());
}



/****************** 延时模式demo ******************/
$res2 = UniJobsQueue::getInstance($config)->model(UniJobsQueue::MODEL_DELAY)->subscribe('demo',function ($rawData){

    // TODO:  执行成功请返回return true，执行失败返回return false,否则会认为job任务执行失败，队列回滚，用try catch
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

//判断是否有错误信息
if ($res2->getError()){
    echo '队列事务执行有错误';
    var_dump($res1->getError());
}