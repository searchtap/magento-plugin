<?php

namespace Bitqit\Searchtap\Block;

use \Bitqit\Searchtap\Helper\ConfigHelper;
use \Magento\Framework\Locale\CurrencyInterface;

class Init extends \Magento\Framework\View\Element\Template
{
    private $configHelper;
    private $storeId;
    private $currencyInterface;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        ConfigHelper $configHelper,
        CurrencyInterface $currencyInterface
    )
    {
        parent::__construct($context);
        $this->configHelper = $configHelper;
        $this->currencyInterface = $currencyInterface;
        $this->storeId = $this->_storeManager->getStore()->getId();
    }

    public function getConfiguration()
    {
        return $this->configHelper->getJsConfiguration($this->storeId);
    }

    public function getScriptUrl()
    {
        return $this->configHelper->getScriptUrl($this->storeId);
    }

    public function getCssUrl()
    {
        return $this->configHelper->getCssUrl($this->storeId);
    }

    public function getAutocompleteCustomCss()
    {
        return $this->configHelper->getAutocompleteCustomCss($this->storeId);
    }

    public function getSearchPageCustomCss()
    {
        return $this->configHelper->getSearchPageCustomCss($this->storeId);
    }

    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function getCurrentCurrencyRate($currencyCode)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($currencyCode);
    }

    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getCode();
    }

    public function getCurrentCurrencySymbol($currencyCode)
    {
        return $this->currencyInterface->getCurrency($currencyCode)->getSymbol();
    }
}
