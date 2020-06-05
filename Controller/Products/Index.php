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
        $this->searchtapHelper = $searchtapHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 1);
        $page = $this->getRequest()->getParam('page', 1);
        $count = $this->getRequest()->getParam('count', 100);
        $productIds = $this->getRequest()->getParam('ids');
        $isCacheImage = $this->getRequest()->getParam('is_cache_image', true);
        $imageType = $this->getRequest()->getParam('image_type', 'base_image');
        $imageWidth = $this->getRequest()->getParam('image_width', 300);
        $imageHeight = $this->getRequest()->getParam('image_height', 300);
        $onHoveImageType = $this->getRequest()->getParam('on_hover_image_type', 'base_image');
        $onHoverImageStatus = $this->getRequest()->getParam('on_hover_image_status', false);
        $indexOutOfStockVariations = $this->getRequest()->getParam('index_oos_variations', false);
        $token = $this->getRequest()->getParam('token');

        $imageConfig = array(
            "is_cache_image" => $isCacheImage,
            "image_type" => $imageType,
            "image_width" => $imageWidth,
            "image_height" => $imageHeight,
            "on_hover_image_status" => $onHoverImageStatus,
            "on_hover_image_type" => $onHoveImageType
        );

        if ($productIds)
            $productIds = explode(',', $productIds);

        $response = $this->productHelper->getProductsJSON($token, $storeId, $count, $page, $imageConfig, $indexOutOfStockVariations, $productIds);

        $this->getResponse()->setHeader('content-type', 'application/json');
        $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
        $this->getResponse()->setBody($response["output"]);
    }
}
