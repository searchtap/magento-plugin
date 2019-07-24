<?php

namespace Bitqit\Searchtap\Helper\Categories;

class CategoryHelper
{
    private $configHelper;
    private $categoryCollectionFactory;
    private $storeManager;
    private $categoryRepository;
    private $searchtapHelper;

    public function __construct(
        \Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    )
    {
        $this->configHelper = $configHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->searchtapHelper = $searchtapHelper;
    }

    public function getCategoriesJSON($storeId, $categoryIds = null)
    {
        //check if indexing is enabled for the store
        if (!$this->configHelper->isIndexingEnabled($storeId)) {
            echo "Indexing is disabled for the store: " . $storeId;
            return;
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

        return json_encode($data);
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
        $requiredAttributes = $this->getRequiredAttributes();

        $rootCategoryId = $this->storeManager->getStore($storeId)->getRootCategoryId();

        $collection = $this->categoryCollectionFactory->create();
        $collection->setStore($storeId);
        $collection->addAttributeToSelect($requiredAttributes);
        $collection->addAttributeToFilter('is_active', ['eq' => true]);
        $collection->addAttributeToFilter('level', ['gt' => 1]);
        $collection->addAttributeToFilter('path', ['like' => "%/$rootCategoryId/%"]);

        if ($categoryIds)
            $collection->addAttributeToFilter('entity_id', ['in' => $categoryIds]);

        return $collection;
    }

    public function isCategoryPathActive($category, $storeId)
    {
        $pathIds = $category->getPathIds();

        //todo: We can use category repository instead
        foreach ($pathIds as $pathId) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->setStore($storeId);
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('level', ['gt' => 1]);
            $collection->addAttributeToFilter('entity_id', ['eq' => $pathId]);

            foreach ($collection as $category) {
                if ($category && (bool)$category->getIsActive() === false)
                    return false;
            }
        }

        return true;
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

    public function getMetaKeywords($category)
    {
        //todo: check if comma is available in meta keywords
        $keywords = $category->getMetaKeywords();

        if ($keywords)
            return explode(',', $keywords);
        else return [];
    }

    public function getObject($category, $storeId)
    {
        $data = [];

        $data['id'] = (int)$category->getId();
        $data['name'] = $this->getFormattedString($category->getName());
        $data['url'] = $category->getUrl();
        //todo: check if disabled product is also included in product count
        $data['product_count'] = $category->getProductCount();
        $data['is_active'] = (int)$category->getIsActive();
        $data['include_in_menu'] = (int)$category->getIncludeInMenu();
        $data['description'] = $this->getFormattedString($category->getDescription());
        $data['meta_title'] = $this->getFormattedString($category->getMetaTitle());
        $data['meta_description'] = $this->getFormattedString($category->getMetaDescription());
        $data['meta_keywords'] = $this->getMetaKeywords($category);
        $data['level'] = (int)$category->getLevel();
        $data['parent_id'] = (int)$category->getParentId();
        $data['path'] = $this->getCategoryPath($category, $storeId);
        $data['created_at'] = $category->getCreatedAt();
        $data['last_pushed_to_searchtap'] = "";

        //todo: last level category attribite

        return $data;
    }

   public function getProductStores($productId){

   }
}