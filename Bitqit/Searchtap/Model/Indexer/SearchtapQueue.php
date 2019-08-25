<?php

namespace Bitqit\Searchtap\Model\Indexer;

use \Bitqit\Searchtap\Helper\Api;

class SearchtapQueue implements  \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    private $api;

    public function __construct(
        Api $api
    )
    {
        $this->api = $api;
    }

    public function execute ($ids)
    {
        $this->api->requestToSync();
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