<?php

namespace MageSuite\BulkGoods\Api;

interface BulkGoodsInterface
{
    /**
     * Get base amount with tax
     * @param \Magento\Quote\Model\Quote $quote
     * @return double
     */
    public function getBaseAmountWithTax($quote);

    /**
     * Get base amount
     * @param \Magento\Quote\Model\Quote $quote
     * @return double
     */
    public function getBaseAmount($quote);

    /**
     * Get base tax amount
     * @param double $amount
     * @return double
     */
    public function getBaseTaxAmount($amount);

    /**
     * Get label
     * @return string
     */
    public function getLabel();
}
