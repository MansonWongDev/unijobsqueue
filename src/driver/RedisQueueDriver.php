<?php
namespace Mansonwong\UniJobsQueue\driver;

class RedisQueueDriver implements QueueDriverFactory
{
    const QUEUE_PREFIX = 'jobs_queue:';
    const DELAY_QUEUE_PREFIX = 'jobs_delay_queue:';
    const DELAY_QUEUE_TEMP_PREFIX = 'jobs_delay_queue_temp:';

    protected static $options = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
    ];

    public function __construct($options = [])
    {
        if (!empty($options)) {
            self::$options = array_merge(self::$options, $options);
        }
    }

    public static $redis = null;
    protected static $redisServer = null;

    /**
     * Notes: 引用外部redis
     * @param $server
     * Author: Wangwenjun <260945307@qq.com>
     * Time: 2022/2/26
     */
    public static function setBackend($server)
    {
        self::$redisServer   = $server;
        self::$redis         = null;
    }

    public static function redis()
    {
        if (self::$redis !== null) {
            return self::$redis;
        }
        if (self::$redisServer){
            //如果有指定redis
            self::$redis = self::$redisServer;
            return self::$redis;
        }
        self::$redis = new \Redis();
        if (self::$options['persistent']) {
            self::$redis->pconnect(self::$options['host'], self::$options['port'], self::$options['timeout'], 'persistent_id_' . self::$options['select']);
        } else {
            self::$redis->connect(self::$options['host'], self::$options['port'], self::$options['timeout']);
        }
        if ('' != self::$options['password']) {
            self::$redis->auth(self::$options['password']);
        }
        if (0 != self::$options['select']) {
            self::$redis->select(self::$options['select']);
        }
        return self::$redis;
    }

    public function popDelay($queue)
    {
        $key = self::DELAY_QUEUE_PREFIX.$queue;
        $data = self::zRangeByScore($key);

        if (empty($data)) {
            return false;
        }
        //每次只取一条任务
        $item = $data[0];
        //立刻移除有序队列集中的一个成员，防止并发被多人抢到
        if (self::zRem($key,$item)) {
            $item = json_decode($item, true);
            //返回数据
            return $item;
        }
        return false;
    }

    public function push($queue, $item)
    {
        if (!isset($item['id'])){
            $item['id'] = self::generateJobId(); //这里跟那里一样
        }
        $now = time();
        $item['reserved_at'] = $now; //预约时间
        $encodedItem = json_encode($item);
        if ($encodedItem === false) {
            return false;
        }

        $length = self::redis()->rpush(self::QUEUE_PREFIX. $queue, $encodedItem);
        if ($length < 1) {
            return false;
        }
        return true;

    }

    public function pushDelay($queue, $item,$delay)
    {
        if (!isset($item['id'])){
            $item['id'] = self::generateJobId(); //这里跟那里一样
        }
        $now = time();
        $available_time = $now+$delay;
        $item['reserved_at'] = $now; //预约时间
        $item['available_at'] = $available_time; //计划运行时间
        $encodedItem = json_encode($item,JSON_UNESCAPED_UNICODE);
        if ($encodedItem === false) {
            return false;
        }
        $res =  self::redis()->zAdd(
            self::DELAY_QUEUE_PREFIX.$queue,
            $available_time,//以时间作为score，队列按时间从小到大排序
            $encodedItem
        );
        if ($res){
            return $item;
        }
        return false;
    }
    public static function zRangeByScore($key)
    {
        //以0和当前时间为区间，返回一条记录
        return self::redis()->zRangeByScore($key, 0, time(), array('limit' => array(0, 1)));
    }
    public static function zRem($key,$value)
    {
        return self::redis()->zRem($key, $value);
    }

    public function pop($queue)
    {
        $item = self::redis()->lpop(self::QUEUE_PREFIX.$queue);
        if(!$item) {
            return false;
        }
        return json_decode($item, true);
    }

    public function size($queue)
    {
        return self::redis()->llen(self::QUEUE_PREFIX.$queue);
    }

    public function sizeDelay($queue)
    {
        return self::zRangeByScore(self::DELAY_QUEUE_PREFIX.$queue);
    }

    public static function generateJobId()
    {
        return md5(uniqid('', true));
    }
}
