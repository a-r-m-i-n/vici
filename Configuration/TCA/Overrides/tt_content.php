<?php

use T3\Vici\UserFunction\ItemsProcFunc\AvailableViciTables;
use T3\Vici\UserFunction\PreviewRenderer\ViciFrontendPlugin;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// VICI Frontend Plugin
$pluginIdentifier = ExtensionUtility::registerPlugin(
    'vici',
    'Frontend',
    'VICI Frontend Plugin',
    'vici-extension-icon',
    'plugins',
    'Output custom table contents made by EXT:vici',
);

ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;VICI records,tx_vici_table,tx_vici_options,pages,recursive,--div--;VICI template,tx_vici_template,--div--;VICI detail template,tx_vici_template_detail',
    $pluginIdentifier,
    'after:palette:headers'
);

$GLOBALS['TCA']['tt_content']['types'][$pluginIdentifier]['previewRenderer'] = ViciFrontendPlugin::class;

$newColumns = [
    'tx_vici_table' => [
        'label' => 'VICI table',
        'l10n_mode' => 'exclude',
        'l10n_display' => 'defaultAsReadonly',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'itemsProcFunc' => AvailableViciTables::class . '->get',
            'minitems' => 0,
            'maxitems' => 1,
        ],
    ],
    'tx_vici_options' => [
        'label' => 'VICI options',
        'l10n_mode' => 'exclude',
        'config' => [
            'type' => 'flex',
            'ds' => [
                'default' => 'FILE:EXT:vici/Configuration/FlexForms/ViciFrontendPlugin.xml',
            ],
        ],
    ],
    'tx_vici_template' => [
        'label' => 'VICI template',
        'config' => [
            'type' => 'text',
            'renderType' => 'codeEditor',
            'format' => 'html',
        ],
    ],
    'tx_vici_template_detail' => [
        'label' => 'VICI detail template',
        'config' => [
            'type' => 'text',
            'renderType' => 'codeEditor',
            'format' => 'html',
        ],
        'displayCond' => 'USER:T3\\Vici\\UserFunction\\DisplayCondition\\DetailpageIsEnabled->check',
    ],
];
ExtensionManagementUtility::addTCAcolumns('tt_content', $newColumns);
