<?php

return [
    'ctrl' => [
        'title' => 'VICI list items',
        'label' => 'name',
        'label_alt' => 'value',
        'label_alt_force' => true,
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
        'typeicon_classes' => [
            'default' => 'actions-list-alternative',
        ],
    ],
    'types' => [
        0 => [
            'showitem' => 'name,value',
        ],
    ],
    'columns' => [
        'name' => [
            'exclude' => false,
            'label' => 'Label',
            'config' => [
                'type' => 'user',
                'renderType' => 'viciTranslatableInput',
                'required' => true,
            ],
        ],
        'value' => [
            'exclude' => false,
            'label' => 'Value',
            'config' => [
                'type' => 'input',
                'size' => 30,
                'required' => true,
                'eval' => 'trim',
            ],
        ],
    ],
];
