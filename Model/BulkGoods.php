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

    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $taxConfig;

    /**
     * @var \Magento\Tax\Api\TaxCalculationInterface
     */
    protected $taxRateCalculation;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    public function __construct(
        \MageSuite\BulkGoods\Service\FeeProvider $feeProvider,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \MageSuite\BulkGoods\Service\TaxCalculator $taxCalculator,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Tax\Api\TaxCalculationInterface $taxRateCalculation,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->feeProvider = $feeProvider;
        $this->configuration = $configuration;
        $this->taxCalculator = $taxCalculator;
        $this->taxConfig = $taxConfig;
        $this->taxRateCalculation = $taxRateCalculation;
        $this->priceCurrency = $priceCurrency;
    }

    public function getBaseAmountWithTax($quote, $force = false)
    {
        $baseAmount = $this->getBaseAmount($quote);

        return $baseAmount + $this->getBaseTaxAmount($quote, $baseAmount, $force);
    }

    public function getBaseAmount($quote)
    {
        $fee = $this->feeProvider->getFee($quote);

        if (!$fee) {
            return 0;
        }

        if ($this->taxConfig->shippingPriceIncludesTax($quote->getStoreId())) {
            $fee = $this->getFeeAmountExclTax($fee, $quote->getStoreId());
        }

        return $this->priceCurrency->round($fee);
    }

    protected function getFeeAmountExclTax($fee, $storeId)
    {
        $taxRate = $this->taxRateCalculation->getCalculatedRate(
            $this->taxConfig->getShippingTaxClass(),
            null,
            $storeId
        );

        $fee = $fee / ((100 + $taxRate) / 100);

        return $this->priceCurrency->round($fee);
    }

    public function getOrderFeeExclTax(\Magento\Sales\Model\Order $order)
    {
        $fee = (float)$order->getBulkGoodsFee();

        // for EU customers with valid VAT ID
        if (!$order->getTaxAmount()) {
            return $fee;
        }

        return $this->getFeeAmountExclTax($fee, $order->getStoreId());
    }

    public function getBaseTaxAmount($quote, $amount = null, $force = false)
    {
        $shippingAddress = $quote->getShippingAddress();

        if (!$force && $shippingAddress) {
            $appliedTaxes = $shippingAddress->getAppliedTaxes();

            if (empty($appliedTaxes)) {
                return 0;
            }
        }

        if (empty($amount)) {
            $amount = $this->getBaseAmount($quote);
        }

        return $this->taxCalculator->calculate($amount);
    }

    public function getLabel()
    {
        return $this->configuration->getLabel();
    }

    public function getShippingTaxClassId()
    {
        return $this->taxCalculator->getShippingTaxClassId();
    }

    public function getInvoiceSku()
    {
        return self::BULK_GOODS_FEE_CODE;
    }

    public function getInvoiceName()
    {
        return self::BULK_GOODS_FEE_CODE;
    }
}
