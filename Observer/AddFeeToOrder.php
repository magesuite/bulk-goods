<?php

namespace MageSuite\BulkGoods\Observer;

class AddFeeToOrder implements \Magento\Framework\Event\ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $order = $observer->getOrder();

        $fee = $quote->getBulkGoodsFee();

        if(!(float)$fee){
            return $this;
        }

        $order->setData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $fee);

        return $this;
    }
}
