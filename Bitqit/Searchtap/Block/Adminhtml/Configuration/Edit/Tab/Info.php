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

class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    protected $_newsStatus;
    protected $_apiToken;

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
        PluginHelper $apiToken,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
        $this->_apiToken= $apiToken;
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

        $searchtapDashboard = $form->addFieldset(
            'dashboard_fieldset',
            ['legend' => __('Searchtap Dashboard')]
        );

        $searchtapDashboard->addField('link', 'link', array(
            'label'     => ('Searchtap Account'),
            'after_element_html'=>'<a href="https://magento-portal.searchtap.net/signup/" target="_blank" class="action-default primary" style="background-color: #e85d22;color: white;font-weight: 500;">Signup for the Searchtap Account</a>',
        ));

        $apiToken= $form->addFieldset(
            'token_fieldset',
            ['legend' => __('API Token')]
        );

        $apiToken->addField('api_token', 'textarea', array(
            'label'     => 'Token',
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'Token',
            'value'  => $this->_apiToken->getToken('api_token'),

        ));

        $apiToken->addField('dsubmit', 'submit', array(
            'required'  => true,
            'value'  => 'Save Token Credential',
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
