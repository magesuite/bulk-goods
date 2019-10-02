<?php

namespace MageSuite\BulkGoods\Observer;

class AddFeeAsCustomItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\BulkGoods\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return $this;
        }

        $cart = $observer->getEvent()->getCart();
        $payment = $cart->getSalesModel()->getDataUsingMethod('payment');
        $totals = $cart->getSalesModel()->getDataUsingMethod('totals');

        $bulkGoodsFee = 0;

        if (isset($totals[\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE])) {
            $bulkGoodsTotal = $totals[\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE];

            if ($bulkGoodsTotal->getValue()) {
                $bulkGoodsFee = $bulkGoodsTotal->getValue();
            }
        }

        if (!$bulkGoodsFee && $payment->getMethod() == \Magento\Paypal\Model\Config::METHOD_EXPRESS) {
            $bulkGoodsFee = $cart->getSalesModel()->getDataUsingMethod(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        }

        if (!$bulkGoodsFee) {
            return $this;
        }

        $cart->addCustomItem($this->configuration->getLabel(), 1, $bulkGoodsFee, \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
    }
}
