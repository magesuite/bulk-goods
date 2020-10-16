<?php

namespace MageSuite\BulkGoods\Api;

interface BulkGoodsInterface
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param bool $force
     * @return double
     */
    public function getBaseAmountWithTax($quote, $force);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @return double
     */
    public function getBaseAmount($quote);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param double $amount
     * @param bool $force
     * @return double
     */
    public function getBaseTaxAmount($quote, $amount, $force);

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return int
     */
    public function getShippingTaxClassId();

    /**
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getOrderFeeExclTax(\Magento\Sales\Model\Order $order);
}
