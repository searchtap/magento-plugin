<?php

namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Bitqit\Searchtap\Helper\Api;

class Uninstall implements UninstallInterface
{
    protected $_apiHelper;

    public function __construct(Api $Api)
    {
        $this->_apiHelper = $Api;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->dropTable('searchtap_queue');
        $connection->dropTable('searchtap_config');
        $installer->endSetup();
        $result=$this->_apiHelper->notifyUninstall();
        if($result==='OK'){
            echo "Request send for delete stores to searchtap..";
        }
    }
}
