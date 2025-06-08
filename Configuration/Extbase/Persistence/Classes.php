<?php

use T3\Vici\Generator\StaticValues;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/** @var StaticValues $staticValues */
$staticValues = GeneralUtility::makeInstance(StaticValues::class);

/** @var ViciRepository $viciRepository */
$viciRepository = GeneralUtility::makeInstance(ViciRepository::class);

$customMappings = [];
foreach ($viciRepository->findAllTables() as $table) {
    $tableColumns = $viciRepository->findTableColumnsByTableUid($table['uid']);
    if (empty($tableColumns)) {
        continue;
    }

    $tableName = $staticValues->getFullTableName($table['name']);
    $modelName = $staticValues->getProxyClassNamespace(GeneralUtility::underscoredToUpperCamelCase($table['name']));
    $customMappings[$modelName] = ['tableName' => $tableName];
}

return $customMappings;
