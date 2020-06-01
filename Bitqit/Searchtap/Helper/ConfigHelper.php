<?php

namespace Bitqit\Searchtap\Helper;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Bitqit\Searchtap\Model\ConfigurationFactory;


class ConfigHelper
{
    const PRIVATE_TOKEN = 'searchtap_credentials/credentials/st_credentials_keys';
    const JS_CONFIG = 'searchtap_credentials/credentials/st_config';
    const SCRIPT_URL = 'searchtap_credentials/credentials/st_script_url';
    const CSS_URL = 'searchtap_credentials/credentials/st_css_url';
    const AUTOCOMPLETE_CUSTOM_CSS = 'searchtap_credentials/credentials/st_autocomplete_custom_css';
    const SEARCH_PAGE_CUSTOM_CSS = 'searchtap_credentials/credentials/st_search_page_custom_css';

    private $configInterface;
    private $storeManager;
    private $searchtapHelper;
    private $configurationFactory;

    public function __construct(
        ScopeConfigInterface $configInterface,
        StoreManagerInterface $storeManager,
        SearchtapHelper $searchtapHelper,
        ConfigurationFactory $configurationFactory
    )
    {
        $this->configInterface = $configInterface;
        $this->storeManager = $storeManager;
        $this->searchtapHelper = $searchtapHelper;
        $this->configurationFactory = $configurationFactory;
    }

//    public function getCredentials()
//    {
//        $credentials = $this->configInterface->getValue(self::PRIVATE_TOKEN);
//        return json_decode($credentials);
//    }

    public function getJsConfiguration($storeId)
    {
        return $this->configInterface->getValue(self::JS_CONFIG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getScriptUrl($storeId)
    {
        return $this->configInterface->getValue(self::SCRIPT_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCssUrl($storeId)
    {
        return $this->configInterface->getValue(self::CSS_URL, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCredentials()
    {
        return $this->configurationFactory->create()->getToken();
    }

    public function getAutocompleteCustomCss($storeId)
    {
        return $this->configInterface->getValue(self::AUTOCOMPLETE_CUSTOM_CSS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getSearchPageCustomCss($storeId)
    {
        return $this->configInterface->getValue(self::SEARCH_PAGE_CUSTOM_CSS, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

}