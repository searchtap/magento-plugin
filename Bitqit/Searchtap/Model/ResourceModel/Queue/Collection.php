<?php
namespace Bitqit\Searchtap\Model\ResourceModel\Queue;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'searchtap_queue_collection';
    protected $_eventObject = 'queue_collection';

    public function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\Queue', 'Bitqit\Searchtap\Model\ResourceModel\Queue');
    }

}
