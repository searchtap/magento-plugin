<?php

namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.3.0', '<=')) {
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
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                     100,
                    ['nullable' => false],
                    'Action'
                )
                ->addColumn(
                    'Status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                     100,
                    ['nullable' => false],
                    'Status (pending/ processing/ finished)'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                     100,
                    ['nullable'=> false],
                    'Type'
                )
                ->addColumn(
                    'store',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 
                    100,
                    ['nullable'=> false],
                    'Store'
                 )->setComment("SearchTap Queue Table");
            $setup->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}
