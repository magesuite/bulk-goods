<?php
namespace MageSuite\BulkGoods\Test\Integration\Observer;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class AddBulkGoodsFeeToOrderTest extends \PHPUnit\Framework\TestCase
{
    const DEFAULT_CUSTOMER_TAX_CLASS_ID = 3;

    const DEFAULT_PRODUCT_TAX_CLASS_ID = 2;

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
    private $cart;

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

    /**
     * @var \Magento\Tax\Api\TaxRateRepositoryInterface
     */
    protected $rateRepository;

    /**
     * @var \Magento\Tax\Api\Data\TaxRateInterfaceFactory
     */
    protected $taxRateFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Tax\Api\Data\TaxRuleInterfaceFactory
     */
    protected $taxRuleFactory;

    /**
     * @var \Magento\Tax\Api\TaxRuleRepositoryInterface
     */
    protected $taxRuleRepository;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->storeManager = $this->objectManager->get(\Magento\Store\Model\StoreManagerInterface::class);
        $this->cartManagement = $this->objectManager->get(\Magento\Quote\Api\CartManagementInterface::class);
        $this->cartRepository = $this->objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $this->productRepository = $this->objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->bulkGoods = $this->objectManager->get(\MageSuite\BulkGoods\Model\BulkGoods::class);
        $this->orderRepository = $this->objectManager->get(\Magento\Sales\Api\OrderRepositoryInterface::class);
        $this->rateRepository = $this->objectManager->get(\Magento\Tax\Api\TaxRateRepositoryInterface::class);
        $this->taxRateFactory = $this->objectManager->create(\Magento\Tax\Api\Data\TaxRateInterfaceFactory::class);
        $this->dataObjectHelper = $this->objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
        $this->taxRuleFactory = $this->objectManager->create(\Magento\Tax\Api\Data\TaxRuleInterfaceFactory::class);
        $this->taxRuleRepository = $this->objectManager->create(\Magento\Tax\Api\TaxRuleRepositoryInterface::class);

        $this->_generateTaxRate();
    }

    private function _generateTaxRate()
    {
        $taxData = [
            'tax_country_id' => 'DE',
            'tax_region_id' => 0,
            'tax_postcode' => '*',
            'rate' => '19.0000',
            'code' => 'DE VAT Rate',
            'zip_is_range' => null,
            'zip_from' => null,
            'zip_to' => null
        ];

        $taxRate = $this->taxRateFactory->create();
        $this->dataObjectHelper->populateWithArray($taxRate, $taxData, \Magento\Tax\Api\Data\TaxRateInterface::class);
        $taxRateData = $this->rateRepository->save($taxRate);

        $taxRuleDataObject = $this->taxRuleFactory->create();
        $taxRuleDataObject->setCode('test')
            ->setTaxRateIds([$taxRateData->getId()])
            ->setCustomerTaxClassIds([self::DEFAULT_CUSTOMER_TAX_CLASS_ID])
            ->setProductTaxClassIds([self::DEFAULT_PRODUCT_TAX_CLASS_ID])
            ->setPriority(0)
            ->setPosition(0);

        $this->taxRuleRepository->save($taxRuleDataObject);
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
        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        $order = $this->orderRepository->get($orderId);

        $this->assertEquals(0, $order->getBulkGoodsFee());
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
        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        $order = $this->orderRepository->get($orderId);

        $this->assertEquals(10, $order->getBulkGoodsFee());
    }

    /**
     * @magentoConfigFixture default_store bulk_goods/general/is_enabled 1
     * @magentoConfigFixture default_store bulk_goods/general/fee 10
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
     */
    public function testItAddsBulkGoodsFeeExclTaxCorrectlyToOrder()
    {
        $qty = 1;
        $product = $this->productRepository->get('product');

        $quote = $this->prepareQuote($product, $qty);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        $order = $this->orderRepository->get($orderId);

        $this->assertEquals(11.9, $order->getBulkGoodsFee());
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

        $quote->setData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE, $this->bulkGoods->getBaseAmountWithTax($quote));

        return $quote;
    }
}
