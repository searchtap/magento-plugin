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
                    $dataCenters[str_replace("store_", "", $key)] = $value;
            }

            //Send request to sync stores
            if ($dataCenters && count($dataCenters) > 0)
                $this->_apiHelper->requestToSyncStores($dataCenters);

            //Save updated data
            $this->_configurationFactory->create()->setConfiguration($apiToken, $dataCenters);

            if (in_array("Save API Token", $formData)) {
                $this->messageManager->addSuccess(__('Settings have been saved successfully'));
            } else {
                $this->messageManager->addSuccess(__('Setting Saved, Please Go to Searchtap Dashboard <a href="https://magento-portal.searchtap.net" target="_blank">Link</a>'));
            }
            $this->_getSession()->setFormData($formData);

            $this->_redirect('*/*/edit');

        } catch (Exception $exception) {
            $this->messageManager->addError($exception->getMessage());
        }
    }
}
