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
        return trim(htmlspecialchars_decode(strip_tags(preg_replace('/[^A-Za-z0-9\-]/', '', $string))));
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



}