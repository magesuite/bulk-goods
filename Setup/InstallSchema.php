<?php

namespace MageSuite\BulkGoods\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    public function install(\Magento\Framework\Setup\SchemaSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $setup->startSetup();

        $setup
            ->getConnection()
            ->addColumn(
                $setup->getTable('quote'),
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Bulk Goods Fee'
                ]
            );

        $setup
            ->getConnection()
            ->addColumn(
                $setup->getTable('sales_order'),
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Bulk Goods Fee'
                ]
            );

        $setup
            ->getConnection()
            ->addColumn(
                $setup->getTable('sales_invoice'),
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Bulk Goods Fee'
                ]
            );

        $setup
            ->getConnection()
            ->addColumn(
                $setup->getTable('sales_creditmemo'),
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
                    'nullable' => true,
                    'length' => '12,4',
                    'default' => '0.0000',
                    'comment' => 'Bulk Goods Fee'
                ]
            );

        $setup->endSetup();
    }
}
