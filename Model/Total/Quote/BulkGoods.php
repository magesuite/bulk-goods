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
        if (!$this->validateQuote($quote, $shippingAssignment) || !$this->canApplyTotal($quote)) {
            return $this;
        }

        $baseAmount = $this->getBaseAmount($quote);
        $amount = $this->getConvertedAmount($baseAmount);

        $total->setBaseTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $amount);
        $total->setTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $baseAmount);

        $total->setBaseBulkGoodsFee($baseAmount);
        $total->setBulkGoodsFee($amount);

        $quote->setBaseBulkGoodsFee($baseAmount);
        $quote->setBulkGoodsFee($amount);

        $taxableQuoteAssociate = [
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $baseAmount,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $baseAmount,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID => $this->bulkGoods->getShippingTaxClassId(),
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => 0,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE => \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE
        ];

        $shippingAssignment->getShipping()->getAddress()->setAssociatedTaxables(array_merge(
            [$taxableQuoteAssociate],
            $shippingAssignment->getShipping()->getAddress()->getAssociatedTaxables() ?: []
        ));

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
