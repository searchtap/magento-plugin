<?php

namespace Bitqit\Searchtap\Controller\Adminhtml\Configuration;

use Bitqit\Searchtap\Controller\Adminhtml\Configuration;

class Edit extends Configuration
{
   /**
     * @return void
     */
   public function execute()
   {
        $model = $this->_configFactory->create();

        // Restore previously entered form data from session
        $data = $this->_session->getNewsData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->_coreRegistry->register('searchtap_configuration', $model);


        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Bitqit_Searchtap::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Searchtap'));

        return $resultPage;
   }
}
