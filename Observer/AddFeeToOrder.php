<?php

namespace MageSuite\BulkGoods\Observer;

class AddFeeToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    public function __construct(\MageSuite\BulkGoods\Model\BulkGoods $bulkGoods)
    {
        $this->bulkGoods = $bulkGoods;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $fee = $this->bulkGoods->getBaseAmountWithTax($quote);

        if(!(float)$fee){
            return $this;
        }

        $order = $observer->getOrder();
        $order->setData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $fee);

        return $this;
    }
}
