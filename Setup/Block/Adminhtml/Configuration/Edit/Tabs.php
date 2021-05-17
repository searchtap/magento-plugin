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
    protected $_dataHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\App\Request\Http $request,
        \Bitqit\Searchtap\Helper\Data $dataHelper,
        Api $apiHelper,
        array $data = []
    )
    {
        $this->_apiHelper = $apiHelper;
        $this->request = $request;
        $this->_dataHelper = $dataHelper;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _construct()
    {
        $this->setId('configuration_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('SearchTap Configuration'));
        parent::_construct();
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'st-api-token',
            [
                'label' => __('Step 1: API Token'),
                'title' => __('Step 1: API Token'),
                'content' => $this->getLayout()
                    ->createBlock('Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab\ApiToken')
                    ->toHtml(),
                'active' => true
            ]
        );

        if ($this->_dataHelper->getCredentials()) {
            $this->addTab(
                'st-store-settings',
                [
                    'label' => __('Step 2: Settings'),
                    'title' => __('Step 2: Settings'),
                    'content' => $this->getLayout()
                        ->createBlock('Bitqit\Searchtap\Block\Adminhtml\Configuration\Edit\Tab\Settings')
                        ->toHtml(),
                    'active' => false
                ]
            );
        }

        return parent::_beforeToHtml();
    }
}
