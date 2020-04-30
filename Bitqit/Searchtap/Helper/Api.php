<?php

namespace Bitqit\Searchtap\Helper;

use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Bitqit\Searchtap\Helper\Logger;
use \Bitqit\Searchtap\Helper\Data;
use Bitqit\Searchtap\Helper\ConfigHelper;
use mysql_xdevapi\Exception;
use Bitqit\Searchtap\Model\ConfigurationFactory;

class Api
{
    const INDEXING_URL = "/sync";
    const STORE_API = '/my-stores';
    const DATACENTER_API='/data-centers';

    private $searchtapHelper;
    private $logger;
    private $dataHelper;
    private $_configHelper;
    private $_configFactory;

    public function __construct(
        SearchtapHelper $searchtapHelper,
        Logger $logger,
        Data $dataHelper,
        ConfigHelper $configHelper,
        ConfigurationFactory $configFactory
    )
    {
        $this->searchtapHelper = $searchtapHelper;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->_configHelper = $configHelper;
        $this->_configFactory=$configFactory;
    }

    private function _getCurlObject($apiUrl, $requestType, $token, $data = null)
    {
        $curlObject = array(
            CURLOPT_URL => $apiUrl,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_CAINFO => "",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $requestType,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache",
                "content-type: application/json",
                "Authorization: Bearer " . $token
            )
        );

        return $curlObject;
    }

    public function getApiBaseUrl()
    {
        return $this->searchtapHelper->getBaseApiUrl();
    }

    public function requestToSyncStores()
    {
        try {
            $url = $this->getApiBaseUrl() . self::STORE_API;
            $stores = $this->dataHelper->getStores();
            $credentials = $this->configHelper->getCredentials();

            $data = [];
            foreach ($stores as $store) {
                $data[] = array(
                    'storeId' => $store->getId(),
                    'storeUrl' => $store->getBaseUrl(),
                    'storeName' => $store->getName(),
                    'storeStatus' => $store->isActive()
                );
            }

            $token = $credentials->uniqueId . "," . $credentials->privateKey;

            $config = $this->_getCurlObject($url, 'POST', $token, json_encode($data));
            $curl = curl_init();

            curl_setopt_array($curl, $config);

            $result = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $this->logger->add($statusCode);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw new Exception($e);
        }
    }

    public function requestToSync($data = null)
    {
//        try {
//            $curlObject = $this->_getCurlObject($this->getApiUrl(self::INDEXING_URL), "POST");
//            if ($data)
//                $curlObject['CURLOPT_POSTFIELDS'] = $data;
//
//            $curl = curl_init();
//            curl_setopt_array($curl, $curlObject);
//
//            $results = curl_exec($curl);
//            $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//            $curlError = curl_error($curl);
//
//            if ($curlError)
//                $this->logger->error($curlError);
//
//            curl_close($curl);
//
//        } catch (err $e) {
//            $this->logger->error($e);
//        }
    }
    public function getDataCenterList(){
        $tokenValue=json_decode($this->_configHelper->getAPIToken());
        $token=$tokenValue->uniqueId.",".$tokenValue->privateKey;
        try {
            $curlObject = $this->_getCurlObject($this->getApiBaseUrl().self::DATACENTER_API, "GET",$token);
            $curl = curl_init();
            curl_setopt_array($curl, $curlObject);
            $results = curl_exec($curl);
            $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);

            if ($curlError)
                $this->logger->error($curlError);

            curl_close($curl);

            return json_decode($results);

        } catch (err $e) {
            $this->logger->error($e);
        }
    }

}