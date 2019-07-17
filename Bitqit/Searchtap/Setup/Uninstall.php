<?php
/**
* Copyright © 2019 Searchtap. All rights reserved.
* See st_cert/searchtap.io.crt for license details.
*/
namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * Searchtap Module Uninstall Code
     *
     * @return void
     */

    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
        $connection = $setup->getConnection();
        $connection->dropTable($connection->getTableName('searchtap_queue'));
        $setup->endSetup();
    }
}
