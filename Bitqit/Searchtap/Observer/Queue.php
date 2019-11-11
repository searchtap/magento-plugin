<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Test\Fixture\Category\StoreId;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    private $getconfig;
    protected $storeManager;
    const ADD_CATEGORIES =  'add_categories';
    const DEL_CATEGORIES =  'delete_categories';
    const STATUS_PENDING =  'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE=  'complete';
    const UPDATE_PRODUCT= 'update_product';

    public static $processTypes=[
     self::ADD_CATEGORIES,
     self::DEL_CATEGORIES,
     self::STATUS_PENDING,
     self::STATUS_PROCESSING,
     self::STATUS_COMPLETE
    ];

     public function __construct(\Bitqit\Searchtap\Helper\getConfigValue $config,\Magento\Store\Model\StoreManagerInterface $storeManager)
     {
         $this->getconfig=$config;
         $this->storeManager=$storeManager;

     }

    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Queue');
    }
    /*
     * addCategory() used for both add/update
     * @ Searchtap
     *
     */
    public function addCategory(\Magento\Catalog\Model\Category $category = null, $process = self::ADD_CATEGORIES)
    {
      /* Checks for category */
        if($category)
        {
             $isActive= $category->getIsActive();
             $getstoreId = $category->getStoreId();
            // $InactiveCategoryConf=$this->getconfig->inactiveCategoryOption; // used in V2
             $IncludeInMenuConf=$this->getconfig->categoryIncludeInMenu;
             $EmptyOptionConf=$this->getconfig->emptycategoryoption;
             $skipCategoryId=$this->getconfig->skipCategoryIds;

             if(!in_array($category->getId(),$skipCategoryId)){
                 if($isActive) // index only Active Category
                 {
                     if($IncludeInMenuConf || $EmptyOptionConf)
                     {
                         $categoryId=$category->getId();
                         $this->doProcess($categoryId,$process,$getstoreId);
                     }
                 }
                 else{
                    return false;
                 }
             }
        }
    }

    public function addProducts( \Magento\Catalog\Model\ResourceModel\Product\Collection $products, $process = self::UPDATE_PRODUCT)
    {
        /* Checks for category */
        if($products)
        {
          $productIds = [];
          foreach ($products as $product){
             $productIds[]=$product->getId();
          }
            if (!empty($productIds)) {
                $this->doProcess( $productIds,$process);
            }
        }

    }

    public function doProcess($value=null,$action,$storeids=null,$singlestore=null){

      $getStore=$this->getconfig->getStores(!empty($storeids)?$storeids:$singlestore);
      $data=[$value];

      foreach ($getStore as $store){
          $storesID[]=$store->getId();
      }

      foreach ($data as $val) {
            foreach ($storesID as $storeId) {
                $st_queue = [
                    'entity_id'=>$val,
                    'action' => $action,
                    'status'=> self::STATUS_PENDING,
                    'type'=>'category',
                    'store'=>$storesID
                ];

                // TODO: Deprecated
                $this->setData($st_queue)->save();
            }
        }

    }


}