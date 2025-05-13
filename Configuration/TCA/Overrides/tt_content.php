<?php

use T3\Vici\UserFunction\ItemsProcFunc\AvailableViciTables;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Vici Frontend Content Element
$newColumns = [
    'tx_vici_table' => [
        'label' => 'Vici Table',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => AvailableViciTables::class . '->get',
            'minitems' => 0,
            'maxitems' => 1,
        ],
    ],
    'tx_vici_template' => [
        'label' => 'Vici Template',
        'config' => [
            'type' => 'text',
            'renderType' => 'codeEditor',
            'format' => 'html',
        ],
    ],
];
TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('tt_content', $newColumns);

$pluginIdentifier = ExtensionUtility::registerPlugin(
    'vici',
    'Frontend',
    'Vici Frontend Plugin',
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Vici Records,tx_vici_table,pages,recursive,--div--;Vici Templates,tx_vici_template',
    $pluginIdentifier,
    'after:palette:headers'
);
