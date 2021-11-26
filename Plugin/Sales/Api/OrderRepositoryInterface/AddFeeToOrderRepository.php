<?php
namespace MageSuite\BulkGoods\Plugin\Sales\Api\OrderRepositoryInterface;

class AddFeeToOrderRepository
{
    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    protected $orderExtensionFactory;

    public function __construct(\Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory)
    {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    public function afterGet(\Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderInterface $order)
    {
        $fee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
        $tax = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

        $extensionAttributes = $order->getExtensionAttributes();
        $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
        $extensionAttributes->setBulkGoodsFee($fee);
        $extensionAttributes->setBulkGoodsTax($tax);

        $order->setExtensionAttributes($extensionAttributes);

        return $order;
    }

    public function afterGetList(\Magento\Sales\Api\OrderRepositoryInterface $subject, \Magento\Sales\Api\Data\OrderSearchResultInterface $searchResult)
    {
        $orders = $searchResult->getItems();

        foreach ($orders as &$order) {
            $fee = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE);
            $tax = $order->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_TAX_CODE);

            $extensionAttributes = $order->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
            $extensionAttributes->setBulkGoodsFee($fee);
            $extensionAttributes->setBulkGoodsTax($tax);

            $order->setExtensionAttributes($extensionAttributes);
        }

        return $searchResult;
    }
}
