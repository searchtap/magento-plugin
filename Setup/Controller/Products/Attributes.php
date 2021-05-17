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
        try {
            $token = $this->getRequest()->getParam("token");
            $attributeCodes = $this->getRequest()->getParam("attribute_code");

            $response = $this->attributeHelper->getFilterableAttributesCollection($token, $attributeCodes);

            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
            $this->getResponse()->setBody($response["output"]);

        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
