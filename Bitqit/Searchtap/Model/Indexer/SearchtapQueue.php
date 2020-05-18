<?php

namespace Bitqit\Searchtap\Model\Indexer;

use \Bitqit\Searchtap\Helper\Api;
use \Bitqit\Searchtap\Helper\Data;

class SearchtapQueue implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    private $api;
    private $dataHelper;

    public function __construct(
        Api $api,
        Data $dataHelper
    )
    {
        $this->api = $api;
        $this->dataHelper = $dataHelper;
    }

    public function execute($ids)
    {
        if (!$this->dataHelper->checkCredentials()) {
            echo "Invalid credentials";
        }

//        $this->api->requestToSyncStores();
    }

    public function executeFull()
    {
        $this->execute(null);
    }

    public function executeList(array $ids)
    {
        $this->execute($ids);
    }

    public function executeRow($id)
    {
        $this->execute($id);
    }
}