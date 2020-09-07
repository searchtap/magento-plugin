<?php

namespace Bitqit\Searchtap\Controller\Log;

class Index extends \Magento\Framework\App\Action\Action
{

    private $logFileName = "./var/log/searchtap.log";
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
        \Bitqit\Searchtap\Helper\Logger $logger
    )
    {
        $this->productHelper = $productHelper;
        $this->searchtapHelper = $searchtapHelper;
        $this->logger = $logger;

        parent::__construct($context);
    }
    public function execute()
    {
        try {
            $logs = file_get_contents($this->logFileName);
            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[200]);
            $this->getResponse()->setBody($logs);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
