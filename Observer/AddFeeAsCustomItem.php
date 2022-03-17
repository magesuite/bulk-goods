<?php

namespace MageSuite\BulkGoods\Observer;

class AddFeeAsCustomItem implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    public function __construct(
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \Magento\Tax\Model\Config $taxConfig
    ) {
        $this->configuration = $configuration;
        $this->taxConfig = $taxConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->configuration->isEnabled()) {
            return;
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
            $bulkGoodsFee = (float)$cart->getSalesModel()->getDataUsingMethod(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        }

        if (!$bulkGoodsFee) {
            return;
        }

        if (!$this->taxConfig->shippingPriceIncludesTax()) {
            $bulkGoodsFee = $this->configuration->getFee();
        }

        $cart->addCustomItem($this->configuration->getLabel(), 1, $bulkGoodsFee, \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
    }
}
