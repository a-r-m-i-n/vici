<?php

use T3\Vici\Controller\FrontendController;
use T3\Vici\Generator\ProxyClassLoader;
use T3\Vici\Hook\TcemainHook;
use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;
use T3\Vici\UserFunction\TcaFieldValidator\ReservedTcaColumnsValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;


// Register custom TCA field validators
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][LeadingLetterValidator::class] = '';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][ReservedTcaColumnsValidator::class] = '';

// TCEMAIN Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['vici'] = TcemainHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['vici'] = TcemainHook::class;

// Proxy class loader
/** @var ProxyClassLoader $classLoader */
$classLoader = GeneralUtility::makeInstance(ProxyClassLoader::class);
$classLoader->registerAutoloader();

// Vici Frontend Content Element
ExtensionUtility::configurePlugin(
    'Vici',
    'Frontend',
    [
        FrontendController::class => 'index',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
