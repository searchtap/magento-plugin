<?php

namespace Bitqit\Searchtap\Plugin;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Magento\Store\Model\StoreManagerInterface;

class CatalogSearchPlugin
{
    protected $configHelper;
    protected $storeManager;

    public function __construct(ConfigHelper $configHelper, StoreManagerInterface $storeManager) {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
    }

    public function afterGetResultUrl($subject, $result)
    {
        $config = $this->configHelper->getJsConfiguration($this->storeManager->getStore()->getId());
        if (json_decode($config)->searchPage->isSearchUIEnabled)
            return "";

        return $result;
    }
}