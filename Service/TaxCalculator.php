<?php

namespace MageSuite\BulkGoods\Service;

class TaxCalculator
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;

    /**
     * @var \Magento\Framework\DataObject
     */
    private $addressRateRequest = null;

    public function __construct(
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Model\Calculation $taxCalculation
    ) {
        $this->taxConfig = $taxConfig;
        $this->taxCalculation = $taxCalculation;
    }

    public function calculateTax($quote, $amount)
    {
        $taxRate = $this->getTaxRate($quote);
        $tax = $this->taxCalculation->calcTaxAmount($amount, $taxRate);

        return $tax;
    }

    protected function getAddressRateRequest(\Magento\Quote\Model\Quote $quote)
    {
        if (null == $this->addressRateRequest) {
            $this->addressRateRequest = $this->taxCalculation->getRateRequest(
                $quote->getShippingAddress(),
                $quote->getBillingAddress(),
                $quote->getCustomerTaxClassId(),
                $quote->getStoreId(),
                $quote->getCustomerId()
            );
        }

        return $this->addressRateRequest;
    }

    public function getTaxRate(\Magento\Quote\Model\Quote $quote){
        $taxRateRequest = $this->getAddressRateRequest($quote)->setProductClassId(
            $this->taxConfig->getShippingTaxClass()
        );
        $rate = $this->taxCalculation->getRate($taxRateRequest);

        return $rate;
    }

    public function getShippingTaxClassId()
    {
        return $this->taxConfig->getShippingTaxClass();
    }
}
