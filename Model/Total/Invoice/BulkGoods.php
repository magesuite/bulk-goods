<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoods extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $invoice->setBulkGoodsFee($order->getBulkGoodsFee());

        if ($this->canApplyTotal($order)) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getBulkGoodsFee());
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBulkGoodsFee());
        }

        return $this;
    }
}
