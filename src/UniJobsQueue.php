<?php
/**
 *
 * 联合消息队列 UniJobsQueue 主逻辑
 * Created On 2022/2
 * Created By WangWenJun <260945307@qq.com>
 */

namespace Mansonwong\UniJobsQueue;

use Mansonwong\UniJobsQueue\driver\MysqlQueueDriver;
use Mansonwong\UniJobsQueue\driver\RabbitMqQueueDriver;
use  Mansonwong\UniJobsQueue\driver\RedisQueueDriver;

class UniJobsQueue implements UniJobsQueueInterface
{
    /**
     * 驱动实例
     * @var
     */
    protected static $driver;
    /**
     * 模式
     * @var
     */
    protected $model;
    /**
     * 延时模式
     */
    const MODEL_DELAY = 'delay';
    /**
     * 队列模式/可用于抢购
     */
    const MODEL_LIST = 'list';

    /**
     * 初始默认模式
     * @var
     */
    protected static $default_model = 'list';

    /**
     * 配置
     * @var null
     */
    protected static $config = null;
    /**
     * 驱动实体
     * @var
     */
    protected static $driverInstance;
    /**
     * 延时周期
     * @var array
     */
    protected $delay_periods = [];
    /**
     * 首次延时时间
     * @var int
     */
    protected $delay = 0;
    /**
     * 最大重试次数
     * @var int
     */
    protected $max_attempts = 10;
    /**
     * 队列名
     * @var null
     */
    protected $queue = null;
    /**
     * 组装的队列数据
     * @var array
     */
    protected $item = [];
    /**
     *  方法
     * @var null
     */
    protected $method = null;
    /**
     * 结果集
     * @var null
     */
    protected $result = null;
    /**
     * 当前类实例化
     * @var
     */
    protected static $instance;
    /**
     * 用于检测是否 设置模式
     * @var bool
     */
    protected $is_set_model = false;


    public function __construct($config = [])
    {
        $this->initConfig($config);
    }


    /**
     * @return mixed
     */
    private function getDriver()
    {
        return $this->driver;
    }

    /**
     * @param mixed $driver
     */
    private function setDriver($driver)
    {
        $this->driver = $driver;
    }

    /**
     * @return mixed
     */
    private function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    private function setModel($model)
    {
        $this->model = $model;
    }

    /**
     * @return array
     */
    private function getDelayPeriods()
    {
        return $this->delay_periods;
    }

    /**
     * @param array $delay_periods
     */
    private function setDelayPeriods($delay_periods)
    {
        $this->delay_periods = $delay_periods;
    }

    /**
     * @return int
     */
    private function getDelay()
    {
        return $this->delay;
    }

    /**
     * @param int $delay
     */
    private function setDelay($delay)
    {
        $this->delay = $delay;
    }

    /**
     * @return int
     */
    private function getMaxAttempts()
    {
        return $this->max_attempts;
    }

    /**
     * @param int $max_attempts
     */
    private function setMaxAttempts($max_attempts)
    {
        $this->max_attempts = $max_attempts;
    }

    /**
     * @return null
     */
    private function getQueue()
    {
        return $this->queue;
    }

    /**
     * @param null $queue
     */
    private function setQueue($queue)
    {
        $this->queue = $queue;
    }

    /**
     * @return array
     */
    private function getItem()
    {
        return $this->item;
    }

    /**
     * @param array $item
     */
    private function setItem($item)
    {
        $this->item = $item;
    }


    public static function getInstance($config=[])
    {
        if (self::$instance) {
            return self::$instance;
        }
        return new self($config);
    }

    /**
     * @return null
     */
    private function getMethod()
    {
        return $this->method;
    }

    /**
     * @param null $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }


    /**
     * 设置队列参数
     * @param $queue
     * @param $payload
     * @return $this|mixed
     */
    public function dispatch($queue, $payload)
    {
        $this->setMethod('doDispatch');
        $this->setQueue($queue);
        $this->setItem(array(
            'queue' => $queue,
            'model' => self::$default_model,//默认为rpush rpop
            'attempts' => 0,
            'max_attempts' => $this->max_attempts,
            'payload' => $payload,
        ));
        return $this;
    }

    public function getCurrentDelayPeriod($delay_periods,$num)
    {
        if (isset($delay_periods[$num])){
            return $delay_periods[$num];
        }
        return end($delay_periods);
    }

    public function doDispatch()
    {
        if ($this->model == self::MODEL_DELAY) {
            $result = self::driverInstance()->pushDelay(self::getQueue(), self::getItem(), self::getDelay());
        } else {
            $result = self::driverInstance()->push(self::getQueue(), self::getItem());
        }
        $this->setResult($result);
        return $result;
    }

    public function doSubscribe()
    {
        if ($this->model == self::MODEL_DELAY) {
            //延迟模式
            $result = self::driverInstance()->popDelay($this->getQueue());
        } else {
            $result = self::driverInstance()->pop($this->getQueue());
        }
        return $result;
    }

