<?php

namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->dropTable('searchtap_queue');
        $connection->dropTable('searchtap_config');
        
        $installer->endSetup();
        
        echo "Table Deleted successfully !!";
    }
}
