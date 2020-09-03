<?php

namespace Bitqit\Searchtap\Controller\Log;

class File extends \Magento\Framework\App\Action\Action
{
   
    private $logFileName = "./var/log/searchtap.log";

    public function execute()
    {
        try {
         $logs = file_get_contents($this->logFileName);
         echo $logs;   
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
