<?php

namespace MageSuite\BulkGoods\Setup\Patch\Data;

class BulkGoodsTax implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    protected $moduleDataSetup;

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $connection = $this->moduleDataSetup->getConnection();

        $select = $connection->select()
            ->from(
                ['o' => $this->moduleDataSetup->getTable('sales_order')],
                ['o.entity_id', 'o.tax_amount', 'o.shipping_tax_amount']
            )
            ->joinLeft(
                ['i' => $this->moduleDataSetup->getTable('sales_order_item')],
                'i.order_id = o.entity_id',
                ['item_tax_sum' => new \Zend_Db_Expr('SUM(i.tax_amount)')]
            )
            ->where('o.bulk_goods_fee > 0')
            ->group('o.entity_id');

        $bulkGoodsSelect = $connection->fetchAll($select);

        if (empty($bulkGoodsSelect)) {
            $this->moduleDataSetup->endSetup();
            return;
        }

        $updateData = [];
        foreach ($bulkGoodsSelect as $row) {
            $updateData[$row['entity_id']] = $row['tax_amount'] - $row['shipping_tax_amount'] - $row['item_tax_sum'];
        }

        $conditions = [];
        foreach ($updateData as $id => $bulkGoodsTax) {
            $case = $connection->quoteInto('?', $id);
            $result = $connection->quoteInto('?', $bulkGoodsTax);
            $conditions[$case] = $result;
        }

        $value = $connection->getCaseSql('entity_id', $conditions, 'bulk_goods_tax');
        $where = ['entity_id IN (?)' => array_keys($updateData)];

        try {
            $connection->beginTransaction();
            $connection->update($this->moduleDataSetup->getTable('sales_order'), ['bulk_goods_tax' => $value], $where);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '1.0.1';
    }
}
