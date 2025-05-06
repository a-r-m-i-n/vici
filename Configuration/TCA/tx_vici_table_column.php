<?php

use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;
use T3\Vici\UserFunction\TcaFieldValidator\ReservedTcaColumnsValidator;

$showitem = <<<TXT
    --div--;General,
    --palette--;;general_header,

    --div--;New Tab,
    TXT;

return [
    'ctrl' => [
        'title' => 'Table column',
        'label' => 'name',
        'hideTable' => true,
        'adminOnly' => true,
        'rootLevel' => 1,
        'tstamp' => 'tstamp',
        'crdate' => 'crdate',
        'versioningWS' => true,
        'origUid' => 't3_origuid',
        'delete' => 'deleted',
        'sortby' => 'sorting',
        'enablecolumns' => [
            'disabled' => 'hidden',
        ],
        'type' => 'type',
        'typeicon_column' => 'type',
        'typeicon_classes' => [
            'input' => 'form-text',
            'text' => 'form-textarea',
            'select' => 'form-single-select',
            // TODO: Add all types
        ],
    ],
    'palettes' => [
        'general_header' => ['showitem' => 'type,name,--linebreak--,title'],
    ],

    'types' => [
        0 => [
            'showitem' => 'type',
        ],
        'input' => ['showitem' => $showitem],
        'text' => ['showitem' => $showitem],
        'select' => ['showitem' => $showitem],
        // TODO: Add all types
    ],
    'columns' => [
        'name' => [
            'exclude' => false,
            'label' => 'Column name',
            'description' => 'Allowed characters are: lowercase letters (a-z), numbers (0-9) and underscores (_)',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'min' => 3,
                'max' => 48,
                'eval' => 'trim,is_in,' . LeadingLetterValidator::class . ',' . ReservedTcaColumnsValidator::class,
                'is_in' => 'abcdefghijklmnopqrstuvwxyz01234567890_',
                'placeholder' => 'new_column_name',
            ],
        ],

        'type' => [
            'exclude' => 0,
            'label' => 'Type',
            'description' => 'Defines the TCA type of the new column',
            'onChange' => 'reload',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'items' => [
                    [
                        'label' => 'Input',
                        'value' => 'input',
                        'icon' => 'form-text',
                    ],
                    [
                        'label' => 'Textarea (and RTE)',
                        'value' => 'text',
                        'icon' => 'form-textarea',
                    ],
                    [
                        'label' => 'Select',
                        'value' => 'select',
                        'icon' => 'form-single-select',
                    ],
                    // TODO: Add all types
                ],
                'default' => 'input',
            ],
        ],

        'title' => [
            'exclude' => false,
            'label' => 'Title',
            'description' => 'Title of field, being displayed in backend',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],

        'parent' => [
            'exclude' => false,
            'label' => 'Parent table',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_vici_table',
                'size' => 1,
                'minitems' => 0,
                'maxitems' => 1,
                'default' => 0,
            ],
        ],
    ],
];
