<?php

namespace Bitqit\Searchtap\Controller\Adminhtml\Configuration;

use Bitqit\Searchtap\Controller\Adminhtml\Configuration;

//use Bitqit\Searchtap\Model\ConfigureFactory;

class Save extends Configuration
{
    /**
     * @return void
     */

    public function execute()
    {
        $isPost = $this->getRequest()->getPost();
        if ($isPost) {
            $model = $this->_configFactory->create();
            $model->load(1);
            $formData = $this->getRequest()->getParams();
            try {
                switch ($formData['searchtap_credential']) {
                    case 'Save API Token':
                        $model->setAPIToken($formData['api_token']);
                        $model->save();
                        if ($this->_apiHelper->getDataCenterList()){
                            $this->messageManager->addSuccess(__('Searchtap API Token Saved'));
                        }
                        else{
                            $this->messageManager->addError(__("Invalid Token !!!"));
                        }

                        break;

                    case 'Save and Sync Store':
                        $dataCenter = [];
                        foreach ($formData as $key => $value) {
                            if (strpos($key, "store_") !== false) {
                                $dataCenter[substr($key, 6)] = $value;
                            }
                        }
                        $model->setDataCenter(json_encode($dataCenter));
                        $model->save();

                        $this->_apiHelper->requestToSyncStores();

                        $this->messageManager->addSuccess(__('Searchtap Setting Saved & Store Synced'));
                        break;
                }

                $this->_redirect('*/*/edit');
                return;

            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
        }
    }
}