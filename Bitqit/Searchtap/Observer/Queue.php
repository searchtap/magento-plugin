<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Model\AbstractModel;
use Magento\Catalog\Test\Fixture\Category\StoreId;

class Queue extends \Magento\Framework\Model\AbstractModel
{
    private $getconfig;
    protected $storeManager;
    protected $_curlRequest;
    const ADD_CATEGORIES =  'add_categories';
    const DEL_CATEGORIES =  'delete_categories';
    const STATUS_PENDING =  'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETE=  'complete';
    const UPDATE_PRODUCT= 'update_product';
    const ENTITY_TYPE_PRODUCT='product';
    const ENTITY_TYPE_CATEGORY='category';

    public static $processTypes=[
     self::ADD_CATEGORIES,
     self::DEL_CATEGORIES,
     self::STATUS_PENDING,
     self::STATUS_PROCESSING,
     self::STATUS_COMPLETE,
     self::ENTITY_TYPE_CATEGORY,
     self::ENTITY_TYPE_PRODUCT,
    ];

     public function __construct(
         \Bitqit\Searchtap\Helper\getConfigValue $config,
         \Magento\Store\Model\StoreManagerInterface $storeManager,
         \Magento\Catalog\Model\ProductFactory $productFactory,
         \Magento\Catalog\Model\CategoryFactory $categoryFactory,
         \Magento\Cms\Model\PageFactory $pageFactory,
         \Magento\Catalog\Model\ProductFactory $catalogProductFactory,
         \Magento\Framework\Model\Context $context,
         \Magento\Framework\Registry $registry,
         \Magento\Framework\App\ResourceConnection $resourceConnection,
         \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
         \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
         array $data = [])
     {
         $this->getconfig=$config;
         $this->storeManager=$storeManager;
     //    $this->_curlRequest=$curl;
         parent::__construct($context, $registry, $resource, $resourceCollection, $data);
     }


    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\Queue');
    }

    /*
     * addCategory() used for both add/update
     * @ Searchtap
     *
     */
    public function addCategory($category = null,$isActive,$getstoreId, $process = self::ADD_CATEGORIES,$type=self::ENTITY_TYPE_CATEGORY)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info("Queue Action value: ". self::ADD_CATEGORIES);
      /* Checks for category */
        if($category)
        {
            $IncludeInMenuConf=$this->getconfig->categoryIncludeInMenu;
            $EmptyOptionConf=$this->getconfig->emptycategoryoption;
            $skipCategoryId=$this->getconfig->skipCategoryIds;
            if($skipCategoryId){
             if(!in_array($category,$skipCategoryId)){
                 if($isActive) // index only Active Category
                 {
                     if($IncludeInMenuConf || $EmptyOptionConf)
                     {
                         $categoryId=$category->getId();
                         $this->doProcess($categoryId,$process,$type,$getstoreId);
                     }
                 }
                 else{
                    return false;
                 }
             }


            }

            $this->doProcess($category,$process,$getstoreId);

        }

    }

    public function addProducts($products,$storeId, $process = self::UPDATE_PRODUCT,$type=self::ENTITY_TYPE_PRODUCT)
    {
        /* Checks for category */


        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info("Inside Add Product and process is : ". $process);
        //$this->doProcess($products,$process,$storeId);
        $this->logger->info("Failed : ");
        if(!empty($products))
        {
            foreach ($products as $product){

                $this->doProcess($product,$process,$type,$storeId);
            }

          //  $this->doProcess( $products,$process,$storeId);
//          $productIds = [];
//          foreach ($products as $product){
//             $productIds[]=$product;
//          }
//            if (!empty($productIds)) {
//                $this->doProcess( $productIds,$process,$storeId);
//            }

        }

    }

    public function doProcess($value,$action,$type,$storeIds=null,$singleStore=null){
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info("IN DoProcess : ". $value);
        $this->logger->info("Store Id".$storeIds);
        $this->logger->info("Type Value".$type);
        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $model = $objectManager->create('Bitqit\Searchtap\Model\Queue');

        if(is_array($value)){
            $this->logger->info("check Array : yes");

            foreach ($value as $val){
                $st_queue=array(
                    'entity_id'=>$val,
                    'action' => $action,
                    'Status'=> self::STATUS_PENDING,
                    'type'=>$type,
                    'store'=>$storeIds
                );
                $model->setData($st_queue)->save();
            }
        }
        else {

        try {
            $this->logger->info("Going to Save Category Data ". $value);
            if(empty($this->getEntity($value))) {
                $st_queue = array(
                    'entity_id' => $value,
                    'action' => $action,
                    'Status' => self::STATUS_PENDING,
                    'type' => $type,
                    'store' => $storeIds
                );
                $model->setData($st_queue)->save();
            }
            $this->logger->info("Data Save using Sigle Value : ". $value);
          //  $this->getEntity($value);
        } catch (error $error) {
            $this->logger->info("Cache Part Executed !!! ");
            $this->logger->info($error);

        }

        }
       // print_r($st_quee);
      //$getStore=$this->getconfig->getStores(!empty($storeids)?$storeids:$singleStore);

   //   $data=Array($value);

/*
      foreach ($getStore as $store){
          $storesID[]=$store->getId();
      }

      foreach ($data as $val) {
          $this->logger->info("Vakue agsin : ". $val);
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
*/
    }

   public function getEntity($entityId){
       $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
       $this->logger = new \Zend\Log\Logger();
       $this->logger->addWriter($writer);
       $getResult="http://206.189.135.94/s3/searchtap?entityId=".$entityId;
       $curl = new \Magento\Framework\HTTP\Client\Curl;
       $curl->get($getResult);
       //response will contain the output in form of JSON string
       $response = $curl->getBody();
       $this->logger->info("Processing Curl Request for ".$response);
       return $response;
   }



}
