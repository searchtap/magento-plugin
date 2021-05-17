<?php

namespace Bitqit\Searchtap\Model\ResourceModel\Configuration;

//use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\Configuration','Bitqit\Searchtap\Model\ResourceModel\Configuration');
    }
}