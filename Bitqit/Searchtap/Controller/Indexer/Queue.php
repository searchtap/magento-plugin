<?php

namespace Bitqit\Searchtap\Controller\Indexer;

class Queue extends \Magento\Framework\App\Action\Action
{
    private $dataHelper;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Data $dataHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->dataHelper = $dataHelper;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $count = $this->getRequest()->getParam('count', 100);
        $page = $this->getRequest()->getParam('page', 1);
        $type = $this->getRequest()->getParam('type');
        $action = $this->getRequest()->getParam('action');
        $storeId = $this->getRequest()->getParam('store');
        $token = $this->getRequest()->getParam('token');

        $response = $this->dataHelper->getQueueData($token, $count, $page, $type, $action, $storeId);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}