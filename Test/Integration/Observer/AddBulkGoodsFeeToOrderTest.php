<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddBulkGoodsFeeToOrderTest extends \PHPUnit\Framework\TestCase
{
    protected \Magento\Framework\App\ObjectManager $objectManager;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Quote\Api\CartManagementInterface $cartManagement;
    protected \Magento\Quote\Api\CartRepositoryInterface $cartRepository;
    protected \Magento\Checkout\Model\Cart $cart;
    protected \Magento\Quote\Model\QuoteManagement $quoteManagement;
    protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods;
    protected \Magento\Sales\Api\OrderRepositoryInterface $orderRepository;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->bulkGoods = $this->objectManager->get(\MageSuite\BulkGoods\Model\BulkGoods::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
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
        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);

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
        $qty = 1;
        $product = $this->productRepository->get('product');
        $quote = $this->prepareQuote($product, $qty);
        $totals = $quote->getTotals();

        $this->assertEquals(10, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(0, $totals['tax']->getValue());
        $this->assertEquals(20, $totals['grand_total']->getValue());

        $orderId = $this->cartManagement->placeOrder($quote->getId());
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
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
        $qty = 1;
        $product = $this->productRepository->get('product');
        $quote = $this->prepareQuote($product, $qty);
        $totals = $quote->getTotals();

        $this->assertEquals(10, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(1.9, $totals['tax']->getValue());
        $this->assertEquals(21.9, $totals['grand_total']->getValue());

        $orderId = $this->cartManagement->placeOrder($quote->getId());
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($orderId);
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
    }

    private function prepareQuote(\Magento\Catalog\Api\Data\ProductInterface $product, int $qty): \Magento\Quote\Model\Quote
    {
        $addressData = [
            'region_id' => '82',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Berlin',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'DE'
        ];

        $shippingMethod = 'freeshipping_freeshipping';

        $store = $this->storeManager->getStore(1);

        $cartId = $this->cartManagement->createEmptyCart();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $quote->setStore($store);

        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);

        $quote->setCurrency();

        $quote->addProduct($product, $qty);

        $billingAddress = $this->objectManager->create('Magento\Quote\Api\Data\AddressInterface', ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $rate = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $rate->setCode($shippingMethod);

        $quote->getPayment()->importData(['method' => 'checkmo']);

        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);
        $quote->getShippingAddress()->addShippingRate($rate);
        $quote->getShippingAddress()->setShippingMethod($shippingMethod);


        $quote->setPaymentMethod('checkmo');
        $quote->setInventoryProcessed(false);

        $quote->save();

        $quote->collectTotals();

        $quote->setData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $this->bulkGoods->getBaseAmountWithTax($quote));

        return $quote;
    }
}
