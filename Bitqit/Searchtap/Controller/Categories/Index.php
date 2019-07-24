<?php

namespace Bitqit\Searchtap\Controller\Categories;

class Index extends \Magento\Framework\App\Action\Action
{
    private $categoryHelper;
    private $isStoreExist;
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
        \Bitqit\Searchtap\Helper\ConfigHelper $searchtapHelper
    )
    {
        $this->categoryHelper = $categoryHelper;
        $this->isStoreExist=$searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('storeid',1);
        $categoryIds = $this->getRequest()->getParam('ids');

        if($this->isStoreExist->checkStoreAvailibity($storeId)){
        if ($categoryIds)
            $categoryIds = explode(',', $categoryIds);

        $result = $this->categoryHelper->getCategoriesJSON($storeId, $categoryIds);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setBody($result);
        }
        else{
            $msg="StoreId ".$storeId." does not exist !!!!";
            echo $msg;
        }
    }



}