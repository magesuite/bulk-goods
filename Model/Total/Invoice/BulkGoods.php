<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoods extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
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

    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $invoice->setBulkGoodsFee($order->getBulkGoodsFee());

        $bulkGoodsFeeExclTax = $this->bulkGoods->getOrderFeeExclTax($order);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $bulkGoodsFeeExclTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $bulkGoodsFeeExclTax);

        return $this;
    }
}
