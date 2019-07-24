<?php

namespace Bitqit\Searchtap\Helper;

use  \Magento\Store\Model\ScopeInterface;

class ConfigHelper
{
    const APPLICATION_ID = 'searchtap_credentials/credentials/application_id';
    const WRITE_TOKEN = 'searchtap_credentials/credentials/write_token';
    const READ_TOKEN = 'searchtap_credentials/credentials/read_token';
    const ENABLE_INDEXING = 'searchtap_credentials/credentials/enable_indexing';
    const ENABLE_SEARCH = 'searchtap_credentials/credentials/enable_search';
    const IMAGE_WIDTH='searchtap_credentials/image_config/image_width';
    const IMAGE_HEIGHT='searchtap_credentials/image_config/image_height';

    private $configInterface;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface
    )
    {
        $this->configInterface = $configInterface;
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

    public function isSearchEnabled($storeId) {
        return $this->configInterface->getValue(self::ENABLE_SEARCH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getImageWidth($storeId){
        return $this->configInterface->getValue(self::IMAGE_WIDTH, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getImageHeight($storeId){
        return $this->configInterface->getValue(self::IMAGE_HEIGHT, ScopeInterface::SCOPE_STORE, $storeId);
    }
    public function checkStoreAvailibity($storeId)
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create("\Magento\Store\Model\StoreManagerInterface");
        $stores = $storeManager->getStores(true, false);
        foreach ($stores as $store)
        {
            if($storeId===$store->getId()){
                return true;
            }
        }
        return false;
    }
}