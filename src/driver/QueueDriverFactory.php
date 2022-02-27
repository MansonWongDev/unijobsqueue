<?php
namespace Mansonwong\UniJobsQueue\driver;

interface QueueDriverFactory
{

    public function push($queue,$item);
    public function pushDelay($queue,$item,$delay);
    public function pop($queue);
    public function popDelay($queue);
    public function size($queue);
    public function sizeDelay($queue);

}

