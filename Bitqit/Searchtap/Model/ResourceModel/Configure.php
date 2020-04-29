<?php

namespace Bitqit\Searchtap\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Configure extends AbstractDb
{
    /**
     * Define main table
     */

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    protected function _construct()
    {
        $this->_init('bitqit_searchtap_config', 'id');
    }
}
