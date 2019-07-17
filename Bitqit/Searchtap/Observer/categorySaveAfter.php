<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class categorySaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $queue;

    protected $productsInCategory = [];


    protected $category = null;

    public function __construct(\Bitqit\Searchtap\Observer\Queue $queue)
    {
        $this->queue=$queue;

    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->category=$observer->getEvent()->getCategory();
        $this->catSaveAfter( $this->category);
    }

    public function catSaveAfter($categoryAfter)
    {

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

}
