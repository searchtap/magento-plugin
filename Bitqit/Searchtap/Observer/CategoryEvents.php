<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class CategoryEvents implements \Magento\Framework\Event\ObserverInterface
{
    private $queue;

    private $productsInCategory=[];


    private $category = null;

    private function getMethodName(\Magento\Framework\Event $event)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $event->getName()))));
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $method_name = $this->getMethodName($observer->getEvent());
        if (method_exists($this, $method_name)) {
            $this->{$method_name}($observer->getEvent());
        }

    }
    


    /********************************
     * Category events
     ********************************/
    /*
     * Category save before event
     * @ Searchtap
     * instance : Bitqit/Searchtap/Observer/catBeforeAction (used in adminhtml/events.xml)
     */

    public function __construct(\Bitqit\Searchtap\Observer\Queue $queue)
    {
       $this->queue=$queue;

    }
    public function catalogCategorySaveBefore(\Magento\Framework\Event $event)
    {
        $categoryBefore = $event->getCategory();

        if ($categoryBefore) {

            // For category
            $this->queue->addCategory($categoryBefore);

            $products = $categoryBefore->getProductCollection();

            $this->queue->addProducts($products); // save all productid into queue

            // Save all products id to verify with save_After Action

            if ($products) {
                foreach ($products as $product) {
                        $this->productsInCategory[]= $product->getId();
                     }
            }
        }
        return $this;
    }


    /*
     * Category save After event
     * @ Searchtap
     * instance : Bitqit/Searchtap/Observer/catAfterAction (used in adminhtml/events.xml)
     */

    public function catalogCategorySaveAfter(\Magento\Framework\Event $event)
    {
        $categoryAfter = $event->getCategory();
        if ($categoryAfter) {

            // For Category evant failsafe
            $this->queue->addCategory($categoryAfter);

            // For products from category
            $products = $categoryAfter->getProductCollection();

            if($products){
                if($this->productsInCategory){
                    $this->queue->addProducts($products);
                }
                else{
                    $pids=[];
                    foreach ($products as $product)
                    {
                       $id=$product->getId();

                       if(($id) && (!in_array($id, $this->productsInCategory)))
                       {
                           $pids[]=$id;
                       }
                    }
                }
                $this->queue->addProducts($pids);

            }

        }

        unset($this->productIdsInCategory);

        return $this;
    }

    /*
     * Category Move event
     * @ Searchtap
     * instance : Bitqit/Searchtap/Observer/catMoveAction (used in adminhtml/events.xml)
     */

    private function catalogCategoryMoveAfter(\Magento\Framework\Event $event)
    {
        $category = $event->getCategory();

        if ($category) {

            $products = $category->getProductCollection();

            if ($products) {
                $this->queue->addProducts($products);
            }
        }

        return $this;
    }



    /*
      * Delete Category Before event
      * @ Searchtap
      * instance : Bitqit/Searchtap/Observer/catMoveAction (used in adminhtml/events.xml)
      */


    private function catalogCategoryDeleteBefore(\Magento\Framework\Event $event)
    {
        $category = $event->getCategory();

        if ($category && $category->getId()) {
            // For category
            $this->queue->addCategory($category,\Searchanise\SearchAutocomplete\Model\Queue::DEL_CATEGORIES);

            // For products from category
            $products = $category->getProductCollection();
            if($products){
                $this->queue->addProducts($products);
            }
        }

        return $this;
    }

    

}