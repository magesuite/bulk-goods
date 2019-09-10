<?php

namespace MageSuite\BulkGoods\Helper;

class Configuration extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BULK_GOODS_CONFIGURATION = 'bulk_goods/general';
    const XML_PATH_SUBTOTAL_DISPLAY_TYPE = 'tax/cart_display/subtotal';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $config = null;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfigInterface;
    }

    public function isEnabled()
    {
        return $this->getConfig()->getIsEnabled();
    }

    public function getLabel()
    {
        return $this->getConfig()->getLabel();
    }

    public function getFee()
    {
        return (float)$this->getConfig()->getFee();
    }

    public function isFreeShipping()
    {
        return $this->getConfig()->getIsFreeShipping();
    }

    public function getSubtotalDisplayType()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUBTOTAL_DISPLAY_TYPE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    protected function getConfig()
    {
        if($this->config === null){
            $this->config = new \Magento\Framework\DataObject(
                $this->scopeConfig->getValue(self::XML_PATH_BULK_GOODS_CONFIGURATION, \Magento\Store\Model\ScopeInterface::SCOPE_STORE)
            );
        }

        return $this->config;
    }
}
