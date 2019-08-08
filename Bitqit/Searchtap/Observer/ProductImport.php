<?php
namespace Bitqit\Searchtap\Observer;

use Magento\Framework\Event\Observer;

class ProductImport implements \Magento\Framework\Event\ObserverInterface
{

    private $queueFactory;
    private $productHelper;
    private $configHelper;
    private $logger;
    private $product;

    public function __construct(
        \Bitqit\Searchtap\Model\QueueFactory $queueFactory,
        \Bitqit\Searchtap\Helper\Products\ProductHelper $productHelper,
        \Bitqit\Searchtap\Helper\ConfigHelper $configHelper,
        \Bitqit\Searchtap\Helper\Logger $logger,
        \Magento\Catalog\Model\Product $product
    )
    {
        $this->queueFactory = $queueFactory;
        $this->productHelper = $productHelper;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->product=$product;
    }

    public function execute(Observer $observer)
    {
            switch ($observer->getEvent()->getName()) {


                case "catalog_product_import_bunch_save_after":
                    $productImport=$observer->getEvent()->getData('bunch');
                    $this->catalogProductImportBunchSaveAfter($productImport);
                    break;

                case "catalog_product_import_bunch_delete_commit_before":
                    $idsToDelete = $observer->getEvent()->getData('ids_to_delete');
                    $this->catalogProductImportBunchDeleteCommitBefore($idsToDelete);
                    break;
            }

    }

    /*
     * @searchtap
     * Product Trigger Import
     *
     * */

    private function catalogProductImportBunchSaveAfter($productImport){
     $storeId = $this->product->getStoreId();
     foreach($productImport as $importId){
         $productId= $this->product->getIdBySku($importId['sku']);
         $this->queueFactory->create()->addToQueue($productId, 'add', 'pending', 'product', $storeId);
         $this->logger->add("Product Id ".$productId." added in searchtap queue for Add/Update.");
     }
    }

    private function catalogProductImportBunchDeleteCommitBefore($idsToDelete)
    {
        $storeId = $this->product->getStoreId();
        foreach($idsToDelete as $productId){
            $this->queueFactory->create()->addToQueue($productId, 'delete', 'pending', 'product', $storeId);
            $this->logger->add("Product Id ".$productId." added in searchtap queue for Delete.");
        }

    }


}