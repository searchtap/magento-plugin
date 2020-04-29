<?php

namespace Bitqit\Searchtap\Model\ResourceModel\Configuration;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'searchtap_config_collection';
    protected $_eventObject = 'config_collection';

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\Configuration', 'Bitqit\Searchtap\Model\ResourceModel\Configuration');
    }
}