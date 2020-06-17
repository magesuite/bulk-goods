<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$rateRepository = $objectManager->create(\Magento\Tax\Api\TaxRateRepositoryInterface::class);
$taxRateFactory = $objectManager->create(\Magento\Tax\Api\Data\TaxRateInterfaceFactory::class);
$dataObjectHelper = $objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
$taxRuleFactory = $objectManager->create(\Magento\Tax\Api\Data\TaxRuleInterfaceFactory::class);
$taxRuleRepository = $objectManager->create(\Magento\Tax\Api\TaxRuleRepositoryInterface::class);

$taxData = [
    'tax_country_id' => 'DE',
    'tax_region_id' => 0,
    'tax_postcode' => '*',
    'rate' => '19.0000',
    'code' => 'DE VAT Rate',
    'zip_is_range' => null,
    'zip_from' => null,
    'zip_to' => null
];

$taxRate = $taxRateFactory->create();
$dataObjectHelper->populateWithArray($taxRate, $taxData, \Magento\Tax\Api\Data\TaxRateInterface::class);
$taxRateData = $rateRepository->save($taxRate);

$taxRuleDataObject = $taxRuleFactory->create();
$taxRuleDataObject->setCode('test')
    ->setTaxRateIds([$taxRateData->getId()])
    ->setCustomerTaxClassIds([3])
    ->setProductTaxClassIds([2])
    ->setPriority(0)
    ->setPosition(0);
$taxRuleRepository->save($taxRuleDataObject);
