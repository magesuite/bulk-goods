<?php

namespace MageSuite\BulkGoods\Setup\Patch\Data;

class BulkGoodsTax implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /** @var \Magento\Framework\Setup\ModuleDataSetupInterface */
    private $moduleDataSetup;

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
        $sqlUpdate = 'UPDATE `sales_order` o, (SELECT `order_id`, SUM(`tax_amount`) as item_tax_sum FROM `sales_order_item` GROUP BY `order_id`) as i
SET o.`bulk_goods_tax` = (o.`tax_amount` - o.`shipping_tax_amount` - i.`item_tax_sum`)
WHERE o.`bulk_goods_fee` > 0 AND i.`order_id` = o.`entity_id`';
        $connection->query($sqlUpdate);

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
