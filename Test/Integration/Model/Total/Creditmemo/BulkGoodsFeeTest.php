<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Test\Integration\Model\Total\Creditmemo;

class BulkGoodsFeeTest extends \PHPUnit\Framework\TestCase
{
    protected \MageSuite\BulkGoods\Test\Integration\Helper\Order $orderHelper;

    protected \Magento\Sales\Model\Service\InvoiceService $invoiceService;

    protected \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderHelper = $objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
        $this->invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
        $this->creditmemoFactory = $objectManager->get(\Magento\Sales\Model\Order\CreditmemoFactory::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoConfigFixture current_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture current_store bulk_goods/general/fee 10
     * @magentoConfigFixture current_store carriers/flatrate/price 0
     * @magentoConfigFixture current_store general/country/default DE
     * @magentoConfigFixture current_store tax/calculation/based_on shipping
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture current_store tax/defaults/country DE
     * @magentoConfigFixture current_store shipping/origin/country_id DE
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToCreditmemo(): void
    {
        $expectedFeeWithTax = 10;
        $expectedFeeWithoutTax = 8.4;
        $expectedTax = 1.6;

        $orderWithTax = $this->orderHelper->createOrder('DE');
        $invoice = $this->invoiceService->prepareInvoice($orderWithTax);
        $invoice->register()->save();
        $creditmemo = $this->creditmemoFactory->createByInvoice($invoice);

        $this->assertEquals($expectedFeeWithTax, $creditmemo->getBulkGoodsFee());
        $this->assertEquals($expectedTax, $creditmemo->getTaxAmount());

        $orderWithoutTax = $this->orderHelper->createOrder('FR');
        $invoice = $this->invoiceService->prepareInvoice($orderWithoutTax);
        $invoice->register()->save();
        $creditmemo = $this->creditmemoFactory->createByInvoice($invoice);

        $this->assertEquals($expectedFeeWithoutTax, $creditmemo->getBulkGoodsFee());
        $this->assertEquals(0, $creditmemo->getTaxAmount());
    }
}
