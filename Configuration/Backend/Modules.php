<?php

use T3\Vici\Controller\Backend\ViciModuleController;

return [
    'tools_ViciModule' => [
        'parent' => 'tools',
        'access' => 'user',
        'workspaces' => 'live',
        'iconIdentifier' => 'vici-extension-icon',
        'path' => '/module/tools/ViciModule',
        'labels' => 'LLL:EXT:vici/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'Vici',
        'controllerActions' => [
            ViciModuleController::class => [
                'index',
                'edit',
                'clearAllCaches',
                'showDatabaseChanges',
                'applyDatabaseChanges',
            ],
        ],
    ],
];
