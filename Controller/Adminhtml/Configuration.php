<?php

namespace Bitqit\Searchtap\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Bitqit\Searchtap\Model\ConfigurationFactory;
use Bitqit\Searchtap\Helper\Api;

class Configuration extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;


    protected $_configurationFactory;
    protected $_apiHelper;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        ConfigurationFactory $configurationFactory,
        Api $apiHelper
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_configurationFactory = $configurationFactory;
        $this->_apiHelper=$apiHelper;

    }
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Bitqit_Searchtap::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('SearchTap Configuration'));
        return $resultPage;
    }

    /**
     * News access rights checking
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bitqit_Searchtap::searchtap_configuration');
    }
}
