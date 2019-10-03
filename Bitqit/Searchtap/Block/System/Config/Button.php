<?php

namespace Bitqit\Searchtap\Block\System\Config;

use Bitqit\Searchtap\Helper\ConfigHelper;

class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_template = 'Bitqit_Searchtap::system/config/Button.phtml';
    protected $configHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        ConfigHelper $configHelper,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }
    public function getAjaxUrl()
    {
        return $this->getUrl('searchtap/system_config/button');
    }
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'btnid',
                'label' => __('Sync Stores'),
            ]
        );

        return $button->toHtml();
    }
}