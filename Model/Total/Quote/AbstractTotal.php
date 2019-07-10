<?php

namespace MageSuite\BulkGoods\Model\Total\Quote;

abstract class AbstractTotal extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    const PAYPAL_METHOD_CODE = 'express';

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \MageSuite\BulkGoods\Api\BulkGoodsInterface
     */
    protected $bulkGoods;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected $paypalActionNames = ['placeOrder', 'start'];

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency

    ){
        $this->request = $request;
        $this->bulkGoods = $bulkGoods;
        $this->configuration = $configuration;
        $this->priceCurrency = $priceCurrency;
    }

    protected function validateQuote($quote, $shippingAssignment)
    {
        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() != \Magento\Quote\Model\Quote\Address::TYPE_SHIPPING || $quote->isVirtual()) {
            return false;
        }

        return true;
    }

    protected function getBaseAmountWithTax(\Magento\Quote\Model\Quote $quote)
    {
        return $this->bulkGoods->getBaseAmountWithTax($quote);
    }

    protected function getBaseAmount(\Magento\Quote\Model\Quote $quote)
    {
        return $this->bulkGoods->getBaseAmount($quote);
    }

    protected function getBaseTaxAmount($quote)
    {
        $baseAmount = $this->getBaseAmount($quote);

        return $this->bulkGoods->getBaseTaxAmount($quote, $baseAmount);
    }

    protected function getConvertedAmount($baseAmount)
    {
        return $this->priceCurrency->convert($baseAmount);
    }

    protected function canApplyTotal(\Magento\Quote\Model\Quote $quote)
    {
        if(!$this->configuration->isEnabled()){
            return false;
        }

        if (!$quote->getId()) {
            return false;
        }

        if($this->request->getControllerName() == self::PAYPAL_METHOD_CODE && in_array($this->request->getActionName(), $this->paypalActionNames)){
            return false;
        }

        return true;
    }

}
