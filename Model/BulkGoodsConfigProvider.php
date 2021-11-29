<?php
namespace MageSuite\BulkGoods\Model;

class BulkGoodsConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \MageSuite\BulkGoods\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\BulkGoods\Model\BulkGoods
     */
    protected $bulkGoods;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \MageSuite\BulkGoods\Helper\Configuration $configuration,
        \MageSuite\BulkGoods\Model\BulkGoods $bulkGoods
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->configuration = $configuration;
        $this->bulkGoods = $bulkGoods;
    }

    public function getConfig()
    {
        $bulkGoodsConfig = [];
        $quote = $this->checkoutSession->getQuote();

        if (empty($quote)) {
            return $bulkGoodsConfig;
        }

        $bulkGoodsConfig['bulk_goods_title'] = $this->getBulkGoodsTitle();
        $bulkGoodsConfig['bulk_goods_fee'] = $this->getBulkGoodsFee($quote);

        return $bulkGoodsConfig;
    }

    protected function getBulkGoodsTitle()
    {
        return $this->configuration->getLabel();
    }

    protected function getBulkGoodsFee($quote)
    {
        return $this->bulkGoods->getBaseAmountWithTax($quote);
    }
}
