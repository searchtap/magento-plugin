<?php

namespace Bitqit\Searchtap\Helper\Products;

class ProductHelper{

    private $requiredAttributes;
    private $configHelper;
    private $imageHelper;
    private $searchtapHelper;
    private $productCollectionFactory;
    private $currencyFactory;
    private $objectManager;
    private $categoryHelper;
    private $productImageHelper;
    private $categoryRepository;
    private $productRepositry;
    private $storeManager;

    public function __construct(\Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
                                \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
                                \Bitqit\Searchtap\Helper\Products\ImageHelper $imageHelper,
                                \Magento\Catalog\Model\Category $categoryManager,
                                \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper,
                                \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
                                \Magento\Catalog\Model\ProductRepository $productRepository,
                                \Magento\Catalog\Helper\Image $productImageHelper,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Directory\Model\Currency $currencyFactory

    )
    {
        $this->imageHelper=$imageHelper;
        $this->searchtapHelper=$searchtapHelper;
        $this->configHelper=$configHelper;
        $this->productCollectionFactory=$productFactory;
        $this->objectManager=$categoryManager;
        $this->categoryHelper=$categoryHelper;
        $this->productImageHelper=$productImageHelper;
        $this->categoryRepository=$categoryRepository;
        $this->productRepositry=$productRepository;
        $this->storeManager=$storeManager;
        $this->currencyFactory=$currencyFactory;
    }

    public function getProductCollection($storeId,$productIds=null,$offset=null,$count=null){

        $collection = $this->productCollectionFactory->create();
        $collection->setStore($storeId);
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('status', ['eq' => 1]);
        $collection->addAttributeToFilter('visibility',['neq'=>1]);
        $collection->setPageSize($offset);
        $collection->setCurPage($count);

        if ($productIds)
            $collection->addAttributeToFilter('entity_id', ['in' => $productIds]);

        return $collection;
    }

    public function getProductsJSON($storeId,$productIds=null,$offset=null,$count=null){
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
            $data[] = $this->getProductObject($product,$storeId);
        }
        //Stop Emulation
        $this->searchtapHelper->stopEmulation();

        return json_encode($data);
    }

    public function getFormattedString($string)
    {
        return $this->searchtapHelper->getFormattedString($string);
    }
    public function getProductObject($product,$storeId)
    {
        $data=[];
        $child_attribute=[];
        $data['id']=$product->getId();
        $data['name']=$this->getFormattedString($product->getName());
        $data['URL']=$product->getProductUrl();
        $data['status']=$product->getStatus();
        $data['visibility']=$product->getVisibility();// need todo
        $data['type']=$product->getTypeId();
        $data['price']=$this->getPrices($product,$storeId);
        $data['currency_symbol']=$this->getCurrencySymbol($storeId);
        $data['description']=$this->getFormattedString(str_replace("\r\n","",$product->getDescription()));
        $data['base_image']=$this->getFormattedString($this->imageHelper->generateImage($product,'image'));
        $data['thumbnail_image']=$this->getFormattedString($this->imageHelper->generateImage($product,'thumbnail'));
        $data['created_at']=$product->getCreatedAt();
        $data['SKU']=$this->getProductsSku($product);
        $data['category']=$this->getProductCategory($product,'category');
        $data['categoryIds']=$this->getProductCategory($product,'category_Ids');

        if($product->getTypeId()==="configurable"){
            $child_attribute[]=$this->getChildAttribute($product);
        }
        foreach($child_attribute as $child)
        {
            $data=array_merge($data,$child);
        }

        return $data;
    }

    public function getPrices($product, $storeId)
    {
        //todo: Index the prices based on customer group in Phase 3
        //todo: Index the tier prices in Phase 4
        $regularPrice = $this->searchtapHelper->getFormattedPrice($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue());
        $specialPrice = $this->searchtapHelper->getFormattedPrice($product->getFinalPrice());
        $bundleObj=$product->getPriceInfo()->getPrice('final_price');
        $priceMin = $bundleObj->getMinimalPrice();
        $priceMax = $bundleObj->getMaximalPrice();
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

      //  var_dump(json_encode($data,JSON_UNESCAPED_UNICODE));
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

    public function getAllImage($product){
        $image=[];
        $image['base_image']=$this->imageHelper->generateImage($product,'image');
        $image['thumbnail_image']=$this->imageHelper->generateImage($product,'thumbnail');
        return $image;
    }

    public function getProductCategory($product,$value='category'){

       $getCategories=$product->getCategoryIds();
       foreach ($getCategories as $categoryId)
        {
                $category=$this->objectManager->load($categoryId);
                $getPaths=$category->getPath();
                $categoryLevel = $category->getLevel();
                $level=(int)$categoryLevel-1;
                if($category->getIsActive()==1 && $category->getLevel()>=2)
                       $mapedCategory['Level_'.$level][]=$category->getName(); // Get all maped Category Information

        }
        switch ($value){
            case "category":
                return $mapedCategory;
                unset($mapedCategory);
                break;
            case "category_Ids":
                return $getCategories;
                unset($getCategories);
                break;

        }
      return false;
    }



    public function getProductsSku($product)
    {
        $productSKU=[];

        switch($product->getTypeId()){
            case 'downloadable':
            case 'bundle':
            case 'virtual':
            case 'simple':
                    return $product->getSKU();
                    break;
            case 'configurable':
                   $i=0;
                   $productSKU[]=$product->getSKU();
                   $variationProduct= $product->getTypeInstance()->getUsedProducts($product);
                   foreach ($variationProduct as $child){
                      $productSKU[]=$child->getSku();
                   }
                   return $productSKU;
                   break;

            case 'grouped':

                $groupedProductSKU[]=$product->getSKU();

                $groupedProducts=$product->getTypeInstance(true)->getAssociatedProducts($product);
                foreach ($groupedProducts as $child)
                {
                    $groupedProductSKU[]=$child->getSKU();
                }
                return $groupedProductSKU;
                break;
        }

        return false;
    }

    public function getChildAttribute($product)
    {
        $childAttribute=[];
        $data = $product->getTypeInstance()->getConfigurableOptions($product);

        $options = array();
        $attributes = [];
        foreach($data as $attr){
            foreach($attr as $p) {
                $options[$p['sku']][$p['attribute_code']] = $p['option_title'];
                $configurableAttributes[$p['attribute_code']][] = $p['option_title'];

            }
        }
        foreach($options as $sku =>$d){

            $pr = $this->productRepositry->get($sku);
            foreach($d as $k => $v)
                 if($k=="color"){
                     $childColorAtrribute[$v]=$this->productImageHelper->init($product,'product_base_image')->setImageFile($pr->getImage())->resize(300, 300)->getUrl();
                 }
        }
        foreach ($configurableAttributes as $key=>$value){
            $childAttribute[$key]=array_values(array_filter(array_unique($value)));

        }
        if(!empty($childColorAtrribute)) {
            $childAttribute['media_images'] = $childColorAtrribute;
            return array_merge($configurableAttributes, $childAttribute);
        }

     return $childAttribute;
    }


}