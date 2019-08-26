<?php

namespace Bitqit\Searchtap\Helper;

use \Bitqit\Searchtap\Helper\SearchtapHelper;
use \Bitqit\Searchtap\Helper\Logger;

class Api
{
    const INDEXING_URL = "sync";

    private $searchtapHelper;
    private $logger;

    public function __construct(
        SearchtapHelper $searchtapHelper,
        Logger $logger
    )
    {
        $this->searchtapHelper = $searchtapHelper;
        $this->logger = $logger;
    }

    protected function _getCurlObject($apiUrl, $requestType)
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
            CURLOPT_CUSTOMREQUEST => $requestType
//            CURLOPT_HTTPHEADER => array(
//                "content-type: application/json",
//                "Authorization: Bearer " . $this->adminKey
//            ),
        );

        return $curlObject;
    }

    public function getApiUrl($url)
    {
        return $this->searchtapHelper->getBaseApiUrl() . $url;
    }

    public function requestToSync($data = null)
    {
        try {
            $curlObject = $this->_getCurlObject($this->getApiUrl(self::INDEXING_URL), "POST");
            if ($data)
                $curlObject['CURLOPT_POSTFIELDS'] = $data;

            $curl = curl_init();
            curl_setopt_array($curl, $curlObject);

            $results = curl_exec($curl);
            $responseHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $curlError = curl_error($curl);

            if ($curlError)
                $this->logger->error($curlError);

            curl_close($curl);

        } catch (err $e) {
            $this->logger->error($e);
        }
    }
}