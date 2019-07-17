<?php

/**
* Copyright © 2019 Searchtap. All rights reserved.
* See st_cert/searchtap.io.crt for license details.
*/
namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('searchtap_queue');
        if ($installer->getConnection()->isTableExists($tableName) != true) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('searchtap_queue'))
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'primary' => true],
                    'Entity ID'
                )
                ->addColumn(
                    'action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
                    [
                        'nullable' => false
                    ],
                    'Action'
                )
                ->addColumn(
                    'Status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100,
                    [
                        'nullable' => false
                    ],
                    'Status (pending/ processing/ finished)'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,100,
                    null,
                    [
                        'nullable'=> false
                    ],
                    'Type'
                )->setComment("SearchTap Queue Table");
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
