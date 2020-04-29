<?php


namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Bitqit\Searchtap\Model\System\Config\Status;

class Setting extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Bitqit\Searchtap\Model\Config\Status
     */
    protected $_newsStatus;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Status $newsStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Status $newsStatus,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
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

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Enable Stores')]
        );

      /*  if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }
     */
        $storeManager = \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\Store\Model\StoreManagerInterface');
        $stores = $storeManager->getStores(true, false);
        $i=0;
        foreach ($stores as $store) {
              $i++;

            $fieldset->addField('select'.$i, 'select', array(
                'label' => $store->getName(),
                'class' => 'required-entry',
                'style'=>'
    font-weight: 300;
    background: lemonchiffon;
',
                'required' => true,
                'name' => 'title',
                'onclick' => "",
                'onchange' => "",
                'value' => '1',
                'values' => array('-1' => 'Select Data Center', '1' => 'India', '2' => 'US-NYC', '3' => 'Australia'),
                'tabindex' => 1
            ));
        }
        $fieldset->addField('link1', 'link', array(

            'after_element_html' => '<button style="    background: #eb5202;
    border-color: #eb5202;
    color: #fbfbfb;">Save and Sync Store</button>'
        ));

        $data = $model->getData();
        $form->setValues($data);
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
