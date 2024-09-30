<?php

declare(strict_types=1);
namespace MageSuite\BulkGoods\Model\Total\Invoice;

class BulkGoods extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    protected \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods;

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
        $bulkGoodsFee = (float) $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $bulkGoodsFeeInvoiced = (float) $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_INVOICED_CODE);

        if ($bulkGoodsFee > 0 && $bulkGoodsFeeInvoiced === 0.0) {
            $this->applyBulkGoodsFee($invoice, $bulkGoodsFee);
        }

        return $this;
    }

    protected function applyBulkGoodsFee(\Magento\Sales\Api\Data\InvoiceInterface $invoice, float $bulkGoodsFee):void
    {
        $order = $invoice->getOrder();
        $invoice->setBulkGoodsFee($bulkGoodsFee);
        $order->setBulkGoodsFeeInvoiced($bulkGoodsFee);
        $bulkGoodsFeeExclTax = $this->bulkGoods->getOrderFeeExclTax($order);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $bulkGoodsFeeExclTax);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $bulkGoodsFeeExclTax);
    }
}
