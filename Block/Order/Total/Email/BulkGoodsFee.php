<?php

namespace MageSuite\BulkGoods\Block\Order\Total\Email;

class BulkGoodsFee extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configuration = $configuration;
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals()
    {
        $fee = $this->getSource()->getBulkGoodsFee();

        if (!(float)$fee) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                'value' => $this->configuration->getSubtotalDisplayType() == \Magento\Tax\Model\Config::DISPLAY_TYPE_INCLUDING_TAX ? $fee : $this->configuration->getFee(),
                'label' => $this->configuration->getLabel(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
