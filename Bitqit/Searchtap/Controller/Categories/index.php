<?php

namespace Bitqit\Searchtap\Controller\Categories;
use Bitqit\Searchtap\Model\Queue;
use Bitqit\Searchtap\Helper;

class Index extends \Magento\Framework\App\Action\Action
{
    private $conf;
    private $stcurl;
    public $model;
    public function __construct(\Bitqit\Searchtap\Helper\getConfigValue $config,
                                \Magento\Framework\App\Action\Context $context,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->conf=$config;
     //   $this->model=$queueFactory;
        parent::__construct($context);
    }

    public function execute()
    {
       $this->stcurl = new Helper\searchtapCurl($this->conf->applicationId, $this->conf->collectionName, $this->conf->adminKey);
       $storeId=$this->getRequest()->getParam('sid',0);
       $categoriesIds=$this->getRequest()->getParam('catId',0);
       if($storeId)
       {
           if(!($categoriesIds)){
               $this->categoryJson($storeId);
           }
           else{
               $this->commaSeperatedIds($storeId,$categoriesIds);
           }
       }
       else
       {
           $path=19;
           $data=$this->model->create();
          // $collection = $data->getCollection()->addFieldToFilter('entity_id', array('in' => $path));
         //  print_r($collection->getData());
        //   print_r();
           echo "Please specify Store Id using (sid=1/2/3/..)";
       }

    }

    public function categoryJson($sid)
    {

     $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
     $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
     $categories = $categoryFactory->create()->addAttributeToSelect('*')->setStore($sid); //categories from current store will be fetched
     if(!$this->conf->inactiveCategoryOption)
         $categories->addAttributeToFilter('is_active',true);

        if ($this->conf->categoryIncludeInMenu)
            $categories->addAttributeToFilter('include_in_menu', array('eq' => 1));


        if ($this->conf->skipCategoryIds) {
            $cat_ids = explode(",", $this->skipCategoryIds);
            foreach ($cat_ids as $id)
                $categories->addAttributeToFilter('path', array('nlike' => "%$id%"));
        }
       // $meta_tags=Array();
        foreach ($categories as $category) {

                 $pathIds = explode('/', $category->getPath());
                 foreach ($pathIds as $path){
                 $collection = $categoryFactory->create()->setStoreId($sid)->addAttributeToSelect('name')->addFieldToFilter('entity_id', array('in' => $path));
                 $pathByName = '';

                 foreach ($collection as $cat) {
                     $pathByName .= $cat->getName();
                 }
                     $pathArray[] = $pathByName;
                 }

            $path=implode('|||',$pathArray);
            $meta_tags=explode(',',$category->getData('meta_keywords'));
            // For Active Product Count
            $products = Mage::getModel('catalog/category')->load($category->getId())
                ->getProductCollection()
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility',array("in" =>[2,3,4]));
            $productCount=$products->Count();

             $categoryArray[] = array(
                'id' => (int)$category->getId(),
                'name' => $category->getName(),
                'url' => $category->getUrl(),
                'is_active' => $category->getIsActive(),
                'include_in_menu' => $category->getIncludeInMenu(),
                'product_count' => $category->getProductCount(),
                'path' => $path,
                'description' => strip_tags($category->getDescription()),
                'meta_title' => strip_tags($category->getMetaTitle()),
                'meta_description' => strip_tags($category->getMetaDescription()),
                'meta_keywords' => array_filter(array_map('trim', $meta_tags)),
                'updated_date' => $category->getUpdatedAt(),
                'level'=>$category->getLevel(),
                'parent_id'=> $category->getParentId()

            );

       unset($pathArray);
       unset($meta_tags);
        }

        $categoryJson=json_encode($categoryArray);
        print_r($categoryJson);
      //  $response=$this->stcurl->searchtapCurlRequest($categoryJson);
        //echo $response;
        return true;
    }



    public function commaSeperatedIds($storeid,$categoryids){

        $categoryidsIds = explode(",", $categoryids);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $categoryFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryFactory->create()->addAttributeToSelect('*')->setStore($storeid)->addAttributeToFilter('entity_id', array('in' => $categoryids));
        foreach ($categories as $cat)
        {
            echo $cat->getName();
        }
       return true;
    }

}
