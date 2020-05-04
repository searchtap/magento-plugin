<?php

namespace Bitqit\Searchtap\Model;

use Magento\Framework\Model\AbstractModel;
use \Bitqit\Searchtap\Model\ConfigurationFactory;

class Configuration extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Define resource model
     */
    const CACHE_TAG = 'searchtap_config';

    const TOKEN = "api_token";
    const DATACENTERS = "store_datacenter";

    protected $configurationFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ConfigurationFactory $configurationFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Configuration');
        $this->configurationFactory = $configurationFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Configuration');
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getToken()
    {
        $token = null;
        if($this->isDataExists()){
            $collection = $this->configurationFactory->create()
                ->getCollection()
                ->load();
            foreach ($collection as $config) {
                $token = $config->getData(self::TOKEN);
            }
        }
        return $token;
    }

    public function getDataCenters()
    {
        $collection = $this->configurationFactory->create()
            ->getCollection()
            ->load();

        $dataCenters = null;

        foreach ($collection as $config) {
            $dataCenters = $config->getData(self::DATACENTERS);
        }

        return json_decode($dataCenters, true);
    }

    public function setToken($token)
    {
        $this->setData(self::TOKEN, $token);
        return $this;
    }

    public function setDataCenters($dataCenters)
    {
        $this->setData(self::DATACENTERS, json_encode($dataCenters));
        return $this;
    }

    public function isDataExists()
    {
        $collection = $this->configurationFactory->create()
            ->getCollection()
            ->load();

        foreach ($collection as $entity)
            if ($entity) return $entity;

        return 0;
    }

    public function setConfiguration($token = null, $dataCenters = null) {
        $data = $this->configurationFactory->create();

        if ($token) $data->setToken($token);
        if ($dataCenters) $data->setDataCenters($dataCenters);

        $entity = $this->isDataExists();
        if ($entity) $data->setId($entity->getId());

        $data->save();
    }

}