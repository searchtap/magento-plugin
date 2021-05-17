<?php


namespace Bitqit\Searchtap\Controller\Log;

class Index extends \Magento\Framework\App\Action\Action
{
   private $logFileName = "/var/log/searchtap.log";
   private $dataHelper;

   public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bitqit\Searchtap\Helper\SearchtapHelper $searchtapHelper,
        \Bitqit\Searchtap\Helper\Data $dataHelper
    )
    {
     	$this->searchtapHelper = $searchtapHelper;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }
    
    public function execute()
    {
     	try {
            $token = $this->getRequest()->getParam('token');

            if (!$this->dataHelper->checkPrivateKey($token)) {
              $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[403]);
              $this->getResponse()->setBody('Unauthorized Access');
              return;
            }

            $logs = file_get_contents(BP . $this->logFileName);
            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[200]);
            $this->getResponse()->setBody($logs);
        } catch (\Exception $e) {
             var_dump($e->getMessage());
        }
    }
}