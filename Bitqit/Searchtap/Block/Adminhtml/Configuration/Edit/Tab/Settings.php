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
    private $_configFactory;

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $newsStatus,
        Api $_apiHelper,
        ConfigHelper $configHelper,
        ConfigurationFactory $configFactory,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
        $this->_apiHelper = $_apiHelper;
        $this->_configHelper = $configHelper;
        $this->_configFactory = $configFactory;
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

        $dataCenter = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Select Data Center')]
        );

        $data_center = $this->_apiHelper->getDataCenterList();
        $objectToArray = (array)$data_center->data;
        foreach ($objectToArray as $key=>$value) {
            $dataCenterValue['0'] = 'Select Data Center';
            $dataCenterValue[$value] = $key;
        }

        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores(true, false);

        // Get Configuration from table
        $configValue = $this->_configFactory->create()->getCollection();
        foreach ($configValue as $values) {
            $data = (array)$values->getDataCenter();
        }
        $val = 0; // for data center INDIA 1000, US 2000, AUS 4000
        foreach ($stores as $store) {
            foreach ($data as $k=>$v) {
                if (!strcmp($k, str_replace(" ", "_", $store->getName()))) {
                    $val = $v; // for selected value
                }
            }
            if ($store->getID() == 0) {
                continue;
            }
            $dataCenter->addField('select' . $store->getID(), 'select', array(
                'label' => $store->getName(),
                'class' => 'required-entry',
                'required' => true,
                'name' => "store_" . str_replace(" ", "_", $store->getName()),
                'value' => (int)$val,
                'values' => $dataCenterValue,
                'tabindex' => 1
            ));
        }

        $dataCenter->addField('submit', 'submit', array(
            'required' => true,
            'value' => 'Save and Sync Store',
            'name' => 'searchtap_credential',
            'style' => 'background: #e85d22;border-color: #e85d22;color: #ffffff; width: 35%;'
        ));

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
        return __('Searchtap Config');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Searchtap Config');
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
