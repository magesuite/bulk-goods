<?php

namespace MageSuite\BulkGoods\Observer;

class AddFeeAsCustomItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration\BulkGoods
     */
    protected $configuration;

    public function __construct(\MageSuite\BulkGoods\Helper\Configuration\BulkGoods $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->configuration->isEnabled()){
            return $this;
        }

        $cart = $observer->getEvent()->getCart();
        $totals = $cart->getSalesModel()->getDataUsingMethod('totals');

        if(!isset($totals[\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE])){
            return $this;
        }

        $bulkGoodsTotal = $totals[\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE];

        if(!$bulkGoodsTotal->getValue()){
            return $this;
        }

        $cart->addCustomItem($this->configuration->getLabel(), 1, $bulkGoodsTotal->getValue(), $bulkGoodsTotal->getCode());
    }
}
