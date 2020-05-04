<?php
namespace Bitqit\Searchtap\Block;

use \Bitqit\Searchtap\Helper\ConfigHelper;

class Init extends \Magento\Framework\View\Element\Template
{
    private $configHelper;
    private $storeId;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->storeId = $this->_storeManager->getStore()->getId();
    }

    public function getConfiguration()
    {
        return $this->configHelper->getJsConfiguration($this->storeId);
    }

    public function getScriptUrl() {
        return $this->configHelper->getScriptUrl($this->storeId);
    }

    public function getCssUrl() {
        return $this->configHelper->getCssUrl($this->storeId);
    }
}