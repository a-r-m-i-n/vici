<?php

namespace T3\Vici\Generator\Extbase;

use TYPO3\CMS\Core\Utility\GeneralUtility;

trait RelationFieldTypeTrait
{
    public function createRelationPropertyValue(
        string $name,
        ?string $relationField,
        string $mappingMode,
        ?string $extbaseModelClass,
        bool $isMultiple,
        ?string $defaultValue,
        bool $isManualSelect = false,
    ): PropertyValue {
        if ('models' === $mappingMode && !empty($relationField)) {
            if (!empty($extbaseModelClass)) {
                $fqcn = '\\' . ltrim($extbaseModelClass, '\\');
                if (class_exists($fqcn)) {
                    return new PropertyValue($name, $fqcn, false, null, $isMultiple);
                }
            }

            /** @var ModelClassNameResolver $resolver */
            $resolver = GeneralUtility::makeInstance(ModelClassNameResolver::class);
            $fqcn = $resolver->getExtbaseModelByTablename($relationField);
            if ($fqcn) {
                return new PropertyValue($name, '\\' . $fqcn, false, null, $isMultiple);
            }
            // Fallback
            $mappingMode = 'arrays';
        }

        if ('arrays' === $mappingMode) {
            if ($isManualSelect) {
                // Only for type:select with manual list
                $phpdoc = 'string';
                $default = $defaultValue;
                if ($isMultiple) {
                    $phpdoc = ['array', $phpdoc . '[]'];
                    $default = [];
                }

                return new PropertyValue($name, $phpdoc, false, $default ?? '');
            }

            $phpdoc = 'array<string, mixed>';
            if ($isMultiple) {
                $phpdoc = 'array<int, ' . $phpdoc . '>';
            }

            return new PropertyValue($name, ['array', $phpdoc], false, []);
        }

        // Raw
        return new PropertyValue($name, 'string', false, $defaultValue ?? '');
    }
}
