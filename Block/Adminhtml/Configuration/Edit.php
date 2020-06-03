<?php

namespace Bitqit\Searchtap\Block\Adminhtml\Configuration;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

class Edit extends Container
{
   /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_configuration';
        $this->_blockGroup = 'Bitqit_Searchtap';
        parent::_construct();

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('back');
    }

    /**
     * Retrieve text for header element depending on loaded news
     * 
     * @return string
     */
    public function addButton($buttonId, $data, $level = 0, $sortOrder = 0, $region = 'toolbar')
    {
        if ($this->getRequest()->getParam('popup')) {
            $region = 'header';
        }
        parent::addButton($buttonId, $data, $level, $sortOrder, $region);
        return;
    }

    /**
     * Retrieve header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return $this->__('<a href="https://www.searchtap.io" target="_blank">Go To Web</a>');
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */

    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };
        ";

        return parent::_prepareLayout();
    }
}
