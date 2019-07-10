<?php

namespace MageSuite\BulkGoods\Observer;

class UpdateTaxTotal implements \Magento\Framework\Event\ObserverInterface
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
        $taxAmount = $this->bulkGoods->getBaseTaxAmount($observer->getQuote());

        if(empty($taxAmount)){
            return $this;
        }

        $total = $observer->getTotal();
        $total->addTotalAmount('tax', $taxAmount);

        return $this;
    }
}