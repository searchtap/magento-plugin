<?php

namespace Bitqit\Searchtap\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use Bitqit\Searchtap\Model\ConfigurationFactory;


class ConfigHelper
{
    const PRIVATE_TOKEN = 'searchtap_credentials/credentials/st_credentials_keys';
    const JS_CONFIG = 'searchtap_credentials/credentials/st_config';
    const SCRIPT_URL = 'searchtap_credentials/credentials/st_script_url';
    const CSS_URL = 'searchtap_credentials/credentials/st_css_url';

    private $configInterface;
    private $storeManager;
    private $searchtapHelper;
    private $_configFactory;

    public function __construct(
        ScopeConfigInterface $configInterface,
        StoreManagerInterface $storeManager,
        SearchtapHelper $searchtapHelper,
        ConfigurationFactory $configFactory
    )
    {
        $this->configInterface = $configInterface;
        $this->storeManager = $storeManager;
        $this->searchtapHelper = $searchtapHelper;
        $this->_configFactory=$configFactory;
    }

    public function getCredentials()
    {
        $credentials = $this->configInterface->getValue(self::PRIVATE_TOKEN);
        return json_decode($credentials);
    }

    public function getJsConfiguration()
    {
        return $this->configInterface->getValue(self::JS_CONFIG);
//        return json_decode($jsConfig);
    }

    public function getScriptUrl() {
        return $this->configInterface->getValue(self::SCRIPT_URL);
    }

    public function getCssUrl() {
        return $this->configInterface->getValue(self::CSS_URL);
    }

    public function getAllStoreIds()
    {
        $storeIds = [];

        $storeCollection = $this->storeManager->getStores();

        foreach ($storeCollection as $store) {
            $storeIds[] = $store->getId();
        }

        return $storeIds;
    }

    public function getStores()
    {
        return $this->storeManager->getStores();
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


    public function getAPIToken(){
        $configValue = $this->_configFactory->create()->getCollection();
        foreach ($configValue as $val){
            return $val->getAPIToken();
        }
        return;
    }

}