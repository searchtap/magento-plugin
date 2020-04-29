<?php

namespace Bitqit\Searchtap\Model;

use Magento\Framework\Model\AbstractModel;

class Configuration extends AbstractModel
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Bitqit\Searchtap\Model\ResourceModel\Configuration');
    }


    /**
     * Get Token.
     *
     * @return varchar
     */
    public function getAPIToken()
    {
        return $this->getData('api_token');
    }

    /**
     * Set Token.
     */
    public function setAPIToken($token)
    {
        return $this->setData('api_token', $token);
    }
}