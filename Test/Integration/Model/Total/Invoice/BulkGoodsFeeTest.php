<?php

declare(strict_types=1);
namespace MageSuite\BulkGoods\Test\Integration\Model\Total\Invoice;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkGoodsFeeTest extends \PHPUnit\Framework\TestCase
{
    protected ?\MageSuite\BulkGoods\Test\Integration\Helper\Order $orderHelper = null;
    protected ?\Magento\Sales\Model\Service\InvoiceService $invoiceService = null;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderHelper = $objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
        $this->invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
        $this->productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToInvoice():void
    {
        $expectedFeeWithTax = 10;
        $expectedFeeWithoutTax = 8.4;
        $expectedTax = 1.6;

        $orderWithTax = $this->orderHelper->createOrder('DE');

        $invoice = $this->invoiceService->prepareInvoice($orderWithTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFeeWithTax, $invoice->getBulkGoodsFee());
        $this->assertEquals($expectedTax, $invoice->getTaxAmount());

        $orderWithoutTax = $this->orderHelper->createOrder('FR');

        $invoice = $this->invoiceService->prepareInvoice($orderWithoutTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFeeWithoutTax, $invoice->getBulkGoodsFee());
        $this->assertEquals(0, $invoice->getTaxAmount());
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToInvoiceWithDifferentShippingCountry():void
    {
        $expectedFee = 10;
        $expectedTax = 1.87;

        $orderWithTax = $this->orderHelper->createOrder('PL');

        $invoice = $this->invoiceService->prepareInvoice($orderWithTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFee, $invoice->getBulkGoodsFee());
        $this->assertEquals($expectedTax, $invoice->getTaxAmount());
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     */
    public function testItAddsBulkGoodsFeeToOrderWithPartialInvoice()
    {
        $quantities = [];
        $expectedBulkGoodsFee = 10;
        $expectedBulkGoodsTax = 1.6;
        $firstInvoiceExpectedTax = 11.1;
        $secondInvoiceExpectedTax = 9.5;
        $orderExpectedTax = 20.6;
        $orderExpectedTotal = 129;

        $product = $this->productRepository->get('product');
        $product->setTaxClassId(2)->save();

        $order = $this->orderHelper->createOrder('DE', 2, $product);
        $orderItems = $order->getItems();
        $orderItem = array_shift($orderItems);
        $quantities[$orderItem->getId()] = 1;

        $firstInvoice = $this->invoiceService->prepareInvoice($order, $quantities);
        $firstInvoice->register()->save();
        $this->assertEquals($expectedBulkGoodsFee, $firstInvoice->getBulkGoodsFee());
        $this->assertEquals($expectedBulkGoodsTax, $firstInvoice->getBulkGoodsTax());
        $this->assertEquals($firstInvoiceExpectedTax, $firstInvoice->getTaxAmount());

        $secondInvoice = $this->invoiceService->prepareInvoice($order, $quantities);
        $secondInvoice->register()->save();
        $this->assertEquals(0, $secondInvoice->getBulkGoodsFee());
        $this->assertEquals(0, $secondInvoice->getBulkGoodsTax());
        $this->assertEquals($secondInvoiceExpectedTax, round($secondInvoice->getTaxAmount(), 2));

        $this->assertEquals($orderExpectedTax, $order->getBaseTaxAmount());
        $this->assertEquals($orderExpectedTotal, $order->getBaseGrandTotal());
    }
}
