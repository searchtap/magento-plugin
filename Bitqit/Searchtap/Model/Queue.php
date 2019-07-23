<?php

namespace Bitqit\Searchtap\Model;

use \Magento\Framework\Model\AbstractModel;
use phpDocumentor\Reflection\Types\This;

class Queue extends \Magento\Framework\Model\AbstractModel
    implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'searchtap_queue';

    protected $_cacheTag = 'searchtap_queue';

    protected $_eventPrefix = 'searchtap_queue';

    const ENTITY_ID = 'entity_id';
    const ACTION = 'action';
    const STATUS = 'status';
    const TYPE = 'type';
    const STORE = 'store';

    private $queueFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->queueFactory = $queueFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Queue');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function setEntityId($entityId)
    {
        $this->setData(self::ENTITY_ID, $entityId);
        return $this;
    }

    public function setAction($action)
    {
        $this->setData(self::ACTION, $action);
        return $this;
    }

    public function setStatus($status)
    {
        $this->setData(self::STATUS, $status);
        return $this;
    }

    public function setType($type)
    {
        $this->setData(self::TYPE, $type);
        return $this;
    }

    public function setStore($storeId)
    {
        $this->setData(self::STORE, $storeId);
        return $this;
    }

    public function addToQueue($entityId, $action, $status, $type, $storeId)
    {
            $data = $this->queueFactory->create()
                ->setAction($action)
                ->setStatus($status);

            $entity = $this->isDataExists($entityId, $type, $storeId);

            if ($entity)
                $data->setId($entity->getId());

            else $data->setEntityId($entityId)
                ->setType($type)
                ->setStore($storeId);

            $data->save();
    }

    public function isDataExists($entityId, $type, $storeId)
    {
        $collection = $this->queueFactory->create()
            ->getCollection()
            ->addFilter('entity_id', $entityId)
            ->addFilter('type', $type)
            ->addFilter('store', $storeId)
            ->load();

        foreach ($collection as $entity)
            if ($entity)
                return $entity;

        return 0;
    }
}