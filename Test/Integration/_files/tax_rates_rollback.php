<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$taxRule = $objectManager->create(\Magento\Tax\Api\Data\TaxRuleInterface::class);
$taxRate = $objectManager->create(\Magento\Tax\Api\Data\TaxRateInterface::class);

$taxRule->load('test', 'code');

if ($taxRule->getId()) {
    $taxRule->delete();
}

$taxRate->load('DE VAT Rate', 'code');

if ($taxRate->getId()) {
    $taxRate->delete();
}
