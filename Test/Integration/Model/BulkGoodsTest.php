<?php
namespace MageSuite\BulkGoods\Test\Integration\Model;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class BulkGoodsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\BulkGoods\Test\Integration\Helper\Order
     */
    protected $orderHelper;

    /**
     * @var MageSuite\BulkGoods\Api\BulkGoodsInterface
     */
    protected $bulkGoods;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderHelper = $objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
        $this->bulkGoods = $objectManager->get(\MageSuite\BulkGoods\Api\BulkGoodsInterface::class);
        $this->orderRepository = $objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id DE
     * @magentoConfigFixture default_store shipping/origin/region_id 81
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testItAddsBulkGoodsFeeInclTaxCorrectlyToOrder()
    {
        $expectedFee = 8.4;
        $order = $this->orderHelper->createOrder();
        $bulkGoodsFee = $this->bulkGoods->getOrderFeeExclTax($order);

        $this->assertEqualsWithDelta($expectedFee, $bulkGoodsFee, 0.01);
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id DE
     * @magentoConfigFixture default_store shipping/origin/region_id 81
     * @magentoConfigFixture default_store shipping/origin/postcode 90034
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testItAddsBulkGoodsFeeExclTaxCorrectlyToOrder()
    {
        $expectedFee = 10;
        $order = $this->orderHelper->createOrder();
        $bulkGoodsFee = $this->bulkGoods->getOrderFeeExclTax($order);

        $this->assertEquals($expectedFee, $bulkGoodsFee);
    }

    /**
     * @magentoConfigFixture current_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture current_store bulk_goods/general/fee 9
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testBulkGoodsFeeIsNotReducedForInvoice()
    {
        $order = $this->orderHelper->createOrder('DE');
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();

        $expectedFee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $bulkGoodsFee = $invoice->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $this->assertEquals($expectedFee, $bulkGoodsFee);
    }

    /**
     * @magentoConfigFixture current_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture current_store bulk_goods/general/fee 9
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testTotalsWithBulkGoodsAndTaxesIsSameOnOrderAndInvoice()
    {
        $order = $this->orderHelper->createOrder('DE');
        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();

        $orderTotals = $order->getGrandTotal();
        $invoiceTotals = $invoice->getGrandTotal();
        $this->assertEquals($orderTotals, $invoiceTotals);
    }

    public static function loadTaxRates()
    {
        require __DIR__ . '/../_files/tax_rates.php';
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../_files/products.php';
    }
}
