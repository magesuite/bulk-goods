<?php

namespace MageSuite\BulkGoods\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetupInterface
    ){
        $this->eavSetup = $eavSetupFactory->create(['setup' => $moduleDataSetupInterface]);
    }

    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        $this->eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'input' => 'boolean',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'label' => 'Is Bulk Good',
                'group' => 'General',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => 1,
                'required' => 0,
                'user_defined' => 1,
                'default' => '',
                'searchable' => 0,
                'filterable' => 0,
                'filterable_in_search' => 0,
                'comparable' => 0,
                'visible_on_front' => 0,
                'used_in_product_listing' => 0,
                'unique' => 0,
                'sort_order' => 420
            ]
        );
    }
}
