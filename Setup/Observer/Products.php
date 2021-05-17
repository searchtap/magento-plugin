<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;
use \Bitqit\Searchtap\Model\QueueFactory;
use \Bitqit\Searchtap\Helper\Products\ProductHelper;
use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\Data;
use \Bitqit\Searchtap\Helper\Logger;
use \Magento\Catalog\Model\Product;
use mysql_xdevapi\Exception;

class Products implements \Magento\Framework\Event\ObserverInterface
{
    private $queueFactory;
    private $productHelper;
    private $configHelper;
    private $dataHelper;
    private $logger;
    private $product;

    public function __construct(
        QueueFactory $queueFactory,
        ProductHelper $productHelper,
        ConfigHelper $configHelper,
        Data $dataHelper,
        Logger $logger,
        Product $product
    )
    {
        $this->queueFactory = $queueFactory;
        $this->productHelper = $productHelper;
        $this->configHelper = $configHelper;
        $this->dataHelper = $dataHelper;
        $this->logger = $logger;
        $this->product = $product;
    }

    public function execute(Observer $observer)
    {
        switch ($observer->getEvent()->getName()) {
//                case "catalog_product_save_before":
//                    $this->catalogProductSaveBefore($product, $storeId);
//                    break;
            case "catalog_product_save_after":
                $this->catalogProductSaveAfter($observer);
                break;
            case "catalog_product_delete_before":
                $this->catalogProductDeleteBefore($observer);
                break;
            case "catalog_product_attribute_update_before":
                $this->catalogProductAttributeUpdateBefore($observer);
                break;
            case "catalog_product_import_bunch_save_after":
                $this->catalogProductImportBunchSaveAfter($observer);
                break;
            case "catalog_product_import_bunch_delete_commit_before":
                $this->catalogProductImportBunchDeleteCommitBefore($observer);
                break;
        }
    }

    public function catalogProductImportBunchSaveAfter($observer)
    {
        try {
            $data = $observer->getEvent()->getData('bunch');
            $stores = $this->dataHelper->getEnabledStores();
            foreach ($stores as $store) {
                foreach ($data as $product) {
                    $productId = $this->product->getIdBySku($product['sku']);
                    $this->queueFactory->create()->addToQueue($productId, 'add', 'pending', 'product', $store->getId());
                }
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    public function catalogProductImportBunchDeleteCommitBefore($observer)
    {
        try {
            $idsToDelete = $observer->getEvent()->getData('ids_to_delete');
            $stores = $this->dataHelper->getEnabledStores();
            foreach ($stores as $store) {
                foreach ($idsToDelete as $productId)
                    $this->queueFactory->create()->addToQueue($productId, 'delete', 'pending', 'product', $store->getId());
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    public function catalogProductAttributeUpdateBefore($observer)
    {
        try {
            $productIds = $observer->getEvent()->getProductIds();
            $updatedAttributes = $observer->getAttributesData();
            $stores = $this->dataHelper->getEnabledStores();
            foreach ($stores as $store) {
                $products = $this->productHelper->getProductByIds($productIds, $store->getId());
                $actualProductIds = [];
                foreach ($products as $product) {
                    $actualProductIds[] = $product->getId();
                    $action = "add";
                    // Check if product can be re-indexed
                    if ((isset($updatedAttributes['status']) && $updatedAttributes['status'] != 1)
                        || (!isset($updatedAttributes['status']) && $product->getStatus() != 1)
                        || (isset($updatedAttributes['visibility']) && $updatedAttributes['visibility'] == 1)
                        || (!isset($updatedAttributes['visibility']) && $product->getVisibility() == 1))
                        $action = "delete";

                    if ($product->getTypeId() === "simple")
                        $this->addActionForParentProducts($product->getId(), $store->getId());

                    $this->queueFactory->create()->addToQueue($product->getId(), $action, 'pending', 'product', $store->getId());
                }

                $idsToDelete = array_diff($productIds, $actualProductIds);
                foreach ($idsToDelete as $productId)
                    $this->queueFactory->create()->addToQueue($productId, 'delete', 'pending', 'product', $store->getId());
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    public function catalogProductSaveAfter($observer)
    {
        try {
            //todo: Need to test if product is moved from one store to another store
            // Check if product is mapped to this store
//            $storeIds = $product->getStoreIds();
//            if (!in_array($storeId, $storeIds))
//                $this->logger($storeId);

            $product = $observer->getEvent()->getProduct();
            $storeIds = $product->getStoreIds();

            foreach ($storeIds as $storeId) {
                $action = "add";
                // Check if product can be re-indexed
                if ($product->getStatus() != 1 || $product->getVisibility() == 1)
                    $action = "delete";

                if ($product->getTypeId() === "simple")
                    $this->addActionForParentProducts($product->getId(), $storeId);

                $this->queueFactory->create()->addToQueue($product->getId(), $action, 'pending', 'product', $storeId);
            }

        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    public function catalogProductDeleteBefore($observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $storeIds = $product->getStoreIds();
            $productId = $product->getId();

            foreach ($storeIds as $storeId) {
                if ($product->getTypeId() === "simple")
                    $this->addActionForParentProducts($productId, $storeId);

                $this->queueFactory->create()->addToQueue($productId, 'delete', 'pending', 'product', $storeId);
            }

        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    public function addActionForParentProducts($productId, $storeId)
    {
        try {
            /*
             * Check if product is simple that is child of configurable products
             * if yes then parent need to re-index
            */
            $configurableProductId = $this->productHelper->getConfigurableProductIdFromChildProduct($productId);
            if ($configurableProductId)
                $this->queueFactory->create()->addToQueue($configurableProductId, 'add', 'pending', 'product', $storeId);

            /*
             * Check if product is simple and part of bundle product
             * if yes then both product and associated bundle product need to re-index
             */
            $bundleProductId = $this->productHelper->getBundleProductIdFromSimpleProduct($productId);
            if ($bundleProductId)
                $this->queueFactory->create()->addToQueue($bundleProductId, 'add', 'pending', 'product', $storeId);

            /*
            * Check if product is simple and part of grouped product
            * if yes then both product and associated grouped product need to re-index
            */
            $groupedProductId = $this->productHelper->getGroupedProductIdFromSimpleProduct($productId);
            if ($groupedProductId)
                $this->queueFactory->create()->addToQueue($groupedProductId, 'add', 'pending', 'product', $storeId);

        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}