<?php
 
namespace Bitqit\Searchtap\Controller\Adminhtml\dashboard;
 
class Index extends \Magento\Backend\App\Action
{
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute()
    {
        echo __METHOD__;
        exit;
    }
}