    public function model($model)
    {
        $this->is_set_model = true;
        //延时模式
        $this->model = $model;
        return $this;
    }

    public function delay($delay = 0)
    {
        $this->setDelay($delay);
        $this->item['model'] = 'delay';
        $this->item['delay_periods'] = self::$config['delay_periods'];
        //延时模式
        $this->model = self::MODEL_DELAY;
        return $this;
    }

    public function periods($delay_periods)
    {
        $this->setDelayPeriods($delay_periods);
        return $this;
    }

    public function attempts(int $max_attempts)
    {
        $this->setMaxAttempts($max_attempts);
        $this->item['max_attempts'] = $max_attempts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function push()
    {
        $fun = $this->getMethod();
        return $this->$fun();
    }


    /**
     * @param null $result
     */
    protected function setResult($result)
    {
        $this->result = $result;
    }


    private function callFunc()
    {
        $fun = $this->getMethod();
        return $this->$fun();
    }


    /**
     * 订阅/接收队列消息
     * @param $queue
     * @return $this|mixed
     */
    public function subscribe($queue, $callfunc)
    {
        if ($this->is_set_model == false) {
            echo '未设置model';
            die;
        }
        $this->setQueue($queue);
        $this->setMethod('doSubscribe');
        if ($this->model == self::MODEL_DELAY) {
            //延迟模式
            $rawData = self::driverInstance()->popDelay($queue);
        } else {

            $rawData = self::driverInstance()->pop($queue);
        }
        $jobres = call_user_func($callfunc, $rawData);
        if (!$jobres) {
            //如果回调函数返回false,表示job执行失败或者没有队列
            if ($rawData) {
                //如果失败回滚
                $roll = $this->reDispatch($rawData);
                echo 'job返回 false ,已回滚,回滚状态：' . json_encode($roll);
            }
        } else {
            echo '成功了！！！';
        }

        return $this;

    }


    /**
     * 重新进入对列
     * @param $queue
     * @param $item
     * @return mixed
     */
    public function reDispatch($item)
    {

        $this->model = $item['model'];
        echo '模式为：' . $this->model;
        $queue = $item['queue'];
        $item['attempts']++;
        if ($item['attempts'] <= $item['max_attempts']) {
        }else{
            //推入失败队列 redis
            $queue = self::$config['failed']['table'] . ':' . $queue;
        }
        if ($this->model == self::MODEL_DELAY) {
            //延迟模式
            $reItem = array(
                'id' => $item['id'],
                'model' => $item['model'],
                'queue' => $item['queue'],
                'attempts' => $item['attempts'],
                'max_attempts' => $item['max_attempts'],
                'delay_periods' => $item['delay_periods'],
                'payload' => $item['payload'],
            );
            $delay = $this->getCurrentDelayPeriod($item['delay_periods'],$item['attempts']);
            return self::driverInstance()->pushDelay($queue, $reItem, $delay);
        } else {
            $reItem = array(
                'id' => $item['id'],
                'model' => $item['model'],
                'queue' => $item['queue'],
                'attempts' => $item['attempts'],
                'max_attempts' => $item['max_attempts'],
                'payload' => $item['payload'],
            );
            return self::driverInstance()->push($queue, $reItem);
        }
    }

    /**
     * 获取消息长度
     * @param $queue
     * @return mixed
     */
    public function size($queue)
    {
        if ($this->model == self::MODEL_DELAY) {
            //延迟模式
            return self::driverInstance()->sizeDelay($queue);
        } else {
            return self::driverInstance()->size($queue);
        }
    }

    /**
     * Notes:初始化配置
     * @param $userConfig
     * @return array|mixed|null
     * @throws \Exception
     * Author: Wangwenjun <260945307@qq.com>
     */
    private function initConfig($userConfig)
    {
        if (!self::$config) {
            $defaultConfig = $this->getConfigByFile();
            if ($userConfig) {
                self::$config = array_merge($defaultConfig, $userConfig);
            } else {
                self::$config = $defaultConfig;
            }
        }
        if (isset(self::$config['model'])){
            $this->model(self::$default_model);
            self::$default_model = self::$config['model'];//初始默认模式
        }
        return self::$config;
    }

    public static function driverInstance()
    {
        if (self::$driverInstance !== null) {
            return self::$driverInstance;
        }
        self::$driver = self::$config['driver'];
        switch (self::$driver) {
            case 'redis':
                self::$driverInstance = new RedisQueueDriver(self::$config['connections']['redis']);
                break;
            case 'mysql':
                self::$driverInstance = new MysqlQueueDriver(self::$config['connections']['mysql']);
                break;
            case 'rabbitmq':
                self::$driverInstance = new RabbitMqQueueDriver(self::$config['connections']['rabbitmq']);
                break;
            default:
                exit('driver error');
                break;
        }
        return self::$driverInstance;
    }

    private function getConfigByFile()
    {
        $config = include  __DIR__.'/../config/config.php';
        if (!$config) {
            throw new \Exception('config error');
        }
        return $config;
    }


}
