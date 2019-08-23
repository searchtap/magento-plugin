<?php

namespace Bitqit\Searchtap\Controller\Products;

class Attributes extends \Magento\Framework\App\Action\Action
{
    private $attributeHelper;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\AttributeHelper $attributeHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->attributeHelper = $attributeHelper;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $token = $this->getRequest()->getParam("token");
        $response = $this->attributeHelper->getFilterableAttributesCollection($token);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}