<?php

namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;
use Bitqit\Searchtap\Helper\Logger as Logger;
use Bitqit\Searchtap\Helper\Products\AttributeHelper as AttributeHelper;
use Bitqit\Searchtap\Model\QueueFactory as QueueFactory;

class Attributes implements \Magento\Framework\Event\ObserverInterface
{
    private $logger;
    private $attributeHelper;
    private $queueFactory;

    public function __construct(
        Logger $logger,
        AttributeHelper $attributeHelper,
        QueueFactory $queueFactory
    )
    {
        $this->logger = $logger;
        $this->attributeHelper = $attributeHelper;
        $this->queueFactory = $queueFactory;
    }

    public function execute(Observer $observer)
    {
        $attribute = $observer->getAttribute();
        $eventName = $observer->getEvent()->getName();

        if ($attribute) {
            if ($eventName === "catalog_entity_attribute_save_after")
                $this->catalogEntityAttributeSaveAfter($attribute);
            else if ($eventName === "catalog_entity_attribute_delete_after")
                $this->catalogEntityAttributeDeleteAfter($attribute);
        }
    }

    public function catalogEntityAttributeSaveAfter($attribute)
    {
        try {

            $oldAttributeData = $attribute->getOrigData();
            $isOldAttributeFilterable = $oldAttributeData['is_filterable'];

            $action = "add";
            if (!$this->attributeHelper->canAttributeBeReindex($attribute)) {
                if (!$isOldAttributeFilterable) return;

                $action = "delete";
            }

            $this->queueFactory->create()->addToQueue(
                $attribute->getId(),
                $action,
                "pending",
                "attribute",
                0
            );
        } catch (error $e) {
            $this->logger->error($e);
        }
    }

    public function catalogEntityAttributeDeleteAfter($attribute)
    {
        try {
            //Delete the attribute only if it can be reindex
            if ($this->attributeHelper->canAttributeBeReindex($attribute)) {
                $this->queueFactory->create()->addToQueue(
                    $attribute->getId(),
                    "delete",
                    "pending",
                    "attribute",
                    0
                );
            }
        } catch (error $e) {
            $this->logger->error($e);
        }
    }
}