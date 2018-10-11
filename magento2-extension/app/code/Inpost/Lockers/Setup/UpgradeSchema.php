<?php
/**
 * (c) InPost UK Ltd <it_support@inpost.co.uk>
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 *
 * Built by NMedia Systems Ltd, <info@nmediasystems.com>
 */

namespace Inpost\Lockers\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Upgrades DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $quote = 'quote';
        $orderTable = 'sales_order';
        if (version_compare($context->getVersion(), '0.0.2') < 0) {
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($quote),
                    'locker_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' =>'Locker ID'
                    ]
                );
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'locker_id',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' =>'Locker ID'
                    ]
                );

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('post_locker_machine_checkout_address'))
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true
                    ],
                    'Entity ID'
                )
                ->addColumn(
                    'shipping_address_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false
                    ],
                    'Quote Address ID'
                )
                    ->addColumn('locker_machine', Table::TYPE_TEXT, 255, ['nullable' => false], 'Machine Identifier')
                    ->addForeignKey(
                        $setup->getFkName(
                            $setup->getTable('post_locker_machine_checkout_address'),
                            'shipping_address_id',
                            'quote_address',
                            'address_id'
                        ),
                        'shipping_address_id',
                        $setup->getTable('quote_address'),
                        'address_id',
                        Table::ACTION_CASCADE
                    )
                ->setComment('Shipping Address Locker Machine Table');
            $setup->getConnection()->createTable($table);

            $setup->getConnection()->addIndex(
                $setup->getTable('post_locker_machine_checkout_address'),
                $setup->getIdxName(
                    'post_locker_machine_checkout_address',
                    ['entity_id']
                ),
                ['entity_id']
            );
        }
        if (version_compare($context->getVersion(), '0.0.4') < 0) {
            //Order table
            $setup->getConnection()
                ->addColumn(
                    $setup->getTable($orderTable),
                    'parcel_data',
                    [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        'comment' =>'Parcel Data'
                    ]
                );

            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '0.0.5') < 0) {
            $data = [];
            $statuses = [
                'inpost_shipped' => __('InPost Shipped'),
                'inpost_delivered' => __('InPost Delivered'),
                'inpost_missing'  => __('InPost Missing'),
                'inpost_stored'  => __('InPost Stored24'),
                'inpost_deliveredtoagency'  => __('InPost Delivered2Agency'),
                'inpost_notdelivered'  => __('InPost NotDelivered'),
                'inpost_canceled'  => __('InPost Canceled'),
                'inpost_expired'  => __('InPost Expired'),
                'inpost_returnedtoagency'  => __('InPost Returned2Agency'),
                'inpost_claimed'  => __('InPost Claimed'),
                'inpost_labelexpired'  => __('InPost LabelExpired'),
            ];
            foreach ($statuses as $code => $info) {
                $data[] = ['status' => $code, 'label' => $info];
            }
            $setup->getConnection()
                ->insertArray($setup->getTable('sales_order_status'), ['status', 'label'], $data);

            $states = [
                'complete' => [
                    'label' => __('Complete'),
                    'statuses' => [
                        'inpost_shipped' => ['default' => '0'],
                        'inpost_delivered' => ['default' => '0'],
                        'inpost_missing' => ['default' => '0'],
                        'inpost_stored' => ['default' => '0'],
                        'inpost_deliveredtoagency' => ['default' => '0'],
                        'inpost_notdelivered' => ['default' => '0'],
                        'inpost_canceled' => ['default' => '0'],
                        'inpost_expired' => ['default' => '0'],
                        'inpost_returnedtoagency' => ['default' => '0'],
                        'inpost_claimed' => ['default' => '0'],
                        'inpost_labelexpired' => ['default' => '0']
                    ],
                    'visible_on_front' => true,
                ]
            ];

            $data = [];
            foreach ($states as $code => $info) {
                if (isset($info['statuses'])) {
                    foreach ($info['statuses'] as $status => $statusInfo) {
                        $data[] = [
                            'status' => $status,
                            'state' => $code,
                            'is_default' => is_array($statusInfo) && isset($statusInfo['default']) ? 1 : 0,
                        ];
                    }
                }
            }
            $setup->getConnection()->insertArray(
                $setup->getTable('sales_order_status_state'),
                ['status', 'state', 'is_default'],
                $data
            );
        }
    }
}
