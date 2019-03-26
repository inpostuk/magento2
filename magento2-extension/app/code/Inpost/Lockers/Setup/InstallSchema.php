<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    private $cron;
    public function __construct(
        \Inpost\Lockers\Cron\Machine $cron
    ) {
    
        $this->cron = $cron;
    }

    // @codingStandardsIgnoreStart
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    )
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('inpost_machine')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('inpost_machine')
            )
                ->addColumn(
                    'machine_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Machine ID'
                )
                ->addColumn(
                    'id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'API ID'
                )
                ->addColumn(
                    'post_code',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Post Code'
                )
                ->addColumn(
                    'province',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Province'
                )
                ->addColumn(
                    'street',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Street'
                )
                ->addColumn(
                    'city',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'City'
                )
                ->addColumn(
                    'building_no',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Building NO'
                )
                ->addColumn(
                    'flat_no',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Flat NO'
                )
                ->addColumn(
                    'address_str',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Address Street'
                )
                ->addColumn(
                    'functions',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Functions'
                )
                ->addColumn(
                    'location',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Location'
                )
                ->addColumn(
                    'latitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Latitude'
                )
                ->addColumn(
                    'longitude',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Longitude'
                )
                ->addColumn(
                    'location_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Location Description'
                )
                ->addColumn(
                    'location_description1',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Location Description1'
                )
                ->addColumn(
                    'location_description2',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Location Description2'
                )
                ->addColumn(
                    'operating_hours',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Operating Hours'
                )
                ->addColumn(
                    'payment_type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Payment Type'
                )
                ->addColumn(
                    'status',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Status'
                )
                ->addColumn(
                    'type',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Type'
                )
                ->addColumn(
                    'minimap',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Minimap'
                )
                ->addColumn(
                    'self',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    ['nullable => false'],
                    'Self'
                )
                ->setComment('Machines');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $setup->getTable('inpost_machine'),
                $setup->getIdxName(
                    $installer->getTable('inpost_machine'),
                    ['id'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['id'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        $this->cron->execute();
        $installer->endSetup();
    }
    // @codingStandardsIgnoreEnd
}
