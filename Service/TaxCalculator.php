<?php

namespace MageSuite\BulkGoods\Service;

class TaxCalculator
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxRateCalculation;

    /**
     * @var \Magento\Tax\Model\Calculation
     */
    protected $taxCalculation;



    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxRateCalculation,
        \Magento\Tax\Model\Calculation $taxCalculation
    ){
        $this->storeManager = $storeManager;
        $this->taxConfig = $taxConfig;
        $this->taxRateCalculation = $taxRateCalculation;
        $this->taxCalculation = $taxCalculation;
    }

    public function calculateTax($amount)
    {
        $store = $this->storeManager->getStore();
        $shippingTaxId = $this->taxConfig->getShippingTaxClass($store);
        $isTaxIncluded = $this->taxConfig->shippingPriceIncludesTax($store);

        $taxRate = $this->taxRateCalculation->getCalculatedRate($shippingTaxId, null, $store->getId());
        $tax = $this->taxCalculation->calcTaxAmount($amount, $taxRate, $isTaxIncluded);

        return $tax;
    }
}