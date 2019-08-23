<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;
use \Bitqit\Searchtap\Model\QueueFactory ;
use \Bitqit\Searchtap\Helper\Products\ProductHelper ;
use \Bitqit\Searchtap\Helper\ConfigHelper ;
use \Bitqit\Searchtap\Helper\Logger ;

class Products implements \Magento\Framework\Event\ObserverInterface
{
    private $queueFactory;
    private $productHelper;
    private $configHelper;
    protected $beforeAssociatedStoreIds;
    private $logger;

    public function __construct(
        QueueFactory $queueFactory,
        ProductHelper $productHelper,
        ConfigHelper $configHelper,
        Logger $logger
    )
    {
        $this->queueFactory = $queueFactory;
        $this->productHelper = $productHelper;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $storeIds = $this->configHelper->getEnabledStoresForIndexing($product->getStoreId());
        $this->logger->add($observer->getEvent()->getName());
        foreach ($storeIds as $storeId) {
            switch ($observer->getEvent()->getName()) {

                case "catalog_product_save_before":
                    $this->catalogProductSaveBefore($product, $storeId);
                    break;
                case "catalog_product_save_after":
                    $this->catalogProductSaveAfter($product, $storeId);
                    break;
                case "catalog_product_delete_before":
                    $this->catalogProductDeleteBefore($product, $storeId);
                    break;


            }
        }
    }

    /*
     * @searchtap
     * Product Trigger Before Save
     * */

    private function catalogProductSaveBefore($product, $storeId)
    {
        try {
            $this->beforeAssociatedStoreIds = $product->getStoreIds();
            $this->logger->add($this->beforeAssociatedStoreIds);
        } catch (error $e) {
            $this->logger->error($e);
        }

    }

    /*
     * @searchtap
     * Product Trigger After Save
     * */

    private function catalogProductSaveAfter($product, $storeId)
    {
        try {
            $getUpdatedStoreIds = $product->getStoreIds();
            if (!empty($getUpdatedStoreIds)) {
                $action = "add";
                if ($this->beforeAssociatedStoreIds) {
                    foreach ($this->beforeAssociatedStoreIds as $stores) {
                        if (!in_array($stores, $getUpdatedStoreIds)) {
                            $action = "delete";
                            $this->queueFactory->create()->addToQueue($product->getId(), $action, 'pending', 'product', $this->beforeAssociatedStoreIds);
                        }
                    }
                }
                $this->queueFactory->create()->addToQueue($product->getId(), $action, 'pending', 'product', $storeId);
            }
        } catch (error $e) {
            $this->logger->error($e);
        }

    }

    /*
     * @searchtap
     * Product Trigger Delete Before
     * */

    private function catalogProductDeleteBefore($product, $storeId)
    {
        $getProductStores = $product->getStoreIds();
        try {
            foreach ($getProductStores as $storeid)
                $this->queueFactory->create()->addToQueue($product->getId(), 'delete', 'pending', 'product', $storeid);
        } catch (error $e) {
            $this->logger->error($e);
        }

    }


    /*
     * @searchtap
     * Product Trigger Import
     * */


}