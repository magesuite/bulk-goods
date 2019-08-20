<?php

namespace MageSuite\BulkGoods\Model\Total\Quote;

class BulkGoods extends AbstractTotal
{
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        parent::__construct($request, $bulkGoods, $configuration, $priceCurrency);

        $this->setCode(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if(!$this->validateQuote($quote, $shippingAssignment)){
            return $this;
        }

        $baseAmount = $this->getBaseAmountWithTax($quote);
        $amount = $this->getConvertedAmount($baseAmount);

        if ($this->canApplyTotal($quote)) {

            $total->setTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $amount);
            $total->setBaseTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $baseAmount);

            $total->setBulkGoodsFee($amount);
            $total->setBaseBulkGoodsFee($baseAmount);

            $bulkGoodsTotal = $quote->getBulkGoodsFee();
            $balance = $amount - $bulkGoodsTotal;

            $quote->setGrandTotal($total->getGrandTotal() + $balance);
            $quote->setBaseGrandTotal($total->getBaseGrandTotal() + $balance);
        }

        $quote->setBaseBulkGoodsFee($baseAmount);
        $quote->setBulkGoodsFee($amount);

        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if ($this->canApplyTotal($quote)) {

            return [
                'code' => $this->getCode(),
                'title' => $this->getLabel(),
                'value' => $this->configuration->getSubtotalDisplayType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX ? $this->getBaseAmountWithTax($quote) : $this->getBaseAmount($quote)
            ];
        }

        return [];
    }

    public function getLabel()
    {
        return $this->bulkGoods->getLabel();
    }
}
