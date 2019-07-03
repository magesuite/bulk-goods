<?php

namespace MageSuite\BulkGoods\Model;

class BulkGoods implements \MageSuite\BulkGoods\Api\BulkGoodsInterface
{
    const BULK_GOODS_ATTRIBUTE_CODE = 'bulk_goods_flag';
    const BULK_GOODS_FEE_CODE = 'bulk_goods_fee';
    const BULK_GOODS_TAX_CODE = 'bulk_goods_tax';

    /**
     * @var \MageSuite\BulkGoods\Service\FeeProvider
     */
    protected $feeProvider;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration\BulkGoods
     */
    protected $configuration;

    /**
     * @var \MageSuite\BulkGoods\Service\TaxCalculator
     */
    protected $taxCalculator;

    public function __construct(
        \MageSuite\BulkGoods\Service\FeeProvider $feeProvider,
        \MageSuite\BulkGoods\Helper\Configuration\BulkGoods $configuration,
        \MageSuite\BulkGoods\Service\TaxCalculator $taxCalculator
    ) {
        $this->feeProvider = $feeProvider;
        $this->configuration = $configuration;
        $this->taxCalculator = $taxCalculator;
    }

    public function getBaseAmountWithTax($quote)
    {
        $baseAmount = $this->getBaseAmount($quote);

        return $baseAmount + $this->getBaseTaxAmount($baseAmount);
    }

    public function getBaseAmount($quote)
    {
        return $this->feeProvider->getFee($quote);
    }

    public function getBaseTaxAmount($amount)
    {
        return $this->taxCalculator->calculateTax($amount);
    }

    public function getLabel()
    {
        return $this->configuration->getLabel();
    }
}
