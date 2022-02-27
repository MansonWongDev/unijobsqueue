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
 * $config 配置参数，请参考 config/uniqueue.php，
 * 你可以简单做一个封装用在您的框架里面去，
 * 比如在laravel框架 可以使用服务提供者，助手函数、门面模式等方式做一个简单初始化配置封装
 * 或者不需要封装直接在你的业务类中初始化
 */
$config = [];

/****************** 普通队列模式demo ******************/
UniJobsQueue::getInstance($config)
//    ->model(UniJobsQueue::MODEL_LIST)//不提供 则根据默认config/uniqueue.php 配置文件的 model 参数决定
    ->dispatch('demo',['hello'=>'我是普通队列'])//队列名 和 装载的数据
    ->attempts(10)//最大重试次数
    ->push();//执行推送消息(链式操作的最后)

/****************** 延时模式demo ******************/
UniJobsQueue::getInstance()
    ->model(UniJobsQueue::MODEL_DELAY)//设置为延时模式，不提供该参数将默认非延时模式
    ->dispatch('demo',['hello'=>'我是延时队列'])//队列名 和 数据
    ->delay(10)//首次延时
    ->attempts(10)//最大重试次数
    ->periods([10,30,60,180,1800,1800,3600,7200])//第二次之后的延时周期，如果不满足最大重试次数以数组最后一个元素为准
    ->push();//执行推送消息(链式操作的最后)

/**
 * 温馨提示：关于队列名，普通队列和延时队列模式的队列名同样是 demo ，其实不会冲突，按模式区分
 */