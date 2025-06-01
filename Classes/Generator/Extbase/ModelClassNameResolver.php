<?php

namespace T3\Vici\Generator\Extbase;

use T3\Vici\Generator\StaticValues;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;
use TYPO3\CMS\Extbase\Domain\Model\File;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

readonly class ModelClassNameResolver
{
    public function __construct(
        private StaticValues $staticValues,
    ) {
    }

    public function getExtbaseModelByTablename(string $tablename): ?string
    {
        if ('sys_category' === $tablename) {
            return Category::class;
        }
        if ('sys_file' === $tablename) {
            return File::class;
        }
        if ('sys_file_reference' === $tablename) {
            return FileReference::class;
        }

        if (str_starts_with($tablename, 'tx_vici_custom_')) {
            $name = substr($tablename, strlen('tx_vici_custom_'));

            return $this->staticValues->getProxyClassNamespace(GeneralUtility::underscoredToUpperCamelCase($name));
        }

        return null;
    }
}
