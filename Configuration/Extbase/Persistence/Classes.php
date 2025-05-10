<?php

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @var ViciRepository $viciRepository */
$viciRepository = GeneralUtility::makeInstance(ViciRepository::class);
$customMappings = [];
foreach ($viciRepository->findAllTables() as $table) {
    // TODO Hardcoded table prefix and model namespace
    $tableName = 'tx_vici_custom_' . $table['name'];
    $modelName = 'T3\\Vici\\Custom\\Domain\\Model\\' . GeneralUtility::underscoredToUpperCamelCase($table['name']);
    $customMappings[$modelName] = ['tableName' => $tableName];
}

return $customMappings;
