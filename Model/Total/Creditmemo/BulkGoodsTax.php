<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

class BulkGoodsTax extends AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $creditmemo->setBulkGoodsTax($order->getBulkGoodsTax());

        return $this;
    }
}
