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
     * Get Title.
     *
     * @return varchar
     */
    public function getAPIToken()
    {
        return $this->getData('api_token');
    }

    /**
     * Set Api token.
     */
    public function setAPIToken($token)
    {
        return $this->setData('api_token', $token);
    }

    public function getDataCenter()
    {
        return json_decode($this->getData('store_datacenter'));
    }

    /**
     * Set Datacenter.
     */
    public function setDataCenter($data)
    {
        return $this->setData('store_datacenter', $data);
    }
}