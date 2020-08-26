<?php

namespace Bitqit\Searchtap\Controller\Configuration;

use \Bitqit\Searchtap\Helper\Data as DataHelper;
use \Bitqit\Searchtap\Helper\SearchtapHelper as SearchtapHelper;

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
$productMetadata = $objectManager->get('Magento\Framework\App\ProductMetadataInterface');
$version = $productMetadata->getVersion();

if ($version < 2.3) {
    class Index extends \Magento\Framework\App\Action\Action
    {
        private $dataHelper;
        private $searchtapHelper;

        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            DataHelper $dataHelper,
            SearchtapHelper $searchtapHelper
        )
        {
            $this->dataHelper = $dataHelper;
            $this->searchtapHelper = $searchtapHelper;
            parent::__construct($context);
        }

        public function execute()
        {
            try{
            $data = $this->getRequest()->getContent();
            $token = $this->getRequest()->getHeader("authorization");

            $response = $this->dataHelper->setJSConfiguration($token, json_decode($data));

            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
            $this->getResponse()->setBody($response["output"]);
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
    }
}
else {
    class Index extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
    {
        private $dataHelper;
        private $searchtapHelper;

        public function __construct(
            \Magento\Framework\App\Action\Context $context,
            DataHelper $dataHelper,
            SearchtapHelper $searchtapHelper
        )
        {
            $this->dataHelper = $dataHelper;
            $this->searchtapHelper = $searchtapHelper;
            parent::__construct($context);
        }

        public function execute()
        {
            try{
            $data = $this->getRequest()->getContent();
            $token = $this->getRequest()->getHeader("authorization");

            $response = $this->dataHelper->setJSConfiguration($token, json_decode($data));

            $this->getResponse()->setHeader('content-type', 'application/json');
            $this->getResponse()->setStatusCode($this->searchtapHelper->getStatusCodeList()[$response["statusCode"]]);
            $this->getResponse()->setBody($response["output"]);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
