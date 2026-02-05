<?php

declare(strict_types=1);

namespace MageSuite\BulkGoods\Test\Integration\Model\Total\Invoice;

class BulkGoodsFeeTaxTest extends \PHPUnit\Framework\TestCase
{
    protected \MageSuite\BulkGoods\Test\Integration\Helper\Order $orderHelper;

    protected \Magento\Sales\Model\Service\InvoiceService $invoiceService;

    protected \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;

    protected \Magento\Sales\Model\OrderRepository $orderRepository;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->orderHelper = $objectManager->get(\MageSuite\BulkGoods\Test\Integration\Helper\Order::class);
        $this->invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
        $this->searchCriteriaBuilder = $objectManager->get(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->orderRepository = $objectManager->get(\Magento\Sales\Model\OrderRepository::class);
    }
    /**
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
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/configurable_product.php
     * @magentoDataFixture MageSuite_BulkGoods::Test/Integration/_files/tax_rates.php
     */
    public function testItAddsBulkGoodsFeeWithCorrectTaxToInvoiceWithConfigurableProduct(): void
    {
        $expectedFeeWithTax = 10;
        $expectedFeeWithoutTax = 8.4;
        $expectedInvoiceTax = 1.9;
        $expectedBulkGoodsTax = 1.6;

        $eavConfig = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Eav\Model\Config::class);
        $attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');
        $request = new \Magento\Framework\DataObject([
            'qty' => 1,
            'super_attribute' => [
                $attribute->getId() => $attribute->getOptions()[1]->getValue(),
            ],
        ]);

        $orderWithTax = $this->orderHelper->createOrder('DE', $request);
        $invoice = $this->invoiceService->prepareInvoice($orderWithTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFeeWithTax, $invoice->getBulkGoodsFee());
        $this->assertEquals($expectedInvoiceTax + $expectedBulkGoodsTax, $invoice->getTaxAmount());

        $orderWithoutTax = $this->orderHelper->createOrder('FR', $request);
        $invoice = $this->invoiceService->prepareInvoice($orderWithoutTax);
        $invoice->register()->save();

        $this->assertEquals($expectedFeeWithoutTax, $invoice->getBulkGoodsFee());
        $this->assertEquals(0, $invoice->getTaxAmount());
    }

    protected function getOrderByIncrementId($orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('increment_id', $orderId)->create();
        $orders = $this->orderRepository->getList($searchCriteria)->getItems();
        return current($orders);
    }
}
