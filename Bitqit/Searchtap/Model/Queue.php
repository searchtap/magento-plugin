<?php

namespace Bitqit\Searchtap\Model;

class Queue extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'searchtap_queue';

    protected $_cacheTag = 'searchtap_queue';

    protected $_eventPrefix = 'searchtap_queue';

    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Queue');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}