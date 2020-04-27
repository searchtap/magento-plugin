<?php

namespace Bitqit\Searchtap\Helper\Configuration;

class PluginHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Catalog\Model\Product $productModel,
        \Magento\Catalog\Model\Config $configModel,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Review\Model\ResourceModel\Rating\CollectionFactory $ratingCollectionFactory,
        \Bitqit\Searchtap\Model\ConfigureFactory $configureFactory,
        \Bitqit\Searchtap\Helper\Api $Api,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
    )
    {
        $this->datetime = $datetime;
        $this->timezoneInterface = $timezoneInterface;
        $this->storeManager = $storeManager;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->currency = $currency;
        $this->currencyFactory = $currencyFactory;
        $this->attributeFactory = $attributeFactory;
        $this->productModel = $productModel;
        $this->collectionFactory = $collectionFactory;
        $this->configModel = $configModel;
        $this->productFactory = $productFactory;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->configureFactory = $configureFactory;
        $this->tagalysApi = $Api;
        $this->categoryCollection = $categoryCollection;
    }

    public function getToken($fieldname){
        $configValue = $this->configureFactory->create()->load($fieldname)->getValue();
        return $configValue;
    }

    public function getDataCenterList(){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://magento-portal.searchtap.net/client/data-centers",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/json",
                "Authorization: Bearer U3J6Jol4WW3QIKfQ5gisVXlkef0A4hdk,LUw0iAhgIh7gTUmYjKnSrWlk1GueZJFFhXwCZhkUuhvHu2M1mZMxPJwztjeDU9U8 "
            ),
        ));

        $results = curl_exec($curl);
        $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);
        var_dump($results);
        return $results;
    }

}