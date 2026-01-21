<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoodsTax extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $bulkGoodsTax = (float) $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);
        $bulkGoodsTaxInvoiced = (float) $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_INVOICED_CODE);

        if ($bulkGoodsTax > 0 && $bulkGoodsTaxInvoiced === 0.0) {
            $this->applyBulkGoodsTax($invoice, $bulkGoodsTax);
        }

        return $this;
    }

    protected function applyBulkGoodsTax(\Magento\Sales\Api\Data\InvoiceInterface $invoice, float $bulkGoodsTax):void
    {
        $invoice->setBulkGoodsTax($bulkGoodsTax);
        $invoice->getOrder()->setBulkGoodsTaxInvoiced($bulkGoodsTax);
    }
}
