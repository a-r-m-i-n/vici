<?php

use T3\Vici\UserFunction\ItemsProcFunc\AvailableViciTables;
use T3\Vici\UserFunction\PreviewRenderer\ViciFrontendPlugin;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Vici Frontend Content Element
$newColumns = [
    'tx_vici_table' => [
        'label' => 'VICI Table',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => AvailableViciTables::class . '->get',
            'minitems' => 0,
            'maxitems' => 1,
        ],
    ],
    'tx_vici_template' => [
        'label' => 'VICI Template',
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
    'VICI Frontend Plugin',
    'vici-extension-icon',
    'plugins',
    'Output custom table contents made by EXT:vici',
);

TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;VICI Records,tx_vici_table,pages,recursive,--div--;VICI Templates,tx_vici_template',
    $pluginIdentifier,
    'after:palette:headers'
);

$GLOBALS['TCA']['tt_content']['types'][$pluginIdentifier]['previewRenderer'] = ViciFrontendPlugin::class;
