<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$rateRepository = $objectManager->create(\Magento\Tax\Api\TaxRateRepositoryInterface::class);
$taxRateFactory = $objectManager->create(\Magento\Tax\Api\Data\TaxRateInterfaceFactory::class);
$dataObjectHelper = $objectManager->create(\Magento\Framework\Api\DataObjectHelper::class);
$taxRuleFactory = $objectManager->create(\Magento\Tax\Api\Data\TaxRuleInterfaceFactory::class);
$taxRuleRepository = $objectManager->create(\Magento\Tax\Api\TaxRuleRepositoryInterface::class);

$taxesData = [
    [
        'tax_country_id' => 'DE',
        'tax_region_id' => 0,
        'tax_postcode' => '*',
        'rate' => 19,
        'code' => 'DE VAT Rate',
        'zip_is_range' => null,
        'zip_from' => null,
        'zip_to' => null
    ],
    [
        'tax_country_id' => 'FR',
        'tax_region_id' => 0,
        'tax_postcode' => '*',
        'rate' => 0,
        'code' => 'FR without VAT Rate',
        'zip_is_range' => null,
        'zip_from' => null,
        'zip_to' => null
    ],
    [
        'tax_country_id' => 'PL',
        'tax_region_id' => 0,
        'tax_postcode' => '*',
        'rate' => 23,
        'code' => 'PL VAT Rate',
        'zip_is_range' => null,
        'zip_from' => null,
        'zip_to' => null
    ]
];

$taxRateIds = [];

foreach ($taxesData as $taxData) {
    $taxRate = $taxRateFactory->create();
    $dataObjectHelper->populateWithArray($taxRate, $taxData, \Magento\Tax\Api\Data\TaxRateInterface::class);
    $taxRate = $rateRepository->save($taxRate);

    $taxRateIds[] = $taxRate->getId();
}

$taxRuleDataObject = $taxRuleFactory->create();
$taxRuleDataObject->setCode('test')
    ->setTaxRateIds($taxRateIds)
    ->setCustomerTaxClassIds([3])
    ->setProductTaxClassIds([2])
    ->setPriority(0)
    ->setPosition(0);
$taxRuleRepository->save($taxRuleDataObject);
