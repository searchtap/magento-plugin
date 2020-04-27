<?php


namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Bitqit\Searchtap\Model\System\Config\Status;
use Bitqit\Searchtap\Helper\Configuration\PluginHelper;

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

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $newsStatus,
        PluginHelper $_apiHelper,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
        $this->_apiHelper=$_apiHelper;
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
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('configuration_');
        $form->setFieldNameSuffix('configuration');

        $dataCenter= $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Select Data Center')]
        );
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores(true, false);
        $i=0;
        foreach ($stores as $store) {
              $i++;

            $dataCenter->addField('select'.$i, 'select', array(
                'label' => $store->getName(),
                'class' => 'required-entry',
                'style'=>'font-weight: 600;background: lemonchiffon;',
                'required' => true,
                'name' => 'title',
                'value' => '1',
               // 'values' => array('-1' => 'Select Data Center', '1' => 'India', '2' => 'US-NYC', '3' => 'Australia'),
                'values' => $this->_apiHelper->getDataCenterList(),
                'tabindex' => 1
            ));
        }
        $dataCenter->addField('dcsubmit', 'submit', array(
            'required'  => true,
            'value'  => 'Save and Sync Store',
            'tabindex' => 1,
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
