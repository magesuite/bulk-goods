<?php

namespace MageSuite\BulkGoods\Block\Order\Total\Email;

class BulkGoodsFee extends \Magento\Framework\View\Element\AbstractBlock
{
    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration\BulkGoods
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \MageSuite\BulkGoods\Helper\Configuration\BulkGoods $configuration,
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
        if(!(float)$this->getSource()->getBulkGoodsFee()) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                'value' => $this->getSource()->getBulkGoodsFee(),
                'label' => $this->configuration->getLabel(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
