<?php

namespace Bitqit\Searchtap\Model;
use Magento\Framework\Model\AbstractModel;

class Queue extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface 
{
    const CACHE_TAG = 'searchtap_queue';

    protected $_cacheTag = 'searchtap_queue';

    protected $_eventPrefix = 'searchtap_queue';

    protected function _construct()
    {
        $this->_init(\Bitqit\Searchtap\Model\ResourceModel\Queue::class);
    }

    public function getIdentities()
    {
       return [self::CACHE_TAG . '_'. $this->getId()];

    }

    public function getDefaultValues()
    {
     $value=[];

    return $values;
   }
}
