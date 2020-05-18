<?php

namespace Bitqit\Searchtap\Controller\Image;

class Resize extends \Magento\Framework\App\Action\Action
{
    private $imageHelper;
    private $searchtapHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\ImageHelper $imageHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper
    )
    {
        $this->imageHelper = $imageHelper;
        $this->searchtapHelper = $searchtapHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        $height = $this->getRequest()->getParam('height', 300);
        $width = $this->getRequest()->getParam('width', 300);
        $page = $this->getRequest()->getParam('page', 1);
        $count = $this->getRequest()->getParam('count', 100);
     //   $token = $this->getRequest()->getParam('token');

        $response = $this->imageHelper->forceResizeImage($storeId,$height,$width,$count,$page);
        $this->getResponse()->setHeader('content-type', 'application/json');
      //  $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response);
    }
}