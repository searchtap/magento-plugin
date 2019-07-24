<?php

namespace Bitqit\Searchtap\Controller\Indexer;

class Queue extends \Magento\Framework\App\Action\Action
{
    private $queueFactory;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->queueFactory = $queueFactory;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = $this->getRequest()->getParam('count', 10);
        $page = $this->getRequest()->getParam('page', 1);
        $type = $this->getRequest()->getParam('type');
        $action = $this->getRequest()->getParam('action');
        $storeId = $this->getRequest()->getParam('store');

        $response = $this->queueFactory->create()->getQueueData($count, $page, $type, $action, $storeId);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}