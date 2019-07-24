<?php

namespace Bitqit\Searchtap\Helper;

class SearchtapHelper
{
    private $emulator;
    private $storeEmulation;

    public function __construct(
        \Magento\Store\Model\App\Emulation $emulator
    )
    {
        $this->emulator = $emulator;
    }

    public function getFormattedString($string)
    {
        return trim(htmlspecialchars_decode(strip_tags($string)));
    }

    public function startEmulation($storeId)
    {
        if (!$this->storeEmulation) {
            $this->emulator->startEnvironmentEmulation($storeId, \Magento\Framework\App\Area::AREA_FRONTEND, true);
            $this->storeEmulation = true;
        }
    }

    public function stopEmulation()
    {
        if ($this->storeEmulation) {
            $this->emulator->stopEnvironmentEmulation();
            $this->storeEmulation = false;
        }
    }

    public function getCurrentDate()
    {
        date_default_timezone_set('asia/kolkata');
        return date('Y-m-d H:i:s');
    }

    public function okResult($data, $count, $statusCode = 200)
    {
        return [
            "output" => json_encode(array("data" => $data, "count" => $count)),
            "statusCode" => $statusCode
        ];
    }

    public function error($errMsg, $statusCode)
    {
        return [
            "output" => json_encode(array("data" => $errMsg)),
            "statusCode" => $statusCode
        ];
    }

    public function getStatusCodeList()
    {
        return [
            404 => \Magento\Framework\App\Response\Http::STATUS_CODE_404,
            200 => \Magento\Framework\App\Response\Http::STATUS_CODE_200,
            400 => \Magento\Framework\App\Response\Http::STATUS_CODE_400,
            403 => \Magento\Framework\App\Response\Http::STATUS_CODE_403,
        ];
    }
}