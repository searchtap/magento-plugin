<?php

namespace Bitqit\Searchtap\Helper;

class Logger
{
    private $logFileName = "/var/log/searchtap.log";
    private $logger;

    public function __construct()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . $this->logFileName);
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
    }

    public function add($msg)
    {
        $this->logger->info($msg);
    }

    public function error($error)
    {
        $this->logger->err($error);
    }
}