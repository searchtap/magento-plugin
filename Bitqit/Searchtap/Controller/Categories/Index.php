<?php

namespace Bitqit\Searchtap\Controller\Categories;

class Index extends \Magento\Framework\App\Action\Action
{
    private $categoryHelper;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->categoryHelper = $categoryHelper;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        $categoryIds = $this->getRequest()->getParam('ids');

        if ($categoryIds)
            $categoryIds = explode(',', $categoryIds);

        $response = $this->categoryHelper->getCategoriesJSON($storeId, $categoryIds);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}