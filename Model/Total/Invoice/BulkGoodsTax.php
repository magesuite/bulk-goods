<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoodsTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
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
        $taxFee = $order->getBulkGoodsFee() - $this->bulkGoods->getOrderFeeExclTax($order);

        if (!$invoice->isLast() && $invoice->getBulkGoodsFee() > 0) {
            $invoice->setTaxAmount($invoice->getTaxAmount() + $taxFee);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $taxFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $taxFee);
        }
    }
}
