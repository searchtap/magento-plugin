<?php

namespace Bitqit\Searchtap\Helper\Products;

class ProductHelper{

    private $requiredAttributes;
    private $configHelper;
    private $imageHelper;
    private $searchtapHelper;
    private $productCollectionFactory;
    private $discount_per;
    private $objectManager;
    private $categoryHelper;

    public function __construct(\Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
                                \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
                                \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productFactory,
                                \Bitqit\Searchtap\Helper\Products\ImageHelper $imageHelper,
                                \Magento\Catalog\Model\Category $objectManager,
                                \Bitqit\Searchtap\Helper\Categories\CategoryHelper $categoryHelper
    )
    {
        $this->imageHelper=$imageHelper;
        $this->searchtapHelper=$searchtapHelper;
        $this->configHelper=$configHelper;
        $this->productCollectionFactory=$productFactory;
        $this->objectManager=$objectManager;
        $this->categoryHelper=$categoryHelper;

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
        $data['id']=$product->getId();
        $data['name']=$this->getFormattedString($product->getName());
        $data['sku']=$product->getSKU();
        $data['URL']=$product->getProductUrl();
        $data['status']=$product->getStatus();
        $data['visibility']=$product->getVisibility();// need todo
        $data['type']=$product->getTypeId();
        $data['price']=$this->getAllPrice($product);
        $data['description']=$this->getFormattedString(str_replace("\r\n","",$product->getDescription()));
        $data['base_image']=$this->getFormattedString($this->imageHelper->generateImage($product,'image'));
        $data['thumbnail_image']=$this->getFormattedString($this->imageHelper->generateImage($product,'thumbnail'));
        $data['created_at']=$product->getCreatedAt();
        $data['category']=$this->getProductCategory($product,'category');
        $data['category_level']=$this->getProductCategory($product,'category_level');
        $data['category_path']=$this->getProductCategory($product,'category_path',$storeId);

        return $data;
    }

    public function getAllPrice($product){
        $price=[];
        $regular_price=$product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        if($regular_price)
            $this->discount_per=round((((float)$regular_price-$product->getSpecialPrice())/$regular_price)*100);


        $price['price']=(float)$regular_price;
        $price['discount_price']=$product->getSpecialPrice();;
        $price['discount_percentage']=$this->discount_per;
        $price['specialPriceFromDate']=$product->getSpecialFromDate();
        $price['specialPriceToDate']=$product->getSpecialToDate();
        $price['Max_Price']=$product->getMaxPrice();
        $price['Min_Price']=$product->getMinPrice();

        return $price;
    }

    public function getAllImage($product){
        $image=[];
        $image['base_image']=$this->imageHelper->generateImage($product,'image');
        $image['thumbnail_image']=$this->imageHelper->generateImage($product,'thumbnail');
        // $image['media_gallary']=$this->imageHelper->getMediaGallary($product);
        return $image;
    }

    public function getProductCategory($product,$value='category',$storeId=1){

        $getCategories=$product->getCategoryIds();
        $path = "";
        $categoryName=[];
        $categoryPath=[];
        $categoryLevel=[];
        foreach ($getCategories as $category)
        {
                $category=$this->objectManager->load($category);
                if((int)$category->getLevel()>1){
                if ($path) $path .= "///" . $this->getFormattedString($category->getName());
                else $path = $this->getFormattedString($category->getName());
                $categoryName[] = $this->getFormattedString($category->getName());
                $categoryPath[] = $path;
                $categoryLevel[] = $category->getLevel();

        }}
        switch ($value){
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

    public function getProductAttribute(){


    }

}