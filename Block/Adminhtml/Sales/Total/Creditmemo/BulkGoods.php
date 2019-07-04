<?php
namespace MageSuite\BulkGoods\Block\Adminhtml\Sales\Total\Creditmemo;

class BulkGoods extends \Magento\Framework\View\Element\Template
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
        if(!$this->getSource()->getBulkGoodsFee()) {
            return $this;
        }

        $total = new \Magento\Framework\DataObject(
            [
                'code' => \MageSuite\BulkGoods\Model\BulkGoods::BULK_GOODS_FEE_CODE,
                'value' => $this->getSource()->getBaseBulkGoodsFee(),
                'label' => $this->configuration->getLabel(),
            ]
        );

        $this->getParentBlock()->addTotalBefore($total, 'grand_total');

        return $this;
    }
}
