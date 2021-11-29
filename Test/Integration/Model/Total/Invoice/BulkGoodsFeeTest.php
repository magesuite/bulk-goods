<?php
namespace MageSuite\BulkGoods\Test\Integration\Model\Total\Invoice;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkGoodsFeeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\BulkGoods\Test\Integration\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderHelper = $objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
        $this->invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
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
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToInvoice()
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
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToInvoiceWithDifferentShippingCountry()
    {
        $expectedFee = 10;
        $expectedTax = 1.87;

        $orderWithTax = $this->orderHelper->createOrder('PL');

        $invoice = $this->invoiceService->prepareInvoice($orderWithTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFee, $invoice->getBulkGoodsFee());
        $this->assertEquals($expectedTax, $invoice->getTaxAmount());
    }

    public static function loadTaxRates()
    {
        require __DIR__ . '/../../../_files/tax_rates.php';
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../../../_files/products.php';
    }
}
