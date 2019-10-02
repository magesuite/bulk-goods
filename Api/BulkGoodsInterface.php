<?php

namespace MageSuite\BulkGoods\Api;

interface BulkGoodsInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return double
     */
    public function getBaseAmountWithTax($quote);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return double
     */
    public function getBaseAmount($quote);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param double $amount
     * @return double
     */
    public function getBaseTaxAmount($quote, $amount);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return int
     */
    public function getShippingTaxClassId();
}
