<?php

namespace Bitqit\Searchtap\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface ;
use \Magento\Store\Model\StoreManagerInterface ;

class ConfigHelper
{
    const APPLICATION_ID = 'searchtap_credentials/credentials/application_id';
    const WRITE_TOKEN = 'searchtap_credentials/credentials/write_token';
    const READ_TOKEN = 'searchtap_credentials/credentials/read_token';
    const ENABLE_INDEXING = 'searchtap_credentials/credentials/enable_indexing';
    const ENABLE_SEARCH = 'searchtap_credentials/credentials/enable_search';

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

    public function getApplicationID($storeId) {
        return $this->configInterface->getValue(self::APPLICATION_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getWriteToken($storeId) {
        return $this->configInterface->getValue(self::WRITE_TOKEN, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getReadToken($storeId) {
        return $this->configInterface->getValue(self::READ_TOKEN, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isIndexingEnabled($storeId) {
        return $this->configInterface->getValue(self::ENABLE_INDEXING, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isSearchEnabled($storeId)
    {
        return $this->configInterface->getValue(self::ENABLE_SEARCH, ScopeInterface::SCOPE_STORE, $storeId);
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

       if (!$storeId)
       {
           $stores = $this->getAllStoreIds();
           foreach ($stores as $store)
               if ($this->isIndexingEnabled($store))
                   $enabledStoreIds[] = $store;
       }
       else if ($this->isIndexingEnabled($storeId))
           $enabledStoreIds[] = $storeId;

       return $enabledStoreIds;
    }
}