<?php

namespace Bitqit\Searchtap\Controller\Products;

class Index extends \Magento\Framework\App\Action\Action
{
    private $productHelper;
    private $searchtapHelper;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->productHelper = $productHelper;
        $this->searchtapHelper=$searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('storeid', 1);
        $getOffset=$this->getRequest()->getParam('offset', 1);
        $getCount=$this->getRequest()->getParam('count', 1);
        $productIds = $this->getRequest()->getParam('pids');
        $imageWidth=$this->getRequest()->getParam('imgwidth');
        $imageHeight=$this->getRequest()->getParam('imgheight');

        if ($productIds)
            $productIds = explode(',', $productIds);

        $response= $this->productHelper->getProductsJSON($storeId,$productIds,$getOffset,$getCount,$imageWidth,$imageHeight);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}
