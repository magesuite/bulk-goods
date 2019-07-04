<?php

namespace MageSuite\BulkGoods\Model\Total\Invoice;

abstract class AbstractTotal extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{
    protected function canApplyTotal(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return false;
        }

        return true;
    }
}
