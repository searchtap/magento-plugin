<?php

namespace Bitqit\Searchtap\Controller\Adminhtml\Configuration;

use Bitqit\Searchtap\Controller\Adminhtml\Configuration;
use Bitqit\Searchtap\Model\System\Config\Status;
use MongoDB\Driver\Exception\Exception;

class Save extends Configuration
{

    public function execute()
    {
        try {
            $post = $this->getRequest()->getPost();
            if (!$post) return;

            $formData = $this->getRequest()->getParams();
            $apiToken = $formData["api_token"];
            $dataCenters = [];

            //Formatting the data center values in the required format
            foreach ($formData as $key => $value) {
                if (strpos($key, "store_") !== false)
                    if ($value) $dataCenters[str_replace("store_", "", $key)] = $value;
            }

            if (in_array("Save and Sync Stores", $formData) && empty($dataCenters)) {
                $this->messageManager->addError(__('Please select data center !!'));
                $this->_getSession()->setFormData($formData);
                $this->_redirect('*/*/edit');
                return;
            }

            //Send request to sync stores
            if ($dataCenters && count($dataCenters) > 0)
                $this->_apiHelper->requestToSyncStores($dataCenters);

            //Save updated data
            $this->_configurationFactory->create()->setConfiguration($apiToken, $dataCenters);

            if (in_array("Save API Token", $formData))
                $this->messageManager->addSuccess(__('Api token has been saved successfully'));
            else
                $this->messageManager->addSuccess(__('Data centers have been saved successfully. <a href="https://magento.searchtap.io" target="_blank">Please go to SearchTap Dashboard.</a>'));

            $this->_getSession()->setFormData($formData);

            $this->_redirect('*/*/edit');

        } catch (Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
        }
    }
}
