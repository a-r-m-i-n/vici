<?php

use T3\Vici\Generator\TcaManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$tcaManager = GeneralUtility::makeInstance(TcaManager::class);
$tcaManager->load();
