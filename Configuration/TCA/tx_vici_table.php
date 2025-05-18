<?php

use T3\Vici\UserFunction\ItemsProcFunc\Icons;
use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;

return [
    'ctrl' => [
        'title' => 'VICI Table',
        'label' => 'name',
        'rootLevel' => -1,
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
        'security' => [
            'ignoreRootLevelRestriction' => false,
        ],
        'copyAfterDuplFields' => 'columns',
    ],
    'palettes' => [
        'system_columns' => ['showitem' => <<<TXT
            enable_column_hidden,enable_column_deleted,--linebreak--,
            enable_column_start_end_time, enable_column_fegroup,--linebreak--,
            enable_column_sorting,--linebreak--,
            enable_column_languages,enable_column_versioning,--linebreak--,
            enable_column_timestamps,enable_column_editlock
            TXT
        ],
    ],
    'types' => [
        0 => [
            'showitem' => <<<TXT
                --div--;General,
                name,columns,label,--palette--;Enable system columns;system_columns;,

                --div--;Appearance,
                title,icon,
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
        'label' => [
            'exclude' => false,
            'label' => 'Label',
            'description' => 'Column used for the label of this record (in list view)',
            'displayCond' => 'FIELD:columns:>:0',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'foreign_table' => 'tx_vici_table_column',
                'foreign_table_where' => 'AND tx_vici_table_column.parent=###THIS_UID### ORDER BY tx_vici_table_column.sorting',
                'minitems' => 0,
                'maxitems' => 1,
                'allowNonIdValues' => true,
            ],
        ],
        'title' => [
            'exclude' => false,
            'label' => 'Title',
            'description' => 'Title of the table, being displayed in backend',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => false,
                'eval' => 'trim',
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
                'default' => 'content-database',
                'fieldWizard' => [
                    'selectIcons' => [
                        'disabled' => false,
                    ],
                ],
            ],
        ],

        'enable_column_hidden' => [
            'exclude' => false,
            'label' => 'Hidden column',
            'description' => 'Allows to enable/disable record',
            'config' => [
                'type' => 'check',
                'default' => true,
            ],
        ],
        'enable_column_deleted' => [
            'exclude' => false,
            'label' => 'Deleted column',
            'description' => 'Adds soft-delete. Deleted records are moved to recycler.',
            'config' => [
                'type' => 'check',
                'default' => true,
            ],
        ],
        'enable_column_start_end_time' => [
            'exclude' => false,
            'label' => 'Start- and Endtime columns',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_fegroup' => [
            'exclude' => false,
            'label' => 'fe_group column',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_sorting' => [
            'exclude' => false,
            'label' => 'Sorting column',
            'description' => 'Allows to sort records in list view manually',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_timestamps' => [
            'exclude' => false,
            'label' => 'Timestamp columns',
            'description' => 'Adds creation and last update timestamps to database',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_versioning' => [
            'exclude' => false,
            'label' => 'Versioning columns',
            'description' => 'Enables versioning for this record type',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_editlock' => [
            'exclude' => false,
            'label' => 'Editlock column',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_languages' => [
            'exclude' => false,
            'label' => 'Translation columns',
            'description' => 'Makes this record type translatable',
            'config' => [
                'type' => 'check',
            ],
        ],
    ],
];
