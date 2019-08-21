<?php

namespace Bitqit\Searchtap\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface ;
use \Magento\Store\Model\StoreManagerInterface ;

class ConfigHelper
{
    const PRIVATE_TOKEN = 'searchtap_credentials/credentials/private_token';

    private $configInterface;
    private $storeManager;

    public function __construct(
        ScopeConfigInterface $configInterface,
        StoreManagerInterface $storeManager
    )
    {
        $this->configInterface = $configInterface;
        $this->storeManager = $storeManager;
    }

    public function getPrivateToken($storeId) {
        return $this->configInterface->getValue(self::PRIVATE_TOKEN, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getAllStoreIds()
    {
        $storeIds = [];

        $storeCollection = $this->storeManager->getStores();

        foreach ($storeCollection as $store)
        {
            $storeIds[] = $store->getId();
        }

        return $storeIds;
    }

    public function isStoreAvailable($storeId)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store)
            if ($store->getId() == $storeId)
                return true;

        return false;
    }

    public function getEnabledStoresForIndexing($storeId = 0)
    {
        $enabledStoreIds = [];

//       if (!$storeId)
//       {
//           $stores = $this->getAllStoreIds();
//           foreach ($stores as $store)
//               if ($this->isIndexingEnabled($store))
//                   $enabledStoreIds[] = $store;
//       }
//       else if ($this->isIndexingEnabled($storeId))
//           $enabledStoreIds[] = $storeId;

       return $enabledStoreIds;
    }
}