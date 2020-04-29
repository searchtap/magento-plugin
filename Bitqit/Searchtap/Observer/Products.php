<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;
use \Bitqit\Searchtap\Model\QueueFactory;
use \Bitqit\Searchtap\Helper\Products\ProductHelper;
use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\Data;
use \Bitqit\Searchtap\Helper\Logger;
use mysql_xdevapi\Exception;

class Products implements \Magento\Framework\Event\ObserverInterface
{
    private $queueFactory;
    private $productHelper;
    private $configHelper;
    private $dataHelper;
    private $logger;

    public function __construct(
        QueueFactory $queueFactory,
        ProductHelper $productHelper,
        ConfigHelper $configHelper,
        Data $dataHelper,
        Logger $logger
    )
    {
        $this->queueFactory = $queueFactory;
        $this->productHelper = $productHelper;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

//        $storeIds = $this->dataHelper->getEnableStoreIds($product->getStoreIds());
        $storeIds = $product->getStoreIds();

        foreach ($storeIds as $storeId) {
            switch ($observer->getEvent()->getName()) {
//                case "catalog_product_save_before":
//                    $this->catalogProductSaveBefore($product, $storeId);
//                    break;
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
     * Product Trigger After Save
     * */
    private function catalogProductSaveAfter($product, $storeId)
    {
        try {
            $productId = $product->getId();
            $action = "add";

            //todo: Need to test if product is moved from one store to another store
            // Check if product is mapped to this store
            $storeIds = $product->getStoreIds();
            if (!in_array($storeId, $storeIds))
                $action = "delete";

            //todo: Check product type before checking parent product
            /*
             * Check if product is simple that is child of configurable products
             * if yes then parent need to re-index
             */
            $configurableProductId = $this->productHelper->getConfigurableProductIdFromChildProduct($productId);
            if ($configurableProductId) $productId = $configurableProductId;

            /*
             * Check if product is simple and part of bundle product
             * if yes then both product and associated bundle product need to re-index
             */
            $bundleProductId = $this->productHelper->getBundleProductIdFromSimpleProduct($productId);
            if ($bundleProductId) {
                $this->queueFactory->create()->addToQueue($bundleProductId, 'add', 'pending', 'product', $storeId);
            }

            if (!$configurableProductId) {
                // Check if product can be re-indexed
                if ($product->getStatus() != 1 || $product->getVisibility() == 1)
                    $action = "delete";
            }

            $this->queueFactory->create()->addToQueue($productId, $action, 'pending', 'product', $storeId);
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }

    /*
     * @searchtap
     * Product Trigger Delete Before
     * */
    private function catalogProductDeleteBefore($product, $storeId)
    {
        try {
            $productId = $product->getId();
            /*
             * Check if product is simple that is child of configurable products
             * if yes then parent need to re-index
             */
            $configurableProductId = $this->productHelper->getConfigurableProductIdFromChildProduct($productId);
            if ($configurableProductId) {
                $this->queueFactory->create()->addToQueue($product->getId(), 'add', 'pending', 'product', $storeId);
            }

            /*
             * Check if product is simple and part of bundle product
             * if yes then product need to delete and associated bundle product need to re-index
             */
            $bundleProductId = $this->productHelper->getBundleProductIdFromSimpleProduct($productId);
            if ($bundleProductId) {
                $this->queueFactory->create()->addToQueue($bundleProductId, 'add', 'pending', 'product', $storeId);
            }

            if (!$configurableProductId) {
                $this->queueFactory->create()->addToQueue($productId, 'delete', 'pending', 'product', $storeId);
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }
}