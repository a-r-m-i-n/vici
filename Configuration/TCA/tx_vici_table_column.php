<?php

use T3\Vici\Generator\Tca\FieldTypes\FieldTypes;

return [
    'ctrl' => [
        'title' => 'VICI table column',
        'label' => 'name',
        'hideTable' => true,
        'rootLevel' => -1,
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
        'typeicon_classes' => FieldTypes::listTypeiconClasses(),
    ],
    'palettes' => FieldTypes::listTypePalettes(),
    'types' => FieldTypes::listTypeTypes(),
    'columns' => FieldTypes::listTypeColumns(),
];
