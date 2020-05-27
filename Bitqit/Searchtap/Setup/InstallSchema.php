<?php

namespace Bitqit\Searchtap\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    protected $_pageFactory;
    
    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
    }
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $page = $this->_pageFactory->create();
        $page->setTitle('Search')
            ->setIdentifier('st-search')
            ->setIsActive(true)
            ->setPageLayout('1column')
            ->setStores(array(0))
            ->save();

        $queuetableName = $setup->getTable('searchtap_queue');
        $configTableName = $setup->getTable('searchtap_config');

        if ($setup->getConnection()->isTableExists($queuetableName) != true && $setup->getConnection()->isTableExists($configTableName) != true) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('searchtap_queue'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false
                    ],
                    'Entity ID'
                )
                ->addColumn(
                    'action',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                    'Action'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                    'Status'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => false
                    ],
                    'Type'
                )
                ->addColumn(
                    'store',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    100,
                    [
                        'nullable' => true
                    ],
                    'Store IDs'
                )->setComment("SearchTap Queue Table");


            $configTable = $setup->getConnection()
                ->newTable($setup->getTable('searchtap_config'))
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'api_token',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'API Token'
                )
                ->addColumn(
                    'data_centers',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Store Data Center'
                )->setComment("SearchTap Config Table");

            $setup->getConnection()->createTable($table);
            $setup->getConnection()->createTable($configTable);
        }

        $setup->endSetup();
    }
}

