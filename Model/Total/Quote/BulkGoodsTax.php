<?php

namespace MageSuite\BulkGoods\Model\Total\Quote;

class BulkGoodsTax extends AbstractTotal
{
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if(!$this->validateQuote($quote, $shippingAssignment)){
            return $this;
        }

        $baseTaxAmount = $this->getBaseTaxAmount($quote);
        $taxAmount = $this->getConvertedAmount($baseTaxAmount);

        if ($this->canApplyTotal($quote)) {
            $total->setTaxAmount($total->getTaxAmount() + $taxAmount);
            $total->setBaseTaxAmount($total->getBaseTaxAmount() + $baseTaxAmount);
        }

        $quote->setBaseBulkGoodsTax($baseTaxAmount);
        $quote->setBulkGoodsTax($taxAmount);

        return $this;
    }
}