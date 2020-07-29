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

    public function getFormattedString($value)
    {
       $val=[];
       if (is_array($value)) {
            $res = array_map("gettype", $value);
            for ($i = 0; $i < sizeof($res); $i++) {
              if(array_key_exists($i, $res)){
                switch ($res[$i]) {
                    case "string":
                        $val[] = trim(htmlspecialchars_decode(strip_tags($value[$i])));
                        break;
                    case "integer":
                    case "boolean":
                    case "double":
                        $val[] = trim($value[$i]);
                        break;
                }
              }
            }
            return $val;
        } else {
            return trim(htmlspecialchars_decode(strip_tags($value)));
        }
    }

    public function getFormattedPrice($price)
    {
        return round($price, 2);
    }

    public function getFormattedArray($value)
    {
        foreach ($value as $val) {
            $formatedValue[] = trim(htmlspecialchars_decode(strip_tags($val)));
        }
        return $formatedValue;
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

    public function okResult($data, $count = 0, $statusCode = 200)
    {
        $data = [
            "data" => $data
        ];

        if ($count)
            $data["count"] = $count;

        return [
            "output" => json_encode($data),
            "statusCode" => $statusCode
        ];
    }

    public function error($message, $statusCode = 400)
    {
        return [
            "output" => json_encode(array("error" => $message)),
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

    public function getBaseApiUrl()
    {
       return "https://magento-portal.searchtap.net/client";
    }
}
