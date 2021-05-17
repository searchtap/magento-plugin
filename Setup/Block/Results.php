<?php
namespace Bitqit\Searchtap\Block;

use \Magento\Framework\View\Element\Template\Context;
use \Bitqit\Searchtap\Helper\ConfigHelper;

class Results extends \Magento\Framework\View\Element\Template
{
    private $configHelper;
    public function __construct(Context $context, ConfigHelper $configHelper) {
        $this->configHelper = $configHelper;
        parent::__construct($context);
    }

    public function _prepareLayout() {
//        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
//        $breadcrumbs->addCrumb('home', [
//            'label' => __('Home'),
//            'title' => __('Home'),
//            'link' => $this->_storeManager->getStore()->getBaseUrl()
//        ]);
//
//        $breadcrumbs->addCrumb('search', [
//            'label' => __('Search'),
//            'title' => __('Search')
//        ]);
        parent::_prepareLayout();
    }

    public function getReferenceNodeSelector() {
        $config = $this->configHelper->getJsConfiguration($this->_storeManager->getStore()->getId());
        return json_decode($config)->searchPage->searchResultsPlacementContainer;
    }
}