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

    public function aroundGetTotals(\Magento\Sales\Block\Order\Totals $subject, \Closure $proceed, $area = null)
    {
        $fee = $subject->getSource()->getBulkGoodsFee();

        if (!(float)$fee) {
            return $proceed($area);
        }

        $bulkGoodsTotal = new \Magento\Framework\DataObject([
            'code' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'field' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'value' => $this->configuration->getSubtotalDisplayType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX ? $fee : $this->configuration->getFee(),
            'label' => $this->bulkGoods->getLabel()
        ]);

        $subject->addTotal($bulkGoodsTotal, 'shipping');

        return $proceed($area);
    }
}
