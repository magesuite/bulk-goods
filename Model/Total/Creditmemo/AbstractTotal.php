<?php

namespace MageSuite\BulkGoods\Model\Total\Creditmemo;

abstract class AbstractTotal extends \Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal
{
    /**
     * @var \MageSuite\BulkGoods\Api\BulkGoodsInterface
     */
    protected $bulkGoods;

    public function __construct(
        \MageSuite\BulkGoods\Api\BulkGoodsInterface $bulkGoods,
        array $data = []
    ) {
        $this->bulkGoods = $bulkGoods;
        parent::__construct($data);
    }

    protected function canApplyTotal(\Magento\Sales\Model\Order $order)
    {
        if (!$order->getId()) {
            return false;
        }

        return true;
    }
}
