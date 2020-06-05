<?php

namespace Bitqit\Searchtap\Controller\Indexer\Refactor;

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
        $ids = $this->getRequest()->getParam('ids');
        $token = $this->getRequest()->getParam('token');

        $response = $this->dataHelper->deleteQueueData($token, $ids);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}