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
            $Model = $this->_configFactory->create();
            $Model->load(1);
            $formData = $this->getRequest()->getParams('api_token');

            $Model->setAPIToken($formData['configuration']['api_token']);
            try {
                $Model->save();
                // Display success message
                $this->messageManager->addSuccess(__('Searchtap Configuration Saved !'));
                // Go to grid page
                $this->_redirect('*/*/edit');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
        }
    }
}