<?php

use T3\Vici\UserFunction\ItemsProcFunc\Icons;
use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;

return [
    'ctrl' => [
        'title' => 'Table / Record type',
        'label' => 'name',
        'adminOnly' => true,
        'rootLevel' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'default_sortby' => 'name',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'typeicon_classes' => [
            'default' => 'mimetypes-x-content-table',
        ],
        'copyAfterDuplFields' => 'columns',
    ],
    'types' => [
        0 => [
            'showitem' => <<<TXT
                --div--;General,
                name,columns,

                --div--;Icon,
                icon,
                TXT,
        ],
    ],
    'columns' => [
        'name' => [
            'exclude' => false,
            'label' => 'Table name',
            'description' => 'The table name will get prefixed with "tx_vici_custom_" in database.' . PHP_EOL . 'Allowed characters are: lowercase letters (a-z), numbers (0-9) and underscores (_)',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'min' => 3,
                'max' => 48,
                'eval' => 'trim,is_in,unique,' . LeadingLetterValidator::class,
                'is_in' => 'abcdefghijklmnopqrstuvwxyz01234567890_',
                'placeholder' => 'new_table_name',
            ],
        ],

        'columns' => [
            'exclude' => false,
            'label' => 'Columns',
            'config' => [
                'type' => 'inline',
                'foreign_table' => 'tx_vici_table_column',
                'foreign_sortby' => 'sorting',
                'foreign_field' => 'parent',
                'minitems' => 0,
                'maxitems' => PHP_INT_MAX,
                'appearance' => [
                    'expandSingle' => true,
                    'newRecordLinkTitle' => 'Create new table column',
                    'levelLinksPosition' => 'bottom',
                    'useSortable' => 1,
                ],
            ],
        ],

        'icon' => [
            'exclude' => false,
            'label' => 'Icon',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'itemsProcFunc' => Icons::class . '->getAvailableIcons',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],
    ],
];
