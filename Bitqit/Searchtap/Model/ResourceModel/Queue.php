<?php

namespace Bitqit\Searchtap\Model\ResourceModel;


class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('searchtap_queue', 'process_id');
    }

}