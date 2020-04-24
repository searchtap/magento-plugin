<?php

namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Bitqit\Searchtap\Model\System\Config\Status;

class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

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

        $model = $this->_coreRegistry->registry('searchtap_configuration');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('configuration_');
        $form->setFieldNameSuffix('configuration');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Searchtap Dashboard')]
        );

      /*  if ($model->getId()) {
            $fieldset->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }*/
        $fieldset->addField('link', 'link', array(
            'label'     => ('Searchtap Account'),
            'after_element_html'=>'<button style="background: #eb5202;border-color: #eb5202;color: #fbfbfb;">Signup for the Searchtap Account</button>'
        ));


        $fieldset2 = $form->addFieldset(
            'base2_fieldset',
            ['legend' => __('API Token')]
        );

        $fieldset2->addField('textarea', 'textarea', array(
            'label'     => 'Token',
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'Token',
            'onclick' => "",
            'onchange' => "",
            'value'  => '<b><b/>',

            'after_element_html' => '<button style="background: #eb5202;border-color: #eb5202;color: #fbfbfb;">Save Credential</button>',
            'tabindex' => 1
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
        return __('Searchtap Dashboard');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Searchtap Dashboard');
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
