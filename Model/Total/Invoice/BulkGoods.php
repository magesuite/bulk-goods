<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoods extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $fee = $order->getBulkGoodsFee();
        $feeExclTax = $this->bulkGoods->getOrderFeeExclTax($order);

        $invoice->setBulkGoodsFee($fee);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $feeExclTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $feeExclTax);

        return $this;
    }
}
