<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoodsTax extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $invoice->setBulkGoodsTax($order->getBulkGoodsTax());

        return $this;
    }
}
