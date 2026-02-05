<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddBulkGoodsFeeToOrderTest extends \PHPUnit\Framework\TestCase
{
    protected \Magento\TestFramework\ObjectManager $objectManager;

    protected \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods;

    protected \MageSuite\BulkGoods\Test\Integration\Helper\Order $orderHelper;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->bulkGoods = $this->objectManager->get(\MageSuite\BulkGoods\Model\BulkGoods::class);
        $this->orderHelper = $this->objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
    }


    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 0
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     */
    public function testItDoesntAddBulkGoodsFee(): void
    {
        $expectedFee = 0;
        $order = $this->orderHelper->createOrder();

        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     */
    public function testItAddsBulkGoodsFeeInclTaxCorrectlyToOrder(): void
    {
        $expectedFee = 10;
        $quote = $this->orderHelper->prepareQuote();
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(5, $totals['shipping']->getValue());
        $this->assertEquals(0, $totals['tax']->getValue());
        $this->assertEquals(65, $totals['grand_total']->getValue());

        $order = $this->orderHelper->createOrderByQuoteId($quote->getId());
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeExclTaxCorrectlyToOrder(): void
    {
        $expectedFee = 11.9;
        $quote = $this->orderHelper->prepareQuote();
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(5, $totals['shipping']->getValue());
        $this->assertEquals(2.85, $totals['tax']->getValue());
        $this->assertEquals(67.85, $totals['grand_total']->getValue());

        $order = $this->orderHelper->createOrderByQuoteId($quote->getId());
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeInclTaxBasedOnShippingAddressCorrectlyToOrder(): void
    {
        // de tax rate 19%
        $expectedFee = 10;
        $quote = $this->orderHelper->prepareQuote();
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(8.4, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(4.2, $totals['shipping']->getValue());
        $this->assertEquals(2.4, $totals['tax']->getValue());
        $this->assertEquals(65, $totals['grand_total']->getValue());

        $order = $this->orderHelper->createOrderByQuoteId($quote->getId());
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id PL
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeInclTaxBasedOnShippingAddressWithDifferentShippingCountryCorrectlyToOrder(): void
    {
        // pl tax rate 23%
        $expectedFee = 10;
        $quote = $this->orderHelper->prepareQuote('PL');
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(8.13, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(4.07, $totals['shipping']->getValue());
        $this->assertEquals(2.8, $totals['tax']->getValue());
        $this->assertEquals(65, $totals['grand_total']->getValue());

        $order = $this->orderHelper->createOrderByQuoteId($quote->getId());
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id PL
     * @magentoConfigFixture admin_store tax/cart_display/subtotal 1
     * @magentoConfigFixture default_store tax/cart_display/subtotal 1
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testBulkGoodsAmountInTotalBlockExcludedTax(): void
    {
        $html = $this->prepareOrderTotalsHtml();

        $this->assertStringContainsString(
            '<tdclass="label">BulkGoodsFee</td><td><span><spanclass="price">$8.13</span>',
            $html,
            'Bulk Goods Fee contains tax or is wrong configured'
        );
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id PL
     * @magentoConfigFixture admin_store tax/cart_display/subtotal 2
     * @magentoConfigFixture default_store tax/cart_display/subtotal 2
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testBulkGoodsAmountInTotalBlockIncludedTax(): void
    {
        $html = $this->prepareOrderTotalsHtml();

        $this->assertStringContainsString(
            '<tdclass="label">BulkGoodsFee</td><td><span><spanclass="price">$10.00</span>',
            $html,
            'Bulk Goods Fee doesn\'t contain tax'
        );
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoConfigFixture default_store general/country/default DE
     * @magentoConfigFixture current_store tax/calculation/price_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/shipping_includes_tax 1
     * @magentoConfigFixture default_store tax/calculation/based_on shipping
     * @magentoConfigFixture default_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoConfigFixture default_store shipping/origin/country_id PL
     * @magentoConfigFixture admin_store tax/cart_display/subtotal 3
     * @magentoConfigFixture default_store tax/cart_display/subtotal 3
     * @magentoAppArea adminhtml
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testBulkGoodsAmountInTotalBlockIncludedAndExcludedTax(): void
    {
        $html = $this->prepareOrderTotalsHtml();

        $this->assertStringContainsString(
            '<tdclass="label">BulkGoodsFee(Incl.Tax)</td><td><span><spanclass="price">$10.00</span></span></td></tr>'
            . '<trclass="col-3"><tdclass="label">BulkGoodsFee(Excl.Tax)</td><td><span><spanclass="price">$8.13</span>',
            $html,
            'Bulk Goods Fee doesn\'t wrongly configured'
        );
    }

    protected function prepareOrderTotalsHtml(): string
    {
        $quote = $this->orderHelper->prepareQuote('PL');
        $order = $this->orderHelper->createOrderByQuoteId($quote->getId());

        $request = $this->objectManager->get(\Magento\Framework\App\RequestInterface::class);

        $params = [
            'order_id' => $order->getId()
        ];
        $request->setParams($params);

        $block = $this->objectManager->create(\Magento\Sales\Block\Adminhtml\Order\Totals::class);
        $block->setOrder($order)->setTemplate('Magento_Sales::order/totals.phtml');

        return preg_replace('/\s+/', '', $block->toHtml());
    }
}
