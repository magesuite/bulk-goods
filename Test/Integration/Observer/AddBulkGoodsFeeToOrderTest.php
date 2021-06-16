<?php
namespace MageSuite\BulkGoods\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddBulkGoodsFeeToOrderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $cart;

    /**
     * @var \Magento\Quote\Model\QuoteManagement
     */
    protected $quoteManagement;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->bulkGoods = $this->objectManager->get(\MageSuite\BulkGoods\Model\BulkGoods::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
    }

    public static function loadTaxRates()
    {
        require __DIR__ . '/../_files/tax_rates.php';
    }

    public static function loadProducts()
    {
        require __DIR__ . '/../_files/products.php';
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 0
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
     * @magentoAppArea frontend
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture loadProducts
     */
    public function testItDoesntAddBulkGoodsFee()
    {
        $expectedFee = 0;
        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

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
     * @magentoDataFixture loadProducts
     */
    public function testItAddsBulkGoodsFeeInclTaxCorrectlyToOrder()
    {
        $expectedFee = 10;
        $qty = 1;
        $product = $this->productRepository->get('product');
        $quote = $this->prepareQuote($product, $qty);
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(0, $totals['tax']->getValue());
        $this->assertEquals(60, $totals['grand_total']->getValue());

        $orderId = $this->cartManagement->placeOrder($quote->getId());
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
     * @magentoDataFixture loadProducts
     * @magentoDataFixture loadTaxRates
     */
    public function testItAddsBulkGoodsFeeExclTaxCorrectlyToOrder()
    {
        $expectedFee = 11.9;
        $qty = 1;
        $product = $this->productRepository->get('product');
        $quote = $this->prepareQuote($product, $qty);
        $totals = $quote->getTotals();

        $this->assertEquals(50, $totals['subtotal']->getValue());
        $this->assertEquals(10, $totals['bulk_goods_fee']->getValue());
        $this->assertEquals(1.9, $totals['tax']->getValue());
        $this->assertEquals(61.9, $totals['grand_total']->getValue());

        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get($orderId);
        $this->assertEquals($expectedFee, $order->getBulkGoodsFee());
    }

    private function prepareQuote($product, $qty)
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
        $websiteId = $store->getWebsiteId();

        $cartId = $this->cartManagement->createEmptyCart();
        $quote = $this->cartRepository->get($cartId);
        $quote->setStore($store);

        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);

        $quote->setCurrency();

        $quote->addProduct($product, intval($qty));

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

        return $quote;
    }
}
