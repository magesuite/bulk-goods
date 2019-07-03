<?php

namespace MageSuite\BulkGoods\Helper\Configuration;

class BulkGoods extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_BULK_GOODS_CONFIGURATION = 'bulk_goods/general';

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
        return $this->getConfig()->getFee();
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
