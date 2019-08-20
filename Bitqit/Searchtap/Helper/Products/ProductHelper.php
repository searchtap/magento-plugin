<?php

namespace Bitqit\Searchtap\Helper\Products;

class ProductHelper
{
    private $requiredAttributes;
    private $configHelper;
    private $imageHelper;
    private $searchtapHelper;
    private $productCollectionFactory;
    private $discount_per;
    private $objectManager;
    private $categoryHelper;
    private $attributeHelper;
    private $storeManager;
    private $currencyFactory;

    public function __construct(\Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
                                \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
                                \Bitqit\Searchtap\Helper\Products\ImageHelper $imageHelper,
                                \Magento\Catalog\Model\Category $objectManager,
                                \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
                                \Bitqit\Searchtap\Helper\Products\AttributeHelper $attributeHelper,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Directory\Model\Currency $currencyFactory
    )
    {
        $this->imageHelper = $imageHelper;
        $this->searchtapHelper = $searchtapHelper;
        $this->configHelper = $configHelper;
        $this->productCollectionFactory = $productFactory;
        $this->objectManager = $objectManager;
        $this->categoryHelper = $categoryHelper;
        $this->attributeHelper = $attributeHelper;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
    }

    public function getDiscountPercentage($regularPrice, $specialPrice)
    {
        if ($specialPrice && $regularPrice) {
            $discount = (($regularPrice - $specialPrice) / $regularPrice) * 100;
            return round($discount);
        }

        return 0;
    }

    public function getCurrencySymbol($storeId)
    {
        $currencyCode =  $this->storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->load($currencyCode);
        return $currency->getCurrencySymbol();
    }

    public function getFormattedPrice($regularPrice, $specialPrice, $currencySymbol, $priceMin, $priceMax)
    {
        $data = [];

        if ($regularPrice)
            $data['regular_price'] = $currencySymbol . $regularPrice;
        if ($specialPrice)
            $data['special_price'] = $currencySymbol . $specialPrice;
        if ($priceMin && $priceMax)
            $data['price_range'] = $currencySymbol . $priceMin . " - " . $currencySymbol . $priceMax;

        return $data;
    }

    public function getPrices($product, $storeId)
    {
        //todo: Index the prices based on customer group in Phase 3
        //todo: Index the tier prices in Phase 4

        $regularPrice = $this->searchtapHelper->getFormattedPrice($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue());
        $specialPrice = $this->searchtapHelper->getFormattedPrice($product->getFinalPrice());
        $priceMin = $this->searchtapHelper->getFormattedPrice($product->getMinPrice());
        $priceMax = $this->searchtapHelper->getFormattedPrice($product->getMaxPrice());
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $currencySymbol = $this->getCurrencySymbol($storeId);
        $formattedPrice = $this->getFormattedPrice($regularPrice, $specialPrice, $currencySymbol, $priceMin, $priceMax);

        $data = [
            'special_from_date' => $specialFromDate ? strtotime($specialFromDate) : false,
            'special_to_date' => $specialToDate ? strtotime($specialToDate) : false,
            'discount' => $this->getDiscountPercentage($regularPrice, $specialPrice)
            ];

        $productType = $product->getTypeId();

        if ($productType === "simple" || $productType === "configurable") {
            $data['price'] = $formattedPrice['regular_price'];

            if ($specialPrice && $specialPrice !== $regularPrice) {
                $data['price'] = $formattedPrice['special_price'];
                $data['original_price'] = $formattedPrice['regular_price'];
            }
        } else {
            $data['price'] = $formattedPrice['price_range'];
        }

        return $data;
    }

    public function getProductCollection($storeId, $productIds = null, $offset = null, $count = null)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStore($storeId);
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', ['eq' => 1]);
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->setPageSize($offset);
        $collection->setCurPage($count);

        if ($productIds)
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }

    public function getProductsJSON($storeId, $productIds = null, $offset = null, $count = null)
    {
        if (!$this->configHelper->isIndexingEnabled($storeId)) {
            echo "Indexing is disabled for the store: " . $storeId;
            return;
        }
        //Start Frontend Emulation
        $this->searchtapHelper->startEmulation($storeId);

        $productCollection = $this->getProductCollection($storeId, $productIds);

        $data = [];

        foreach ($productCollection as $product) {
            //   $this->isIndexableProduct($product->getId());
            $data[] = $this->getProductObject($product, $storeId);
        }
        //Stop Emulation
        $this->searchtapHelper->stopEmulation();

        return json_encode($data);
    }

    public function getFormattedString($string)
    {
        return $this->searchtapHelper->getFormattedString($string);
    }

    public function getProductObject($product, $storeId)
    {
        $data = [];
        $data['id'] = $product->getId();
        $data['name'] = $this->getFormattedString($product->getName());
        $data['sku'] = $product->getSKU();
        $data['URL'] = $product->getProductUrl();
        $data['status'] = $product->getStatus();
        $data['visibility'] = $product->getVisibility();// need todo
        $data['type'] = $product->getTypeId();
        $data['description'] = $this->getFormattedString(str_replace("\r\n", "", $product->getDescription()));
        $data['base_image'] = $this->getFormattedString($this->imageHelper->generateImage($product, 'image'));
        $data['thumbnail_image'] = $this->getFormattedString($this->imageHelper->generateImage($product, 'thumbnail'));
        $data['created_at'] = $product->getCreatedAt();
        $data['category'] = $this->getProductCategory($product, 'category');
        $data['category_level'] = $this->getProductCategory($product, 'category_level');
        $data['category_path'] = $this->getProductCategory($product, 'category_path', $storeId);
        $data['price'] = $this->getPrices($product, $storeId);

        $additionalAttributes = $this->attributeHelper->getAdditionalAttributes($product);

        return array_merge($data, $additionalAttributes);
    }

    public function getAllImage($product)
    {
        $image = [];
        $image['base_image'] = $this->imageHelper->generateImage($product, 'image');
        $image['thumbnail_image'] = $this->imageHelper->generateImage($product, 'thumbnail');
        // $image['media_gallary']=$this->imageHelper->getMediaGallary($product);
        return $image;
    }

    public function getProductCategory($product, $value = 'category', $storeId = 1)
    {

        $getCategories = $product->getCategoryIds();
        $path = "";
        $categoryName = [];
        $categoryPath = [];
        $categoryLevel = [];
        foreach ($getCategories as $category) {
            $category = $this->objectManager->load($category);
            if ((int)$category->getLevel() > 1) {
                if ($path) $path .= "///" . $this->getFormattedString($category->getName());
                else $path = $this->getFormattedString($category->getName());
                $categoryName[] = $this->getFormattedString($category->getName());
                $categoryPath[] = $path;
                $categoryLevel[] = $category->getLevel();

            }
        }
        switch ($value) {
            case "category":
                return $categoryName;
                break;
            case "category_level":
                return $categoryLevel;
                break;
            case "category_path":
                return $categoryPath;
                break;
        }
        return false;
    }
}