<?php

namespace Bitqit\Searchtap\Helper;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Magento\Store\Model\StoreManagerInterface;
use \Bitqit\Searchtap\Model\QueueFactory as QueueFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $configHelper;
    private $storeManager;
    private $searchtapHelper;
    private $queueFactory;

    public function __construct(
        ConfigHelper $configHelper,
        StoreManagerInterface $storeManager,
        SearchtapHelper $searchtapHelper,
        QueueFactory $queueFactory
    )
    {
        $this->configHelper = $configHelper;
        $this->storeManager = $storeManager;
        $this->searchtapHelper = $searchtapHelper;
        $this->queueFactory = $queueFactory;
    }

    public function checkPrivateKey($privateKey)
    {
        $dbPrivateKey = ($this->configHelper->getCredentials())->privateKey;

        if (!empty($privateKey)) {
            if ($privateKey === $dbPrivateKey)
                return true;
        }

        return false;
    }

    public function checkCredentials()
    {
        $credentials = $this->configHelper->getCredentials();

        if ($credentials) {
            if (isset($credentials->privateKey) && isset($credentials->uniqueId))
                return true;
        }

        return false;
    }

    public function getStores() {
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
}