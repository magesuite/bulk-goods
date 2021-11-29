<?php

namespace MageSuite\BulkGoods\Model\Total\Quote;

class BulkGoods extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \MageSuite\BulkGoods\Api\BulkGoodsInterface
     */
    protected $bulkGoods;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods,
        \MageSuite\BulkGoods\Helper\Configuration $configuration
    ) {
        $this->bulkGoods = $bulkGoods;
        $this->configuration = $configuration;

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

        $amount = $this->bulkGoods->getBaseAmount($quote);

        $total->setBaseTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $amount);
        $total->setTotalAmount(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $amount);

        $total->setBaseBulkGoodsFee($amount);
        $total->setBulkGoodsFee($amount);

        $quote->setBaseBulkGoodsFee($amount);
        $quote->setBulkGoodsFee($amount);

        $taxableQuoteAssociate = [
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TYPE =>
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_CODE =>
                \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_UNIT_PRICE => $amount,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_BASE_UNIT_PRICE => $amount,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_QUANTITY => 1,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_TAX_CLASS_ID =>
                $this->bulkGoods->getShippingTaxClassId(),
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_PRICE_INCLUDES_TAX => 0,
            \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::KEY_ASSOCIATED_TAXABLE_ASSOCIATION_ITEM_CODE =>
                \Magento\Tax\Model\Sales\Total\Quote\CommonTaxCollector::ASSOCIATION_ITEM_CODE_FOR_QUOTE
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
                'value' => $this->configuration->getDisplayCartSubtotalType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX ?
                    $this->bulkGoods->getBaseAmountWithTax($quote) : $this->bulkGoods->getBaseAmount($quote)
            ];
        }

        return [];
    }

    public function getLabel()
    {
        return $this->bulkGoods->getLabel();
    }

    protected function validateQuote($quote, $shippingAssignment)
    {
        $addressType = $shippingAssignment->getShipping()->getAddress()->getAddressType();

        if ($addressType != \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING || $quote->isVirtual()) {
            return false;
        }

        return true;
    }

    protected function canApplyTotal(\Magento\Quote\Model\Quote $quote)
    {
        if (!$this->configuration->isEnabled() || !$quote->getId()) {
            return false;
        }

        $fee = (float)$quote->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $amount = $this->bulkGoods->getBaseAmount($quote);

        if (!$fee && !$amount) {
            return false;
        }

        return true;
    }
}
