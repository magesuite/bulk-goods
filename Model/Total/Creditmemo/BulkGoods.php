<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

class BulkGoods extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $creditmemo->setBulkGoodsFee($order->getBulkGoodsFee());

        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getBulkGoodsFee());
        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBulkGoodsFee());

        return $this;
    }
}
