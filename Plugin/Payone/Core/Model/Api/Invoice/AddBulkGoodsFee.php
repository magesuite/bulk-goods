<?php
namespace MageSuite\BulkGoods\Plugin\Payone\Core\Model\Api\Invoice;

class AddBulkGoodsFee
{
    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $orderItemFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->bulkGoods = $bulkGoods;
        $this->configuration = $configuration;
        $this->orderItemFactory = $orderItemFactory;
        $this->storeManager = $storeManager;
    }

    public function beforeAddProductInfo(\Payone\Core\Model\Api\Invoice $subject, \Payone\Core\Model\Api\Request\Base $oRequest, \Magento\Sales\Model\Order $oOrder, $aPositions = false, $blDebit = false)
    {
        if (!$this->configuration->isEnabled()) {
            return [$oRequest, $oOrder, $aPositions, $blDebit];
        }

        if ($this->configuration->isFreeShipping() && !(float)$oOrder->getShippingAmount()) {
            return [$oRequest, $oOrder, $aPositions, $blDebit];
        }

        $fee = (float)$oOrder->getTaxAmount() ? $this->bulkGoods->getBaseAmountWithTax($oOrder, true) : $this->bulkGoods->getBaseAmount($oOrder);

        if (!(float)$fee) {
            return [$oRequest, $oOrder, $aPositions, $blDebit];
        }

        $order = clone $oOrder;
        $order->addItem($this->prepareCustomItem($fee));

        return [$oRequest, $order, $aPositions, $blDebit];
    }

    protected function prepareCustomItem($fee)
    {
        $orderItem = $this->orderItemFactory->create();
        $orderItem->setData([
            'store_id' => $this->storeManager->getStore()->getId(),
            'is_virtual' => false,
            'sku' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'name' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
            'qty' => 1,
            'price' => $fee,
            'base_price' => $fee,
            'row_total' => $fee,
            'base_row_total' => $fee,
            'product_type' => 'simple',
            'price_incl_tax' => $fee,
            'base_price_incl_tax' => $fee,
            'row_total_incl_tax' => $fee,
            'base_row_total_incl_tax' => $fee,
            'qty_ordered' => 1,
            'qty_shipped' => 0,
            'qty_refunded' => 0,
            'qty_canceled' => 0,
        ]);

        return $orderItem;
    }
}
