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
        'copyAfterDuplFields' => 'columns',
    ],
    'palettes' => [
        'general' => ['showitem' => 'name,hidden'],
        'label' => ['showitem' => 'label,label_alt_force,--linebreak--,label_alt'],
        'visibility' => ['showitem' => 'root_level,ignore_page_type,hide_table,'],
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
                --palette--;;general,
                columns,
                --palette--;;label,
                --palette--;Record type visibility;visibility,
                --palette--;Enable system columns;system_columns,

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
        'label_alt' => [
            'exclude' => false,
            'label' => 'Alternative label',
            'description' => 'Optional columns being displayed as label, if label field is empty.',
            'displayCond' => 'FIELD:columns:>:0',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectMultipleSideBySide',
                'foreign_table' => 'tx_vici_table_column',
                'foreign_table_where' => 'AND tx_vici_table_column.parent=###THIS_UID### ORDER BY tx_vici_table_column.sorting',
                'minitems' => 0,
            ],
        ],
        'label_alt_force' => [
            'exclude' => false,
            'label' => 'Force alternative label',
            'description' => 'If enabled, the label and the alternative label columns are always shown in the record title, separated by comma.',
            'displayCond' => 'FIELD:columns:>:0',
            'config' => [
                'type' => 'check',
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

        'root_level' => [
            'exclude' => false,
            'label' => 'Root level',
            'description' => 'Determines where a record may exist in the page tree.',
            'config' => [
                'type' => 'select',
                'renderType' => 'selectSingle',
                'minitems' => 1,
                'maxitems' => 1,
                'items' => [
                    [
                        'label' => 'Can only exist in the page tree (Default)',
                        'value' => 0,
                    ],
                    [
                        'label' => 'Can only exist in the root',
                        'value' => 1,
                    ],
                    [
                        'label' => 'Can exist in both page tree and root',
                        'value' => -1,
                    ],
                ],
                'default' => 0,
            ],
            'onChange' => 'reload',
        ],
        'ignore_page_type' => [
            'exclude' => false,
            'label' => 'Ignore page type restriction',
            'description' => 'Allows to create this record type on any page type, not just in (system) folders.',
            'config' => [
                'type' => 'check',
            ],
            'displayCond' => 'FIELD:root_level:<:1',
        ],
        'hide_table' => [
            'exclude' => false,
            'label' => 'Hide table',
            'description' => 'Hide this table in record listings, especially the list module.',
            'config' => [
                'type' => 'check',
            ],
        ],

        'enable_column_hidden' => [
            'exclude' => false,
            'label' => 'Hidden column',
            'description' => 'Allows to enable/disable record.',
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
            'label' => 'Start- and endtime columns',
            'description' => 'Add timings to visibility of the record type.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_fegroup' => [
            'exclude' => false,
            'label' => 'fe_group column',
            'description' => 'Allows to restrict access to frontend user groups for this record type.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_sorting' => [
            'exclude' => false,
            'label' => 'Sorting column',
            'description' => 'Allows to sort records in list view manually.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_timestamps' => [
            'exclude' => false,
            'label' => 'Timestamp columns',
            'description' => 'Adds creation and last update timestamps to database.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_versioning' => [
            'exclude' => false,
            'label' => 'Versioning columns',
            'description' => 'Enables versioning for this record type.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_editlock' => [
            'exclude' => false,
            'label' => 'Editlock column',
            'description' => 'Adds the ability to edit certain records of this type can be limited to administrators.',
            'config' => [
                'type' => 'check',
            ],
        ],
        'enable_column_languages' => [
            'exclude' => false,
            'label' => 'Translation columns',
            'description' => 'Makes this record type translatable.',
            'config' => [
                'type' => 'check',
            ],
        ],
    ],
];
