<?php

use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

// Vici Frontend Content Element
$newColumns = [
    'tx_vici_table' => [
        'label' => 'Vici Table',
        'config' => [
            'type' => 'select',
            'renderType' => 'selectSingle',
            'foreign_table' => 'tx_vici_table',
            //            'foreign_table_where' => 'ORDER BY tx_vici_table.name',
            'minitems' => 0,
            'maxitems' => 1,
            //            'allowNonIdValues' => true,
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
    'tx_vici_table,pages,recursive,--div--;Template,tx_vici_template',
    $pluginIdentifier,
    'after:palette:headers'
);
