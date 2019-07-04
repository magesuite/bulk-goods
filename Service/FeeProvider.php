<?php

namespace MageSuite\BulkGoods\Service;

class FeeProvider
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    public function __construct(\MageSuite\BulkGoods\Helper\Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    public function getFee(\Magento\Quote\Model\Quote $quote)
    {
        if(!$this->configuration->isEnabled()){
            return 0;
        }

        if(!$this->isBulkGoodItemInEntity($quote)){
            return 0;
        }

        return $this->configuration->getFee();
    }

    protected function isBulkGoodItemInEntity($quote)
    {
        foreach ($quote->getAllVisibleItems() as $item) {
            if($item->getProduct()->getData(\MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_ATTRIBUTE_CODE)){
                return true;
            }
        }

        return false;
    }
}