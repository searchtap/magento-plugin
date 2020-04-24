<?php

namespace Bitqit\Searchtap\Model;

use Magento\Framework\Model\AbstractModel;

class Configure extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Configure');
    }
}