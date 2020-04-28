<?php

namespace Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit;

use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use \Bitqit\Searchtap\Helper\Api;

class Tabs extends WidgetTabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected $_apiHelper;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Request\Http $request,
        Api $apiHelper,
        array $data = []
    ) {
        $this->_apiHelper = $apiHelper;
        $this->request = $request;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
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

        if($this->_apiHelper->getToken()){
            $this->addTab(
                'merchandise_info',
                [
                    'label' => __('Step 2: Settings'),
                    'title' => __('Step 2: Settings'),
                    'content' => $this->getLayout()->createBlock(
                        'Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab\Settings'
                    )->toHtml(),
                    'active' => false
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
