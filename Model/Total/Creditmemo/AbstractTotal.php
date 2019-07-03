<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

abstract class AbstractTotal extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    protected function canApplyTotal(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return false;
        }

        return true;
    }
}
