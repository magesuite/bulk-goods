<?php

namespace MageSuite\BulkGoods\Model;

class BulkGoods implements \MageSuite\BulkGoods\Api\BulkGoodsInterface
{
    const BULK_GOODS_ATTRIBUTE_CODE = 'is_bulk_good';
    const BULK_GOODS_FEE_CODE = 'bulk_goods_fee';
    const BULK_GOODS_TAX_CODE = 'bulk_goods_tax';

    /**
     * @var \MageSuite\BulkGoods\Service\FeeProvider
     */
    protected $feeProvider;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\BulkGoods\Service\TaxCalculator
     */
    protected $taxCalculator;

    public function __construct(
        \MageSuite\BulkGoods\Service\FeeProvider $feeProvider,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \MageSuite\BulkGoods\Service\TaxCalculator $taxCalculator
    ) {
        $this->feeProvider = $feeProvider;
        $this->configuration = $configuration;
        $this->taxCalculator = $taxCalculator;
    }

    public function getBaseAmountWithTax($quote)
    {
        $baseAmount = $this->getBaseAmount($quote);

        return $baseAmount + $this->getBaseTaxAmount($quote, $baseAmount);
    }

    public function getBaseAmount($quote)
    {
        return $this->feeProvider->getFee($quote);
    }

    public function getBaseTaxAmount($quote, $amount = null)
    {
        $shippingAddress = $quote->getShippingAddress();

        if($shippingAddress){
            $taxAmount = $shippingAddress->getData('tax_amount');

            if(!(float)$taxAmount){
                return 0;
            }
        }

        if(empty($amount)){
            $amount = $this->getBaseAmount($quote);
        }

        return $this->taxCalculator->calculate($amount);
    }

    public function getLabel()
    {
        return $this->configuration->getLabel();
    }
}
