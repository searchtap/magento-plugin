<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Bitqit\Searchtap\Observer;

//use ;
use Magento\Framework\Event\Observer;
use \Magento\Framework\Message\ManagerInterface;

class FlushImageCache implements \Magento\Framework\Event\ObserverInterface
{

    protected $messageManager;

    public function __construct(
        ManagerInterface $messageManager
    )
    {
        $this->messageManager = $messageManager;
    }

    /**
     * Flash Catalog cache
     */
    public function execute(Observer $observer)
    {
        $this->messageManager->addWarning(__('The images on search page will not load as the urls will be non-existence, please run product sync from SearchTap Dashboard. <a href="https://magento-portal.searchtap.net" target="_blank">Click here.</a>'));
    }
}
