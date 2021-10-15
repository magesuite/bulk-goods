<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

class BulkGoods extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    public function __construct(
        \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods,
        array $data = []
    ) {
        $this->bulkGoods = $bulkGoods;
        parent::__construct($data);
    }

    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $creditmemo->setBulkGoodsFee($order->getBulkGoodsFee());

        $bulkGoodsFeeExclTax = $this->bulkGoods->getOrderFeeExclTax($order);
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $bulkGoodsFeeExclTax);
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $bulkGoodsFeeExclTax);

        return $this;
    }
}
