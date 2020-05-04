<?php

namespace Bitqit\Searchtap\Helper;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Magento\Store\Model\StoreManagerInterface;
use \Bitqit\Searchtap\Model\QueueFactory as QueueFactory;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Cache\Frontend\Pool;
use \Bitqit\Searchtap\Model\ConfigurationFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const JS_CONFIG = 'searchtap_credentials/credentials/st_config';
    const SCRIPT_URL = 'searchtap_credentials/credentials/st_script_url';
    const CSS_URL = 'searchtap_credentials/credentials/st_css_url';

    private $configHelper;
    private $storeManager;
    private $searchtapHelper;
    private $queueFactory;
    private $configInterface;
    private $cacheTypeList;
    private $cacheFrontendPool;
    private $configurationFactory;

    public function __construct(
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager,
        SearchtapHelper $searchtapHelper,
        QueueFactory $queueFactory,
        WriterInterface $configInterface,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool,
        ConfigurationFactory $configurationFactory
    )
    {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->searchtapHelper = $searchtapHelper;
        $this->queueFactory = $queueFactory;
        $this->configInterface = $configInterface;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->configurationFactory = $configurationFactory;
    }

    public function getCredentials()
    {
        $credentials = $this->configurationFactory->create()->getToken();
        return json_decode($credentials);
    }

    public function checkPrivateKey($privateKey)
    {
        $dbPrivateKey = ($this->getCredentials())->privateKey;

        if (!empty($privateKey)) {
            if ($privateKey === $dbPrivateKey)
                return true;
        }

        return false;
    }

    public function checkCredentials()
    {
        $credentials = $this->getCredentials();

        if ($credentials) {
            if (isset($credentials->privateKey) && isset($credentials->uniqueId))
                return true;
        }

        return false;
    }

    public function setJSConfiguration($token, $data)
    {
        if (!$this->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

        $token = str_replace("Bearer ", "", $token);
        if (!$this->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        foreach ($data as $item) {
            $this->configInterface->save(
                self::JS_CONFIG,
                json_encode($item->config),
                "stores",
                $item->storeId);
            $this->configInterface->save(self::SCRIPT_URL, $item->scriptUrl, "stores", $item->storeId);
            $this->configInterface->save(self::CSS_URL, $item->cssUrl, "stores", $item->storeId);
        }

        $this->_cleanCache();

        return $this->searchtapHelper->okResult("ok");
    }

    private function _cleanCache()
    {
        $types = ['config', 'full_page'];
        foreach ($types as $type)
            $this->cacheTypeList->cleanType($type);

        foreach ($this->cacheFrontendPool as $pool)
            $pool->getBackend()->clean();
    }

    public function getStores()
    {
        return $this->storeManager->getStores();
    }

    public function getStoresData($token)
    {
        if (!$this->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

        if (!$this->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        $stores = [];
        $collection = $this->getStores();
        foreach ($collection as $store) {
            $data = array(
                "id" => $store->getId(),
                "code" => $store->getCode(),
                "name" => $store->getName(),
                "is_active" => $store->isActive(),
                "website_id" => $store->getWebsiteId(),
                "url" => $store->getBaseUrl()
            );
            $stores[] = $data;
        }

        return $this->searchtapHelper->okResult($stores, count($stores));
    }

    public function getQueueData($token, $count, $page, $type, $action, $storeId)
    {
        if (!$this->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

        if (!$this->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        $data = $this->queueFactory->create()->getQueueData($count, $page, $type, $action, $storeId);

        return $this->searchtapHelper->okResult($data['data'], $data['count']);
    }

    public function deleteQueueData($token, $entityIds)
    {
        if (!$this->checkCredentials()) {
            return $this->searchtapHelper->error("Invalid credentials");
        }

        if (!$this->checkPrivateKey($token)) {
            return $this->searchtapHelper->error("Invalid token");
        }

        if (!$entityIds) {
            return $this->searchtapHelper->error("Invalid entity Ids");
        }

        $data = $this->queueFactory->create()->deleteQueueData(explode(',', $entityIds));

        return $this->searchtapHelper->okResult($data);
    }

    public function isStoreEnabled($store)
    {
        return $store->isActive();
    }

    public function isStoreAvailable($storeId)
    {
        $stores = $this->storeManager->getStores();

        foreach ($stores as $store)
            if (($store->getId() == $storeId) && $this->isStoreEnabled($store))
                return true;

        return false;
    }

    public function getEnabledStores()
    {
        $stores = [];

        $storeCollection = $this->storeManager->getStores();
        foreach ($storeCollection as $store) {
            if ($store->isActive()) $stores[] = $store;
        }

        return $stores;
    }
}