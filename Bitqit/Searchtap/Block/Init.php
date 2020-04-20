<?php
namespace Bitqit\Searchtap\Block;

use \Bitqit\Searchtap\Helper\ConfigHelper;

class Init extends \Magento\Framework\View\Element\Template
{
    private $configHelper;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->configHelper = $configHelper;
    }

    public function getConfiguration()
    {
        return $this->configHelper->getJsConfiguration();
    }

    public function getScriptUrl() {
        return $this->configHelper->getScriptUrl();
    }

    public function getCssUrl() {
        return $this->configHelper->getCssUrl();
    }
}