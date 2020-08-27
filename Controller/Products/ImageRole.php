<?php


namespace Bitqit\Searchtap\Controller\Products;
use Bitqit\Searchtap\Helper\Products\ImageHelper;

class ImageRole extends \Magento\Framework\App\Action\Action
{
    private $productHelper;
    private $searchtapHelper;
    private $imageHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
        ImageHelper $imageHelper
    )
    {
        $this->productHelper = $productHelper;
        $this->searchtapHelper = $searchtapHelper;
        $this->imageHelper=$imageHelper;

        parent::__construct($context);
    }

    public function execute()
    {
        try {

            $response = $this->imageHelper->getImageRole();
            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
            $this->getResponse()->setBody($response["output"]);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
