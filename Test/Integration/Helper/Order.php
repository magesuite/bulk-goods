<?php
namespace MageSuite\BulkGoods\Test\Integration\Helper;

class Order
{
    const FLATRATE_SHIPPING_METHOD_CODE = 'flatrate_flatrate';

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Quote\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateFactory
     */
    protected $addressRateFactory;

    /**
     * @var MageSuite\BulkGoods\Api\BulkGoodsInterface
     */
    protected $bulkGoods;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Directory\Model\Region
     */
    protected $region;

    public function __construct(
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Quote\Api\Data\AddressInterfaceFactory $addressFactory,
        \Magento\Quote\Model\Quote\Address\RateFactory $addressRateFactory,
        \MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Directory\Model\Region $region
    ) {
        $this->cartManagement = $cartManagement;
        $this->cartRepository = $cartRepository;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->addressFactory = $addressFactory;
        $this->addressRateFactory = $addressRateFactory;
        $this->bulkGoods = $bulkGoods;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->region = $region;
    }

    public function createOrder($countryCode = 'DE', $request = 1, $product = null)
    {
        if(empty($product)) {
            $product = $this->productRepository->get('product');
        }

        $quote = $this->prepareQuote($countryCode, $request, $product);

        return $this->createOrderByQuoteId($quote->getId());
    }

    public function createOrderByQuoteId($quoteId)
    {
        $orderId = $this->cartManagement->placeOrder($quoteId);

        return $this->orderRepository->get($orderId);
    }

    public function findOrderByIncrementId(string $incrementId): ?\Magento\Sales\Api\Data\OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId)
            ->create();
        $orders = $this->orderRepository->getList($searchCriteria)
            ->getItems();

        return array_shift($orders);
    }

    public function prepareQuote($countryCode = 'DE', $request = 1, $product = null)
    {
        if(empty($product)) {
            $product = $this->productRepository->get('product');
        }

        $cartId = $this->cartManagement->createEmptyCart();
        $quote = $this->cartRepository->get($cartId);
        $store = $this->storeManager->getStore(1);
        $quote->setStore($store);
        $quote->setCustomerEmail('test@example.com');
        $quote->setCustomerIsGuest(true);
        $quote->setCurrency();
        $quote->addProduct($product, $request);

        switch($countryCode) {
            case 'PL':
                $regionId = $this->region->loadByCode('PL-24', 'PL')->getRegionId();
                break;
            case 'FR':
                $regionId = $this->region->loadByCode('62', 'FR')->getRegionId();
                break;
            default:
                $regionId = $this->region->loadByCode('BER', 'DE')->getRegionId();
        }

        $addressData = [
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Berlin',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => $countryCode,
            'region_id' => $regionId
        ];

        $billingAddress = $this->addressFactory->create();
        $billingAddress->setData($addressData);
        $billingAddress->setAddressType('billing');
        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');
        $quote->setBillingAddress($billingAddress);
        $quote->setShippingAddress($shippingAddress);

        $rate = $this->addressRateFactory->create();
        $rate->setCode(self::FLATRATE_SHIPPING_METHOD_CODE);

        $quote->getShippingAddress()->addShippingRate($rate);
        $quote->getShippingAddress()->setShippingMethod(self::FLATRATE_SHIPPING_METHOD_CODE);

        $payment = $quote->getPayment();
        $payment->setMethod(\Magento\OfflinePayments\Model\Checkmo::PAYMENT_METHOD_CHECKMO_CODE);
        $quote->setPayment($payment);

        $quote->setInventoryProcessed(false);
        $quote->save();
        $quote->collectTotals();

        return $quote;
    }
}
