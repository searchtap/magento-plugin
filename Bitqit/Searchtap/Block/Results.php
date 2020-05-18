<?php
namespace Bitqit\Searchtap\Block;

use \Magento\Framework\View\Element\Template\Context;

class Results extends \Magento\Framework\View\Element\Template
{
    private $configHelper;
    public function __construct(Context $context) {
        parent::__construct($context);
    }

    public function _prepareLayout() {
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbs->addCrumb('home', [
            'label' => __('Home'),
            'title' => __('Home'),
            'link' => $this->_storeManager->getStore()->getBaseUrl()
        ]);

        $breadcrumbs->addCrumb('search', [
            'label' => __('Search'),
            'title' => __('Search')
        ]);
        parent::_prepareLayout();
    }

}