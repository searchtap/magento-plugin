<?php

namespace Bitqit\Searchtap\Helper\Products;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Bitqit\Searchtap\Helper\Data;
use \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use \Bitqit\Searchtap\Helper\Products\ImageHelper;
use \Bitqit\Searchtap\Helper\Categories\CategoryHelper;
use \Magento\Catalog\Model\ProductRepository;
use \Magento\Catalog\Helper\Image;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Directory\Model\Currency;
use \Bitqit\Searchtap\Helper\Products\AttributeHelper;
use \Magento\CatalogInventory\Api\StockRegistryInterface;

class ProductHelper
{
    private $attributeHelper;
    private $configHelper;
    private $imageHelper;
    private $searchtapHelper;
    private $productCollectionFactory;
    private $currencyFactory;
    private $categoryHelper;
    private $productImageHelper;
    private $productRepository;
    private $storeManager;
    private $stockRepository;
    private $dataHelper;

    public function __construct(
        ConfigHelper $configHelper,
        SearchtapHelper $searchtapHelper,
        CollectionFactory $productFactory,
        ImageHelper $imageHelper,
        CategoryHelper $categoryHelper,
        ProductRepository $productRepository,
        Image $productImageHelper,
        StoreManagerInterface $storeManager,
        Currency $currencyFactory,
        AttributeHelper $attributeHelper,
        StockRegistryInterface $stockRepository,
        Data $dataHelper
    )
    {
        $this->imageHelper = $imageHelper;
        $this->searchtapHelper = $searchtapHelper;
        $this->configHelper = $configHelper;
        $this->productCollectionFactory = $productFactory;
        $this->categoryHelper = $categoryHelper;
        $this->productImageHelper = $productImageHelper;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->attributeHelper = $attributeHelper;
        $this->stockRepository = $stockRepository;
        $this->dataHelper = $dataHelper;
    }

    public function getProductCollection($storeId, $count, $page, $productIds)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStore($storeId);
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', ['eq' => 1]);
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        $collection->addMinimalPrice();
        $collection->addFinalPrice();
        $collection->setPageSize($count);
        $collection->setCurPage($page);

        if ($productIds)
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }

    public function getProductsJSON($token, $storeId, $count, $page, $imageConfig, $productIds)
    {
        if (!$this->dataHelper->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        if (!$this->dataHelper->isStoreAvailable($storeId)) {
            return $this->searchtapHelper->error("store not found for ID " . $storeId, 404);
        }

        //Start Frontend Emulation
        $this->searchtapHelper->startEmulation($storeId);

        $productCollection = $this->getProductCollection($storeId, $count, $page, $productIds);

        $data = [];

        foreach ($productCollection as $product) {
            $data[] = $this->getProductObject($product, $storeId, $imageConfig);
        }

        //Stop Emulation
        $this->searchtapHelper->stopEmulation();

        return $this->searchtapHelper->okResult($data, count($data));
    }

    public function getFormattedString($string)
    {
        return $this->searchtapHelper->getFormattedString(str_replace("\r\n", "", $string));
    }

    public function getProductObject($product, $storeId, $imageConfig)
    {
        $data = [];

        //Product Basic Information
        $data['id'] = $product->getId();
        $data['name'] = $this->getFormattedString($product->getName());
        $data['url'] = $product->getProductUrl();
        $data['status'] = $product->getStatus();
        $data['visibility'] = $product->getVisibility();
        $data['type'] = $product->getTypeId();
        $data['created_at'] = strtotime($product->getCreatedAt());
        $data['sku'] = $this->getSKUs($product);
        $data['description'] = $this->getFormattedString($product->getDescription());
        $data['short_description'] = $this->getFormattedString($product->getShortDescription());

        //Product Stock Information
        $data["in_stock"] = $this->stockRepository->getStockItem($product->getId())->getIsInStock();

        //Product Price Information
        $data['price'] = $this->getPrices($product, $storeId);

        // Product Category Information
        $data['_category_ids'] = $product->getCategoryIds();

        $categoriesData = $this->categoryHelper->getProductCategories($product, $storeId);
        $data['_categories'] = $categoriesData["_categories"];
        $data["categories_path"] = $categoriesData["categories_path"];

        //Product Image Information
        $images = $this->imageHelper->getImages($imageConfig, $product);

        //Additional Attributes Information
        $additionalAttributes = $this->attributeHelper->getProductAdditionalAttributes($product);

        //Get Product Variations Information
        if ($product->getTypeId() === "configurable") {
            $associatedProducts = $this->getAssociatedProducts($product, $storeId);
            $data = array_merge($data, $associatedProducts);
        }

        $data = array_merge(
            $data,
            $images,
            $categoriesData["category_level"],
            $additionalAttributes
        );

        return $data;
    }

    public function getPrices($product, $storeId)
    {
        //todo: check the fixed or dynamic price concept for bundle products
        //todo: discounted price for bundle products

        $regularPrice = $this->searchtapHelper->getFormattedPrice($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue());
        $specialPrice = $this->searchtapHelper->getFormattedPrice($product->getFinalPrice());

        //todo: check for different versions
//      $bundleObj = $product->getPriceInfo()->getPrice('final_price');

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

        if ($productType === "simple" || $productType === "configurable" || $productType === "downloadable") {
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
        $currencyCode = $this->storeManager->getStore($storeId)->getCurrentCurrencyCode();
        $currency = $this->currencyFactory->load($currencyCode);

        return $currency->getCurrencySymbol();
    }

    public function getFormattedPrice($regularPrice, $specialPrice, $currencySymbol, $priceMin, $priceMax)
    {
        $data = [];

        $data['regular_price'] = $currencySymbol . $regularPrice;
        $data['special_price'] = $currencySymbol . $specialPrice;
        $data['price_range'] = $currencySymbol . $priceMin . " - " . $currencySymbol . $priceMax;

        return $data;
    }

    public function getAssociatedProducts($product, $storeId)
    {
        $associatedProducts = [];

        $options = $product->getTypeInstance()->getConfigurableOptions($product);

        foreach ($options as $option) {
            foreach ($option as $simple) {
                $product = $this->productRepository->get($simple['sku'], $storeId);
                $stockStatus = $this->stockRepository->getStockItem($product->getId())->getIsInStock();
                if ($stockStatus && $product->getStatus()) {
                    if (!array_key_exists($simple['attribute_code'], $associatedProducts)
                        || !in_array($simple['option_title'], $associatedProducts[$simple['attribute_code']]))
                        $associatedProducts[$simple['attribute_code']][] = $this->getFormattedString($simple['option_title']);
                }
            }
        }

        //todo: get images of child products that have color associated with them

        return $associatedProducts;
    }

    public function getSKUs($product)
    {
        $sku = [];

        switch ($product->getTypeId()) {
            case 'configurable':
                $sku[] = $product->getSKU();
                $variationProduct = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($variationProduct as $child) {
                    if ($child->getStatus())
                        $sku[] = $child->getSku();
                }
                break;
            case 'grouped':
                $sku[] = $product->getSKU();
                $groupedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($groupedProducts as $child) {
                    if ($child->getStatus())
                        $sku[] = $child->getSku();
                }
                break;
            case 'downloadable':
            case 'bundle':
            case 'virtual':
            case 'simple':
            default:
                $sku = $product->getSKU();
        }

        return $sku;
    }
}