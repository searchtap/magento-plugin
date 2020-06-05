<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;
use Bitqit\Searchtap\Helper\Logger as Logger;
use Bitqit\Searchtap\Helper\Products\AttributeHelper as AttributeHelper;
use Bitqit\Searchtap\Model\QueueFactory as QueueFactory;
use Bitqit\Searchtap\Helper\Data as DataHelper;

class Attributes implements \Magento\Framework\Event\ObserverInterface
{
    private $logger;
    private $attributeHelper;
    private $queueFactory;
    private $dataHelper;

    public function __construct(
        Logger $logger,
        AttributeHelper $attributeHelper,
        QueueFactory $queueFactory,
        DataHelper $dataHelper
    )
    {
        $this->logger = $logger;
        $this->attributeHelper = $attributeHelper;
        $this->queueFactory = $queueFactory;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer)
    {
        $attribute = $observer->getAttribute();
        switch ($observer->getEvent()->getName()) {
            case "catalog_entity_attribute_save_after":
                $this->catalogEntityAttributeSaveAfter($attribute);
                break;
            case "catalog_entity_attribute_delete_after":
                $this->catalogEntityAttributeDeleteAfter($attribute);
                break;
        }
    }

    public function catalogEntityAttributeSaveAfter($attribute)
    {
        try {
            $stores = $this->dataHelper->getEnabledStores();
            $oldAttributeData = $attribute->getOrigData();
            $isOldAttributeFilterable = $oldAttributeData['is_filterable'];

            $action = "add";
            if (!$this->attributeHelper->canAttributeBeReindex($attribute)) {
                if (!$isOldAttributeFilterable) return;

                $action = "delete";
            }

            foreach ($stores as $store)
                $this->queueFactory->create()->addToQueue(
                    $attribute->getId(),
                    $action,
                    "pending",
                    "attribute",
                    $store->getId()
                );
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogEntityAttributeDeleteAfter($attribute)
    {
        try {
            //Refactor the attribute only if it can be reindex
            if ($this->attributeHelper->canAttributeBeReindex($attribute)) {
                $stores = $this->dataHelper->getEnabledStores();
                foreach ($stores as $store) {
                    $this->queueFactory->create()->addToQueue(
                        $attribute->getId(),
                        "delete",
                        "pending",
                        "attribute",
                        $store->getId()
                    );
                }
            }
        } catch (error $e) {
            $this->logger->error($e);
        }
    }
}