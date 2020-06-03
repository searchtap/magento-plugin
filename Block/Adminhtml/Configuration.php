<?php

namespace Bitqit\Searchtap\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Configuration extends Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_configuration';
        $this->_blockGroup = 'Bitqit_Searchtap';
        parent::_construct();
    }
}