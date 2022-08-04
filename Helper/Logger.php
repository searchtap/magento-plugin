<?php

namespace Bitqit\Searchtap\Helper;

class Logger
{
    private $logFileName = "/var/log/searchtap.log";
    private $logger;

    public function __construct()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
        $version = $productMetadata->getVersion();

        if ($version < 2.4) {
            $writer = new \Zend\Log\Writer\Stream(BP . $this->logFileName);
            $this->logger = new \Zend\Log\Logger();
        }
        else {
            $writer = new \Zend_Log_Writer_Stream(BP . $this->logFileName);
            $this->logger = new \Zend_Log();
        }
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