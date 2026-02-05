<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Test\Integration\Model;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BulkGoodsTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager;
    protected ?\Magento\Store\Model\StoreManagerInterface $storeManager;
    protected ?\Magento\Quote\Api\CartManagementInterface $cartManagement;
    protected ?\Magento\Quote\Api\CartRepositoryInterface $cartRepository;
    protected ?\Magento\Checkout\Model\Cart $cart;
    protected ?\Magento\Quote\Model\QuoteManagement $quoteManagement;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods;
    protected ?\Magento\Sales\Api\OrderRepositoryInterface $orderRepository;
    protected ?\Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;
    protected ?\Magento\Sales\Model\Service\InvoiceService $invoiceService;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->bulkGoods = $this->objectManager->get(\MageSuite\BulkGoods\Api\BulkGoodsInterface::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->invoiceService = $this->objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
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
    public function testItAddsBulkGoodsFeeInclTaxCorrectlyToOrder(): void
    {
        $expectedFee = 8.4;
        $quote = $this->prepareQuote();
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get($orderId);
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/products.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeExclTaxCorrectlyToOrder(): void
    {
        $expectedFee = 10;
        $quote = $this->prepareQuote();
        $orderId = $this->cartManagement->placeOrder($quote->getId());
        $order = $this->orderRepository->get($orderId);
        $bulkGoodsFee = $this->bulkGoods->getOrderFeeExclTax($order);

        $this->assertEquals($expectedFee, $bulkGoodsFee);
    }

    /**
     * @magentoConfigFixture current_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture current_store bulk_goods/general/fee 9
     * @magentoConfigFixture current_store tax/calculation/shipping_includes_tax 0
     * @magentoConfigFixture current_store tax/classes/shipping_tax_class 2
     * @magentoConfigFixture default_store tax/defaults/country DE
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testBulkGoodsFeeIsNotReducedForInvoice(): void
    {
        $order = $this->findOrderByIncrementId('100000001');
        $order->setData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, 9);
        $this->orderRepository->save($order);

        $invoice = $this->invoiceService->prepareInvoice($order);
        $invoice->register();
        $invoice->save();

        $expectedFee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $bulkGoodsFee = $invoice->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $this->assertEquals($expectedFee, $bulkGoodsFee);
    }

    protected function prepareQuote(): \Magento\Quote\Model\Quote
    {
        $cartId = $this->cartManagement->createEmptyCart();
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->cartRepository->get($cartId);
        $store = $this->storeManager->getStore(1);
        $quote->setStore($store);
        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);
        $quote->setCurrency();
        $product = $this->productRepository->get('product');
        $quote->addProduct($product, 1);

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
        $billingAddress = $this->objectManager->create(
            'Magento\Quote\Api\Data\AddressInterface',
            ['data' => $addressData]
        );
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        $rate = $this->objectManager->create(\Magento\Quote\Model\Quote\Address\Rate::class);
        $shippingMethod = 'freeshipping_freeshipping';
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
        $quote->setData(
            \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            $this->bulkGoods->getBaseAmountWithTax($quote)
        );

        return $quote;
    }

    protected function findOrderByIncrementId(string $incrementId): ?\Magento\Sales\Api\Data\OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($orders);
    }
}
