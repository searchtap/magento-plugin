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
        $page = $this->getRequest()->getParam('page', 1);
        $count = $this->getRequest()->getParam('count', 50);
        $categoryIds = $this->getRequest()->getParam('ids');
        $token = $this->getRequest()->getParam('token');

        if ($categoryIds)
            $categoryIds = explode(',', $categoryIds);

        $response = $this->categoryHelper->getCategoriesJSON($token, $storeId, $categoryIds,$page,$count);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}