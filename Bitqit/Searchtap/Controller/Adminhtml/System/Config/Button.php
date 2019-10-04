<?php
namespace Bitqit\Searchtap\Controller\Adminhtml\System\Config;

use \Magento\Catalog\Model\Product\Visibility;
use Bitqit\Searchtap\Helper\Api;

class Button extends \Magento\Backend\App\Action
{
    protected $_logger;
    protected $apiHelper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Api $apiHelper
    )
    {
        $this->apiHelper = $apiHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->apiHelper->requestToSyncStores();
    }
}