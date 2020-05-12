<?php

namespace Bitqit\Searchtap\Controller\Results;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action {
    protected $pageFactory;

    public function __construct(Context $context, PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute() {
        $page = $this->pageFactory->create();
        $page->getConfig()->getTitle()->set("Search Results");
        return $page;
    }
}