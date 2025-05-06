<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'vici',
    'description' => 'Versatile Interface for Custom Information - TYPO3 extension to define, manage and output custom data structures using TCA',
    'version' => '0.1.0-dev',
    'state' => 'alpha',
    'author' => 'Armin Vieweg',
    'author_email' => 'armin@v.ieweg.de',
    'author_company' => '',
    'constraints' => [
        'depends' => [
            'php' => '8.2.0-8.3.99',
            'typo3' => '11.5.0-13.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => ['T3\\Vici\\' => 'Classes']
    ],
];
