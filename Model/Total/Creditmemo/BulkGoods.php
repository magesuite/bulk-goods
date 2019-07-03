<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

class BulkGoods extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $creditmemo->setBulkGoodsFee($order->getBulkGoodsFee());

        if ($this->canApplyTotal($order)) {
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getBulkGoodsFee());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBulkGoodsFee());
        }

        return $this;
    }
}
