<?php

use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;
use T3\Vici\UserFunction\TcaFieldValidator\ReservedTcaColumnsValidator;


// Register custom TCA field validators
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][LeadingLetterValidator::class] = '';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals'][ReservedTcaColumnsValidator::class] = '';
