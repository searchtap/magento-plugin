<?php

namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config;
use Bitqit\Searchtap\Model\System\Config\Status;
use \Bitqit\Searchtap\Helper\Data;

class ApiToken extends Generic implements TabInterface
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    protected $_newsStatus;
    protected $_dataHelper;


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
        Data $dataHelper,
        array $data = []
    )
    {
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_newsStatus = $newsStatus;
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
        $model = $this->_coreRegistry->registry('searchtap_configuration');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]);

        $searchtapDashboard = $form->addFieldset(
            'st-dashboard',
            ['legend' => __('SearchTap Dashboard')]
        );

        if ($model->getId()) {
            $searchtapDashboard->addField(
                'id',
                'hidden',
                ['name' => 'id']
            );
        }

        $searchtapDashboard->addField('note', 'note', array(
            'label' => __('SearchTap Account'),
            'text' => '<img src=\'https://d33wubrfki0l68.cloudfront.net/f8230a812b6bff599763387cf815e960bf1625e2/c8b3c/img/logo-dark.png\' alt="Searchtap" width="125" />' . '<br>',
        ));

        $searchtapDashboard->addField('link', 'link', array(
            'after_element_html' => '<a href="https://magento-portal.searchtap.net/signup/" target="_blank" class="action-default primary" style="background-color: #e85d22;color: white;font-weight: 500;padding-bottom: 0.6875em; padding-top: 0.6875em;">
             Signup for a SearchTap Account</a>',
        ));

        /*
         * API Token
         */
        $apiToken = $form->addFieldset(
            'st-api-token',
            ['legend' => __('API Token')]
        );

        $token = json_encode($this->_dataHelper->getCredentials());
        $apiToken->addField('api_token', 'textarea', array(
            'label' => 'Token',
            'class' => 'required-entry',
            'required' => true,
            'name' => 'api_token',
            'value' => $token ? $token : ""
        ));

        /*
         * Save button
         */
        $apiToken->addField('submit', 'submit', array(
            'required' => true,
            'value' => 'Save API Token',
            'name' => 'st-save-token',
            'style' => 'background: #e85d22;border-color: #e85d22;color: #ffffff; width: 40%;padding-bottom: 0.6875em; padding-top: 0.6875em;'));

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
        return __('SearchTap Dashboard');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('SearchTap Dashboard');
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