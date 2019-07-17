<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class categoryDeleteBefore implements \Magento\Framework\Event\ObserverInterface
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
        $this->catDeleteBefore( $this->category);
    }

    public function catDeleteBefore($category)
    {

        if ($category && $category->getId()) {
            // For category
            $this->queue->addCategory($category,\Bitqit\Searchtap\Observer\Queue::DEL_CATEGORIES);

            // For products from category
            $products = $category->getProductCollection();
            if($products){
                $this->queue->addProducts($products);
            }
        }

        return $this;
    }


}
