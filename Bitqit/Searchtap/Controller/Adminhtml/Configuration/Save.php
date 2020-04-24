<?php

namespace Bitqit\Searchtap\Controller\Adminhtml\Configuration;

use Bitqit\Searchtap\Controller\Adminhtml\Configuration;

class Save extends Configuration
{
    /**
     * @return void
     */
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $Model = $this->_newsFactory->create();
            $Id = $this->getRequest()->getParam('id');

            if ($Id) {
                $Model->load($Id);
            }
            $formData = $this->getRequest()->getParam('news');
            $Model->setData($formData);

            try {
                // Save news
                $Model->save();

                // Display success message
                $this->messageManager->addSuccess(__('Saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $Model->getId(), '_current' => true]);
                    return;
                }

                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
            $this->_redirect('*/*/edit', ['id' => $Id]);
        }
    }
}