<?php

namespace Bitqit\Searchtap\Controller\Categories;

class Index extends \Magento\Framework\App\Action\Action
{
    private $categoryHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper
    )
    {
        $this->categoryHelper = $categoryHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        $categoryIds = $this->getRequest()->getParam('ids');

        if ($categoryIds)
            $categoryIds = explode(',', $categoryIds);

        $result = $this->categoryHelper->getCategoriesJSON($storeId, $categoryIds);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }
}