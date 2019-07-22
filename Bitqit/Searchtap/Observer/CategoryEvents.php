<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class CategoryEvents implements \Magento\Framework\Event\ObserverInterface
{
    protected $queue;

    protected $productsInCategory = [];
    protected $productId=[];

    protected $category;
    protected $registry;
    protected $request;
    protected $isactive;
    protected $getstoreId;

    public function __construct(\Bitqit\Searchtap\Observer\Queue $queue)
    {
        $this->queue=$queue;
    }
    private function getMethodName(\Magento\Framework\Event $event)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $event->getName()))));
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $this->category=$observer->getEvent()->getCategory();
        $this->isactive=$this->category->getIsActive();
        $this->getstoreId=$this->category->getStoreId();
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        $this->logger->info("Store Id ".$this->getstoreId);
        $method_name = $this->getMethodName($observer->getEvent());

        if (method_exists($this, $method_name)) {
            $this->{$method_name}($this->category->getId(),$this->isactive,$this->getstoreId);
        }
        //  $this->catalogCategorySaveBefore($this->category->getId(),$this->isactive,$this->getstoreId);

    }
    public function catalogCategorySaveBefore($categoryId,$isActive,$getStoreId)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);
        if ($categoryId) {
            // For category
            $this->queue->addCategory($categoryId,$isActive,$getStoreId);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categories = $categoryFactory->create()->setStoreId($getStoreId)->load($categoryId); //categories from current store will be fetched
            $categoryProducts = $categories->getProductCollection()->addAttributeToSelect('*');
            foreach ($categoryProducts as $product) {
                $this->productId[]=$product->getId();
            }
            $this->queue->addProducts($this->productId,$getStoreId);

            /*  if (!empty($this->productId)) {
                  foreach ($this->productId as $product) {
                      $this->productsInCategory[]= $product;
                  }
              }*/
            //  Mage::getSingleton('customer/session')->setData( 'productSaveBefore', $this->productsInCategory);
        }
        unset($this->productId);
        return $this;
        //   return  $this->productsInCategory;
    }

    public function catalogCategorySaveAfter($categoryAfter,$isactive,$getStoreId)
    {

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/searchtap.log');
        $this->logger = new \Zend\Log\Logger();
        $this->logger->addWriter($writer);

        $this->logger->info(" Save After ".$categoryAfter);
        //  $this->queue->addCategory($categoryAfter,$isactive,$getStoreId);
        if ($categoryAfter) {

            // For Category evant failsafe
            // $this->queue->addCategory($categoryAfter,$isactive,$getStoreId);

            // For products from category

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categories = $categoryFactory->create()->setStoreId($getStoreId)->load($categoryAfter); //categories from current store will be fetched
            $categoryProducts = $categories->getProductCollection()->addAttributeToSelect('*');
            foreach ($categoryProducts as $product) {
                $this->productId[]=$product->getId();
            }

            if(!empty($this->productId)){
                if($this->productsInCategory){
                    //  $this->queue->addProducts($this->productId,$getStoreId);
                }
                else{
                    $pids=[];
                    foreach ($this->productId as $product)
                    {
//                        if(($product) && (!in_array($product, $this->productsInCategory)))
//                        {
//                            $pids[]=$product;
//                        }
                    }
                }
                //  $this->queue->addProducts($this->productId,$getStoreId);

            }

        }

        unset($this->productIdsInCategory);
        unset($this->productId);
        return $this;
    }

    public function catalogCategoryMoveAfter($categoryId,$isactive,$getStoreId)
    {

        if ($categoryId) {


            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
            $categories = $categoryFactory->create()->setStoreId($getStoreId)->load($categoryId); //categories from current store will be fetched
            $categoryProducts = $categories->getProductCollection()->addAttributeToSelect('*');
            foreach ($categoryProducts as $product) {
                $this->productId[]=$product->getId();
            }

            if ($this->productId) {
                $this->queue->addProducts($this->productId,$getStoreId);
            }
        }

        return $this;
    }



}
