<?php

namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('configuration_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Searchtap Configuration'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'config_info',
            [
                'label' => __('Step 1: API Token'),
                'title' => __('Step 1: API Token'),
                'content' => $this->getLayout()->createBlock(
                    'Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab\Info'
                )->toHtml(),
                'active' => true
            ]
        );
        $this->addTab(
            'merchandise_info',
            [
                'label' => __('Step 2: Settings'),
                'title' => __('Step 2: Settings'),
                'content' => $this->getLayout()->createBlock(
                    'Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab\Setting'
                )->toHtml(),
                'active' => false
            ]
        );

        return parent::_beforeToHtml();
    }
}
