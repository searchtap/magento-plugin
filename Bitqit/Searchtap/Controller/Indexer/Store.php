<?php

namespace Bitqit\Searchtap\Controller\Indexer;

class Store extends \Magento\Framework\App\Action\Action
{
    private $searchtapHelper;
    private $dataHelper;

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
        $token = $this->getRequest()->getParam("token");

        $response = $this->dataHelper->getStoresData($token);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}