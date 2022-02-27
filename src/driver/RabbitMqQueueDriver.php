<?php


namespace Mansonwong\UniJobsQueue\driver;

class RabbitMqQueueDriver implements QueueDriverFactory
{
    public function __construct($config)
    {
    }
    public function push($queue, $item)
    {
        // TODO: Implement push() method.
    }

    public function pushDelay($queue, $item, $delay)
    {
        // TODO: Implement pushDelay() method.
    }

    public function pop($queue)
    {
        // TODO: Implement pop() method.
    }

    public function popDelay($queue)
    {
        // TODO: Implement popDelay() method.
    }

    public function size($queue)
    {
        // TODO: Implement size() method.
    }

    public function sizeDelay($queue)
    {
        // TODO: Implement sizeDelay() method.
    }
}
