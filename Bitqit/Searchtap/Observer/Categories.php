<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;

class Categories implements \Magento\Framework\Event\ObserverInterface
{
    private $queueFactory;
    private $categoryHelper;
    private $configHelper;
    protected $associatedProductIds;
    private $logger;

    public function __construct(
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
        \Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
        \Bitqit\Searchtap\Helper\Logger $logger
    )
    {
        $this->queueFactory = $queueFactory;
        $this->categoryHelper = $categoryHelper;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        //If storeId = 0 then the category for all available stores
        $storeIds = $this->configHelper->getEnabledStoresForIndexing($category->getStoreId());

        foreach ($storeIds as $storeId) {

            switch ($observer->getEvent()->getName()) {
                case "catalog_category_save_before":
                    $this->catalogCategorySaveBefore($category, $storeId);
                    break;
                case "catalog_category_save_after":
                    $this->catalogCategorySaveAfter($category, $storeId);
                    break;
                case "catalog_category_delete_before":
                    $this->catalogCategoryDeleteBefore($category, $storeId);
                    break;
                //todo: catalog_catagory_move_after event
            }
        }
    }

    public function catalogCategorySaveBefore($category, $storeId)
    {
        try {
            $this->associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');
      //      $this->logger->add($category);
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogCategorySaveAfter($category, $storeId)
    {
        try {
            $action = "add";
            if (!$this->categoryHelper->canCategoryBeReindex($category, $storeId))
                $action = "delete";

            $this->queueFactory->create()->addToQueue($category->getId(), $action, 'pending', 'category', $storeId);

            $associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');
            $this->associatedProductIds = array_unique(array_merge($this->associatedProductIds, $associatedProductIds));

            //todo: check whether the products can be reindex
            foreach ($this->associatedProductIds as $productId)
                $this->queueFactory->create()->addToQueue($productId, "add", 'pending', 'product', $storeId);

            unset($this->associatedProductIds);
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogCategoryDeleteBefore($category, $storeId)
    {
        try {
            $this->associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');

            //todo: check whether the products can be reindex
            foreach ($this->associatedProductIds as $productId)
                $this->queueFactory->create()->addToQueue($productId, "add", 'pending', 'product', $storeId);

            $this->queueFactory->create()->addToQueue($category->getId(), "delete", 'pending', 'category', $storeId);

        } catch (error $e) {
            $this->logger->error($e);
        }
    }
}