<?php

namespace Bitqit\Searchtap\Model;

use Magento\Framework\Model\AbstractModel;

class Configure extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Configure');
    }


    /**
     * Get Title.
     *
     * @return varchar
     */
    public function getAPIToken()
    {
        return $this->getData('api_token');
    }

    /**
     * Set Title.
     */
    public function setAPIToken($token)
    {
        return $this->setData('api_token', $token);
    }
}