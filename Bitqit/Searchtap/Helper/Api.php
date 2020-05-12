<?php

namespace Bitqit\Searchtap\Helper;

use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Bitqit\Searchtap\Helper\Logger;
use \Bitqit\Searchtap\Helper\Data;
use mysql_xdevapi\Exception;
use Bitqit\Searchtap\Model\ConfigurationFactory;

class Api
{
    const STORE_API = '/my-stores';
    const DATACENTER_API = '/data-centers';

    private $searchtapHelper;
    private $logger;
    private $dataHelper;
    private $configurationFactory;

    public function __construct(
        SearchtapHelper $searchtapHelper,
        Logger $logger,
        Data $dataHelper,
        ConfigurationFactory $configurationFactory
    )
    {
        $this->searchtapHelper = $searchtapHelper;
        $this->logger = $logger;
        $this->dataHelper = $dataHelper;
        $this->configurationFactory = $configurationFactory;
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

    public function requestToSyncStores($dataCenters)
    {
        try {
            $url = $this->getApiBaseUrl() . self::STORE_API;
            $stores = $this->dataHelper->getStores();

            $data = [];
            foreach ($stores as $store) {
                $data[] = array(
                    'storeId' => (int)$store->getId(),
                    'storeUrl' => $store->getBaseUrl(),
                    'storeName' => $store->getName(),
                    'storeStatus' => $store->isActive(),
                    'dataCenter' => (int)$dataCenters[$store->getId()]
                );
            }

            $credentials = $this->dataHelper->getCredentials();
            $token = $credentials->uniqueId . "," . $credentials->privateKey;

            $config = $this->_getCurlObject($url, 'POST', $token, json_encode($data));

            $curl = curl_init();

            curl_setopt_array($curl, $config);

            $result = curl_exec($curl);
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
        } catch (\Exception $e) {
            $this->logger->error($e);
            throw new Exception($e);
        }
    }

    public function getDataCenters()
    {
        try {
            $credentials = $this->dataHelper->getCredentials();
            if (!$credentials) return [];

            $token = $credentials->uniqueId . "," . $credentials->privateKey;

            $url = $this->getApiBaseUrl() . self::DATACENTER_API;

            $curlObject = $this->_getCurlObject($url, "GET", $token);
            $curl = curl_init();
            curl_setopt_array($curl, $curlObject);
            $results = curl_exec($curl);
            $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);

            if ($curlError)
                $this->logger->error($curlError);

            curl_close($curl);

            return json_decode($results, true)["data"];
        } catch (\Exception $e) {
            $this->logger->error($e);
            return [];
        }
    }
}