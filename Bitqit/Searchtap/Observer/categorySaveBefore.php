<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class categorySaveBefore implements \Magento\Framework\Event\ObserverInterface
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
        $this->catSaveBefore( $this->category);
    }

    public function catSaveBefore($categoryBefore)
    {


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

}
