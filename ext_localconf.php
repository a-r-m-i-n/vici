<?php

use T3\Vici\Controller\FrontendController;
use T3\Vici\Generator\ProxyClassLoader;
use T3\Vici\Hook\DataHandlerHook;
use T3\Vici\Localization\TranslatableInputElement;
use T3\Vici\Localization\ViciParser;
use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;
use T3\Vici\UserFunction\TcaFieldValidator\ReservedTcaColumnsValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;


// Register custom TCA field validators
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][LeadingLetterValidator::class] = '';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][ReservedTcaColumnsValidator::class] = '';

// DataHandler Hook
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['vici'] = DataHandlerHook::class;
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc']['vici'] = DataHandlerHook::class . '->clearCachePostProc';

// Proxy class loader
/** @var ProxyClassLoader $classLoader */
$classLoader = GeneralUtility::makeInstance(ProxyClassLoader::class);
$classLoader->registerAutoloader();

// Vici Frontend Content Element
ExtensionUtility::configurePlugin(
    'Vici',
    'Frontend',
    [
        FrontendController::class => 'index,show',
    ],
    [],
    ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);

// Add global Typoscript
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript('vici', 'setup', <<<TYPOSCRIPT
config.pageTitleProviders.vici {
    provider = T3\Vici\FrontendPlugin\PageTitleProvider
    before = record
    after = altPageTitle

    prependWrap = || - |
    appendWrap = | - ||
}
TYPOSCRIPT
);

// Custom localization
$languageFormatPriority = GeneralUtility::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority'], true);
$languageFormatPriority[] = 'vici';
$GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['format']['priority'] = implode(',', $languageFormatPriority);
$GLOBALS['TYPO3_CONF_VARS']['SYS']['lang']['parser']['vici'] = ViciParser::class;

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1748343572] = [
    'nodeName' => 'viciTranslatableInput',
    'priority' => 70,
    'class' => TranslatableInputElement::class,
];
