<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;

class Categories implements \Magento\Framework\Event\ObserverInterface
{
    private $queueFactory;
    private $categoryHelper;
    private $productHelper;
    protected $associatedProductIds;
    private $logger;

    public function __construct(
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper,
        \Bitqit\Searchtap\Helper\Logger $logger
    )
    {
        $this->queueFactory = $queueFactory;
        $this->categoryHelper = $categoryHelper;
        $this->productHelper = $productHelper;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();

        switch ($observer->getEvent()->getName()) {
            case "catalog_category_save_before":
                $this->catalogCategorySaveBefore($category);
                break;
            case "catalog_category_save_after":
                $this->catalogCategorySaveAfter($category);
                break;
            case "catalog_category_delete_before":
                $this->catalogCategoryDeleteBefore($category);
                break;
            //todo: catalog_category_move_after event
        }
    }

    public function catalogCategorySaveBefore($category)
    {
        try {
            $this->associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogCategorySaveAfter($category)
    {
        try {
            $action = "add";

            $storeId = $category->getStoreId();

            if (!$this->categoryHelper->canCategoryBeReindex($category, $storeId))
                $action = "delete";

            $this->queueFactory->create()->addToQueue($category->getId(), $action, 'pending', 'category', $storeId);

            $associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');
            $this->associatedProductIds = array_unique(array_merge($this->associatedProductIds, $associatedProductIds));

            foreach ($this->associatedProductIds as $productId) {
                $this->queueFactory->create()->addToQueue($productId, "add", 'pending', 'product', $storeId);
            }

            unset($this->associatedProductIds);
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogCategoryDeleteBefore($category, $storeId)
    {
        try {
            $this->associatedProductIds = $category->getProductCollection()->getColumnValues('entity_id');

            foreach ($this->associatedProductIds as $productId) {
                $this->queueFactory->create()->addToQueue($productId, "add", 'pending', 'product', $storeId);
            }

            $this->queueFactory->create()->addToQueue($category->getId(), "delete", 'pending', 'category', $storeId);
        } catch (error $e) {
            $this->logger->error($e);
        }
    }
}