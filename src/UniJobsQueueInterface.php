<?php
namespace Mansonwong\Unijobsqueue;

interface UniJobsQueueInterface
{



    /**
     *  发送消息到队列
     * @param $channel
     * @param $item
     * @return mixed
     */
    public function dispatch($channel,$item);

    /**
     * 接收队列消息
     * @param $channel
     * @param $call_func
     * @return mixed
     */
    public function subscribe($channel,$call_func);

    /**
     * 获取消息长度
     * @param $channel
     * @return mixed
     */
    public function size($channel);

    /**
     * 重新进入对列
     * @param $item
     * @return mixed
     */
    public function reDispatch($item);



}
