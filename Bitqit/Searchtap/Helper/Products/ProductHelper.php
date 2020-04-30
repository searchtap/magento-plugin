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
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableModel;
use Magento\Bundle\Model\Product\Type as BundleModel;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedModel;

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
    private $configurableModel;
    private $bundleModel;
    private $groupedModel;

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
        Data $dataHelper,
        ConfigurableModel $configurableModel,
        BundleModel $bundleModel,
        GroupedModel $groupedModel
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
        $this->configurableModel = $configurableModel;
        $this->bundleModel = $bundleModel;
        $this->groupedModel = $groupedModel;
    }

    public function getProductCollection($storeId, $count, $page, $productIds = null)
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStore($storeId);
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', ['eq' => 1]);
        $collection->addAttributeToFilter('visibility', ['neq' => 1]);
        $collection->setFlag('has_stock_status_filter', false);
//        $collection->addMinimalPrice();
//        $collection->addFinalPrice();
        $collection->setPageSize($count);
        $collection->setCurPage($page);

        if ($productIds)
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }

    public function getProductsJSON($token, $storeId, $count, $page, $imageConfig, $productIds)
    {
        if (!$this->dataHelper->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

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

        return $this->searchtapHelper->okResult($data, $productCollection->getSize());
    }

    public function getFormattedString($string)
    {
        return $this->searchtapHelper->getFormattedString(str_replace("\r\n", "", $string));
    }

    protected function getStockData($product)
    {
        if ($product->isSalable()) {
            return $this->stockRepository->getStockItem($product->getId())->getIsInStock();
        }

        return false;
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
        $data["in_stock"] = $this->getStockData($product);

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
        if ($product->getTypeId() === \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
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
        $regularPrice = $this->searchtapHelper->getFormattedPrice($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue());
        $specialPrice = $this->searchtapHelper->getFormattedPrice($product->getFinalPrice());

        $priceObject = $product->getPriceInfo()->getPrice('final_price');
        $priceMin = $priceObject->getMinimalPrice()->getValue();
        $priceMax = $priceObject->getMaximalPrice()->getValue();

//        $priceMin = $this->searchtapHelper->getForm                                                                                   attedPrice($product->getMinPrice());
//        $priceMax = $this->searchtapHelper->getFormattedPrice($product->getMaxPrice());

        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
//        $currencySymbol = $this->getCurrencySymbol($storeId);
//        $formattedPrice = $this->getFormattedPrice($regularPrice, $specialPrice, $currencySymbol, $priceMin, $priceMax);

        $data = [
            "price" => $regularPrice,
            "special_price" => $specialPrice,
            "currency_symbol" => $this->getCurrencySymbol($storeId),
            "special_from_date" => $specialFromDate ? strtotime($specialFromDate) : false,
            "special_to_date" => $specialToDate ? strtotime($specialToDate) : false,
            "discount" => $this->getDiscountPercentage($regularPrice, $specialPrice),
            "min_price" => $priceMin,
            "max_price" => $priceMax,
            "price_type" => $product->getPriceType(),
            "price_view" => $product->getPriceView()
        ];

//        $productType = $product->getTypeId();
//
//        if ($productType === "simple" || $productType === "configurable" || $productType === "downloadable") {
//            $data['price'] = $formattedPrice['regular_price'];
//
//            if ($specialPrice && $specialPrice !== $regularPrice) {
//                $data['price'] = $formattedPrice['special_price'];
//                $data['original_price'] = $formattedPrice['regular_price'];
//            }
//        } else {
//            if ($product->getPriceType() == 0)
//                $data['price'] = $formattedPrice['price_range'];
//            else $data['price'] = $formattedPrice['special_price'];
//        }

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

    public function getReindexIds($storeId, $count, $page, $token)
    {
        if (!$this->dataHelper->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

        if (!$this->dataHelper->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        if (!$this->dataHelper->isStoreAvailable($storeId)) {
            return $this->searchtapHelper->error("store not found for ID " . $storeId, 404);
        }

        //Start Frontend Emulation
        $this->searchtapHelper->startEmulation($storeId);
        $productCollection = $this->getProductCollection($storeId, $count, $page);
        $data = [];

        foreach ($productCollection as $product) {
            $data[] = $product->getId();
        }

        return $this->searchtapHelper->okResult($data, $productCollection->getSize());
    }

    public function getConfigurableProductIdFromChildProduct($productId)
    {
        $parent = $this->configurableModel->getParentIdsByChild($productId);
        if ($parent) return $parent[0];
        return 0;
    }

    public function getBundleProductIdFromSimpleProduct($productId)
    {
        $bundleProduct = $this->bundleModel->getParentIdsByChild($productId);
        if ($bundleProduct) return $bundleProduct[0];
        return 0;
    }

    public function getGroupedProductIdFromSimpleProduct($productId) {
        $groupedProduct = $this->groupedModel->getParentIdsByChild($productId);
        if ($groupedProduct) return $groupedProduct[0];
        return 0;
    }
}