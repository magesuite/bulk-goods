<?php
namespace MageSuite\BulkGoods\Plugin\Sales\Block\Order\Totals;

class AddFeeInCustomerArea
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    public function __construct(\MageSuite\BulkGoods\Model\BulkGoods $bulkGoods)
    {
        $this->bulkGoods = $bulkGoods;
    }

    public function aroundGetTotals(\Magento\Sales\Block\Order\Totals $subject, \Closure $proceed, $area = null)
    {
        $fee = $subject->getSource()->getBulkGoodsFee();

        if(!(float)$fee){
            return $proceed($area);
        }

        $bulkGoodsTotal = new \Magento\Framework\DataObject([
            'code' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'field' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'value' => $fee,
            'label' => $this->bulkGoods->getLabel()
        ]);

        $subject->addTotal($bulkGoodsTotal, 'shipping');

        return $proceed($area);
    }
}