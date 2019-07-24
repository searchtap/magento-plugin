<?php

namespace Bitqit\Searchtap\Controller\Products;

class Index extends \Magento\Framework\App\Action\Action
{
    private $productHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper
    )
    {
        $this->productHelper = $productHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('storeid', 1);
        $getOffset=$this->getRequest()->getParam('offset', 1);
        $getCount=$this->getRequest()->getParam('count', 1);
        $productIds = $this->getRequest()->getParam('pids');

        if ($productIds)
            $productIds = explode(',', $productIds);

        $result = $this->productHelper->getProductsJSON($storeId,$productIds,$getOffset,$getCount);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setBody($result);
    }
}