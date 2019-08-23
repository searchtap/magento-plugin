<?php

namespace Bitqit\Searchtap\Helper\Categories;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Catalog\Api\CategoryRepositoryInterface;
use \Bitqit\Searchtap\Helper\Logger;
use \Bitqit\Searchtap\Helper\Data;

class CategoryHelper
{
    private $configHelper;
    private $categoryCollectionFactory;
    private $storeManager;
    private $categoryRepository;
    private $searchtapHelper;
    private $logger;
    private $dataHelper;

    public function __construct(
        ConfigHelper $configHelper,
        SearchtapHelper $searchtapHelper,
        CollectionFactory $categoryCollectionFactory,
        StoreManagerInterface $storeManager,
        CategoryRepositoryInterface $categoryRepository,
        Logger $logger,
        Data $dataHelper
    )
    {
        $this->configHelper = $configHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->searchtapHelper = $searchtapHelper;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
    }

    public function getCategoriesJSON($token, $storeId, $categoryIds = null)
    {
        if (!$this->dataHelper->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        if (!$this->configHelper->isStoreAvailable($storeId)) {
            return $this->searchtapHelper->error("store not found for ID " . $storeId, 404);
        }

        //Start Frontend Emulation
        $this->searchtapHelper->startEmulation($storeId);

        $collection = $this->getCategoryCollection($storeId, $categoryIds);

        $data = [];

        foreach ($collection as $category) {
            if (!$this->isCategoryPathActive($category, $storeId))
                continue;

            $data[] = $this->getObject($category, $storeId);
        }

        //Stop Emulation
        $this->searchtapHelper->stopEmulation();

        return $this->searchtapHelper->okResult($data, count($data));
    }

    public function getRequiredAttributes()
    {
        return [
            'name',
            'is_active',
            'include_in_menu',
            'product_count',
            'description',
            'meta_title',
            'meta_description',
            'meta_keywords',
            'level',
            'path'
        ];
    }

    public function getCategoryCollection($storeId, $categoryIds = null)
    {
        try {
            $requiredAttributes = $this->getRequiredAttributes();

            $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

            $collection = $this->categoryCollectionFactory->create();
            $collection->setStore($storeId);
            $collection->addAttributeToSelect($requiredAttributes);
            $collection->addAttributeToFilter('is_active', ['eq' => true]);
            $collection->addAttributeToFilter('level', ['gt' => 1]);
            $collection->addAttributeToFilter('path', ['like' => "1/$rootCategoryId/%"]);

            if ($categoryIds)
                $collection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);

            return $collection;
        } catch (error $e) {
            $this->logger->error($e);
            return [];
        }
    }

    public function isCategoryPathActive($category, $storeId)
    {
        try {
            $pathIds = $category->getPathIds();

            //todo: We can use category repository instead
            foreach ($pathIds as $pathId) {
                $collection = $this->categoryCollectionFactory->create();
                $collection->setStore($storeId);
                $collection->addAttributeToSelect(["level", "is_active"]);
                $collection->addAttributeToFilter('level', ['gt' => 1]);
                $collection->addAttributeToFilter('entity_id', ['eq' => $pathId]);

                foreach ($collection as $category) {
                    if ($category && (bool)$category->getIsActive() === false)
                        return false;
                }
            }
            return true;
        } catch (error $e) {
            $this->logger->error($e);
            return false;
        }
    }

    public function canCategoryBeReindex($category, $storeId)
    {
        if ((int)$category->getLevel() > 1 && $this->isCategoryPathActive($category, $storeId))
            return true;

        return false;
    }

    public function getFormattedString($string)
    {
        return $this->searchtapHelper->getFormattedString($string);
    }

    public function getCategoryPath($category, $storeId)
    {
        $pathIds = $category->getPathIds();

        $path = "";

        foreach ($pathIds as $pathId) {
            $category = $this->categoryRepository->get($pathId, $storeId);
            if ((int)$category->getLevel() > 1)
                if ($path) $path .= "///" . $this->getFormattedString($category->getName());
                else $path = $this->getFormattedString($category->getName());
        }

        return $path;
    }

    public function getProductCount($category, $storeId)
    {
        $productIds = [];

        try {
            $productCollection = $category->getProductCollection();
            $productCollection->setStore($storeId);
            $productCollection->addAttributeToSelect('visibility');

            foreach ($productCollection as $product) {
                if ($product->getVisibility() != 1)
                    $productIds[] = $product->getId();
            }
        } catch (error $e) {
            $this->logger->error($e);
        }

        return count($productIds);
    }

    public function getObject($category, $storeId)
    {
        $data = [];

        $data['id'] = (int)$category->getId();
        $data['name'] = $this->getFormattedString($category->getName());
        $data['url'] = $category->getUrl();
        $data['product_count'] = $this->getProductCount($category, $storeId);
        $data['is_active'] = (bool)$category->getIsActive();
        $data['include_in_menu'] = (bool)$category->getIncludeInMenu();
        $data['description'] = $this->getFormattedString($category->getDescription());
        $data['meta_title'] = $this->getFormattedString($category->getMetaTitle());
        $data['meta_description'] = $this->getFormattedString($category->getMetaDescription());
        $data['meta_keywords'] = $category->getMetaKeywords() ? explode(",", $category->getMetaKeywords()) : [];
        $data['level'] = (int)$category->getLevel();
        $data['parent_id'] = (int)$category->getParentId();
        $data['path'] = $this->getCategoryPath($category, $storeId);
        $data['created_at'] = strtotime($category->getCreatedAt());
        $data['isLastLevel'] = $category->hasChildren() ? false : true;
        $data['last_pushed_to_searchtap'] = $this->searchtapHelper->getCurrentDate();

        return $data;
    }

    public function getProductCategories($product, $storeId)
    {
        $categoriesData = [
            "_categories" => [],
            "categories_path" => [],
            "category_level" => []
        ];

        $categoryIds = $product->getCategoryIds();

        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {
                $productCategory = $this->categoryRepository->get($categoryId, $storeId);
                if (!$productCategory || !$this->canCategoryBeReindex($productCategory, $storeId))
                    continue;

                $categoriesData["_categories"][] = $this->getFormattedString($productCategory->getName());
                $categoriesData["categories_path"][] = $this->getCategoryPath($productCategory, $storeId);

                $pathIds = $productCategory->getPathIds();

                foreach ($pathIds as $pathId) {
                    $category = $this->categoryRepository->get($pathId, $storeId);
                    if ($category && (int)$category->getLevel() > 1) {
                        $level = $category->getLevel() - 1; //level starts from 2 but we need to level to be started from 1
                        $categoryName = $this->getFormattedString($category->getName());

                        //Check if category already exists or not
                        if (!array_key_exists("category_level_" . $level, $categoriesData["category_level"])
                            || !in_array($categoryName, $categoriesData["category_level"]["category_level_" . $level])) {
                            $categoriesData["category_level"]["category_level_" . $level][] = $this->getFormattedString($category->getName());
                        }
                    }
                }
            }
        }

        $categoriesData["_categories"] = array_unique($categoriesData["_categories"]);

        return $categoriesData;
    }
}