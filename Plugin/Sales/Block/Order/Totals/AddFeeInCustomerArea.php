<?php
namespace MageSuite\BulkGoods\Plugin\Sales\Block\Order\Totals;

class AddFeeInCustomerArea
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods,
        \MageSuite\BulkGoods\Helper\Configuration $configuration
    ) {
        $this->bulkGoods = $bulkGoods;
        $this->configuration = $configuration;
    }

    public function beforeGetTotals(\Magento\Sales\Block\Order\Totals $subject, $area = null)
    {
        $fee = $subject->getSource()->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $tax = $subject->getSource()->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

        if (!(float)$fee) {
            return [$area];
        }

        $code = \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE;
        $field = \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE;
        $value = $fee;
        $label = $this->bulkGoods->getLabel();

        if ($this->configuration->getDisplayCartSubtotalType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_EXCLUDING_TAX) {
            $value = $fee - $tax;
        }

        if ($this->configuration->getDisplayCartSubtotalType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_BOTH) {
            $codeExcl = $code . "_excl";
            $valueExcl = $fee - $tax;
            $labelExcl = sprintf("%s %s", $label, __("(Excl. Tax)"));

            $code = $code . "_incl";
            $label = sprintf("%s %s", $label, __("(Incl. Tax)"));

            $bulkGoodsTotalExcl = new \Magento\Framework\DataObject([
                'code' => $codeExcl,
                'field' => $field,
                'value' => $valueExcl,
                'label' => $labelExcl
            ]);

            $subject->addTotal($bulkGoodsTotalExcl, 'shipping');
        }

        $bulkGoodsTotal = new \Magento\Framework\DataObject([
            'code' => $code,
            'field' => $field,
            'value' => $value,
            'label' => $label
        ]);

        $subject->addTotal($bulkGoodsTotal, 'shipping');

        return [$area];
    }
}
