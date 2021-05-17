<?php


namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Bitqit\Searchtap\Model\System\Config\Status;
use Bitqit\Searchtap\Helper\Api;
use Bitqit\Searchtap\Helper\ConfigHelper;
use Bitqit\Searchtap\Model\ConfigurationFactory;
use Bitqit\Searchtap\Helper\Data;

class Settings extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Bitqit\Searchtap\Model\Config\Status
     */
    protected $_newsStatus;
    protected $_apiHelper;
    protected $_configHelper;
    private $_configurationFactory;
    private $_dataHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $newsStatus,
        Api $_apiHelper,
        ConfigHelper $configHelper,
        ConfigurationFactory $configurationFactory,
        Data $dataHelper,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
        $this->_apiHelper = $_apiHelper;
        $this->_configHelper = $configHelper;
        $this->_configurationFactory = $configurationFactory;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @return \Magento\Backend\Block\Widget\Form
     */
    protected function _prepareForm()
    {
        /** @var $model \Bitqit\Searchtap\Model\News */
        $model = $this->_coreRegistry->registry('searchtap_configuration');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]);

        /*
         * Select data centers for stores
         */
        $dataCenterFieldSet = $form->addFieldset(
            'st-data-centers',
            ['legend' => __('Select Data Center')]
        );

        $isDisabled = false;
        $stores = $this->_dataHelper->getEnabledStores();
        $dataCenters = $this->_apiHelper->getDataCenters();
        $dataCenters[0] = "Select Data Center";
        $dbDataCenters = $this->_configurationFactory->create()->getDataCenters();
        if ($dbDataCenters && count($dbDataCenters) > 0) $isDisabled = true;

        foreach ($stores as $store) {
            $storeId = $store->getId();
            $selectedValue = $dbDataCenters ? $dbDataCenters[$storeId] : $dataCenters[0];
            $dataCenterFieldSet->addField('select' . $storeId, 'select', array(
                'label' => $store->getName(),
                'class' => 'required-entry',
                'required' => true,
                'name' => "store_" . $storeId,
                'disabled' => $isDisabled,
                'value' => $selectedValue,
                'values' => $dataCenters,
                'after_element_html' => '<p style="margin: 0 0 .5em; -webkit-text-stroke-width: thin; color: #0c0c0c; font-family: serif;"> ( Store ID: '.$storeId.' ) </p>',
                'tabindex' => 1
            ));
        }

        if (!$isDisabled) {
            $dataCenterFieldSet->addField('submit', 'submit', array(
                'required' => true,
                'value' => 'Save and Sync Stores',
                'name' => 'st-save-data-centers',
                'style' => 'background: #e85d22;border-color: #e85d22;color: #ffffff; width: 35%; padding-bottom: 0.6875em; padding-top: 0.6875em;'
            ));
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('SearchTap Config');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('SearchTap Config');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
