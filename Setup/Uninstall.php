<?php

namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Bitqit\Searchtap\Helper\Api;

class Uninstall implements UninstallInterface
{
    protected $_apiHelper;

    public function __construct(Api $apiHelper)
    {
        $this->_apiHelper = $apiHelper;
    }

    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // Send store delete request to SearchTap
        $this->_apiHelper->notifyUninstall();

        // Drop SearchTap config tables
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();
        $connection->dropTable('searchtap_queue');
        $connection->dropTable('searchtap_config');
        $installer->endSetup();
    }
}
