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


//       return UniJobsQueue::getInstance()->dispatch('demo',['hello'=>'我是普通队列'])->attempts(10)->push();
return UniJobsQueue::getInstance()
    ->model(UniJobsQueue::MODEL_DELAY)//设置为延时模式，不提供该参数将默认非延时模式
    ->dispatch('demo',['hello'=>'我是延时队列'])//队列名 和 数据
    ->delay(10)//首次延时
    ->attempts(10)//最大重试次数
    ->periods([10,30,60,180,1800,1800,3600,7200])//第二次之后的延时周期，如果不满足最大重试次数以数组最后一个元素为准
    ->push();//执行推送消息
