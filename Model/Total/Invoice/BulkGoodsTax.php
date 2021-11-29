<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoodsTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $taxFee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

        if (!$invoice->isLast() && $invoice->getBulkGoodsFee() > 0) {
            $invoice->setTaxAmount($invoice->getTaxAmount() + $taxFee);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $taxFee);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $taxFee);
        }
    }
}
