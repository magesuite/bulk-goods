<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

class BulkGoods extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();
        $fee = $order->getBulkGoodsFee();
        $feeExclTax = $this->bulkGoods->getOrderFeeExclTax($order);

        $creditmemo->setBulkGoodsFee($fee);

        if ($this->canApplyTotal($order)) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeExclTax);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $feeExclTax);
        }

        return $this;
    }
}
