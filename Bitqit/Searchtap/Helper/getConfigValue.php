<?php

namespace Bitqit\Searchtap\Helper;

class getConfigValue{

    protected $state;
    protected $objectManager;
    public $productCount;
    public $storeManager;
    protected $storeId=1;
    public $collectionName;
    public $adminKey;
    public $applicationId;
    public $selectedAttributes;
    protected $logger;
    protected $cert_path;
    public $imageWidth = 0;
    public $imageHeight = 0;
    public $actualCount = 0;
    public $parentCount = 0;
    public $inactiveCategoryOption;
    public $emptycategoryoption;
    public $customcategoryAttributeoption;
    public $categoryIncludeInMenu = 0;
    public $skipCategoryIds;
    public $product_visibility_array = array('1' => 'Not Visible Individually', '2' => 'Catalog', '3' => 'Search', '4' => 'Catalog,Search');
  //  private $st;

    public function __construct( \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->productCount=1000;
        $this->storeManager=$storeManager;
        $this->cert_path = BP . '/app/code/Bitqit/Searchtap/st_cert/searchtap.io.crt';
        $this->product_visibility_array = array('1' => 'Not Visible Individually', '2' => 'Catalog', '3' => 'Search', '4' => 'Catalog,Search');
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
   /* Setting */
        $this->imageWidth = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/image/st_image_width', $this->storeId);
        $this->imageHeight = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/image/st_image_height', $this->storeId);
        $this->collectionName = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/general/st_collection', $this->storeId);
        $this->adminKey = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/general/st_admin_key', $this->storeId);
        $this->applicationId = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/general/st_application_id', $this->storeId);
        $this->selectedAttributes = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/attributes/additional_attributes', $this->storeId);
        $this->discountFilterEnabled = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/st_discount/st_discount_enabled', $this->storeId);

    /* Category Configuration Option */
        $this->categoryIncludeInMenu = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/categories/st_categories_menu', $this->storeId);
        $this->skipCategoryIds = $this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_configuration/categories/st_skipcategory', $this->storeId);
        $this->inactiveCategoryOption=$this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_category/general/st_inactivecategory', $this->storeId);
        $this->emptycategoryoption=$this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_category/general/st_emptycategories', $this->storeId);
        $this->customcategoryAttributeoption=$this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_category/general/st_customattribute', $this->storeId);
        $this->categoryIncludeInMenu=$this->objectManager->create('Bitqit\Searchtap\Helper\Data')->getConfigValue('st_category/general/st_discount_enabled', $this->storeId);

      //  $this->st = new SearchTapAPI($this->applicationId, $this->collectionName, $this->adminKey);

    }

    public function getStores($storeIds = null)
    {
        if (empty($storeIds)) {
            return $this->storeManager->getStores();
        }

        $stores = [];

        $storeIds = is_array($storeIds) ? $storeIds : [$storeIds];

        foreach ($storeIds as $storeId) {
            $store = $this->storeManager->getStore($storeId);

            if (!empty($store)) {
                $stores[$store->getId()] = $store;
            }
        }

        return $stores;
    }

}
