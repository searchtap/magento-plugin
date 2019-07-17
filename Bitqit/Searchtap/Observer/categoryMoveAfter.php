<?php

namespace Bitqit\Searchtap\Observer;

use Bitqit\Searchtap\Observer\Queue;

class categoryMoveAfter implements \Magento\Framework\Event\ObserverInterface
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
        $this->catMoveAfter( $this->category);
    }

    public function catMoveAfter($category)
    {

        if ($category) {

            $products = $category->getProductCollection();

            if ($products) {
                $this->queue->addProducts($products);
            }
        }

        return $this;
    }


}
