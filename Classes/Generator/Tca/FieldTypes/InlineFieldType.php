<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\ModelClassNameResolver;
use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InlineFieldType extends AbstractFieldType
{
    protected string $iconClass = 'actions-form-insert-after';

    public function getGroup(): string
    {
        return 'select';
    }

    public function getTypePalettes(): array
    {
        return [
            'inline_foreign_table' => 'foreign_table,foreign_field,foreign_sortby',
            'inline_min_max_items' => 'select_minitems,select_maxitems',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;inline_foreign_table,
        --palette--;;inline_min_max_items,
        --div--;Extbase,extbase_mapping_mode,extbase_model_class
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'foreign_field' => [
                'exclude' => false,
                'label' => 'Foreign table field',
                'description' => 'Defines the field of the child record pointing to the parent record.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                ],
            ],
            'foreign_sortby' => [
                'exclude' => false,
                'label' => 'Foreign sortby',
                'description' => 'Defines the field of the child record which stores custom sorting.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $tableColumn
     *
     * @return array<string, mixed>
     */
    public function buildTcaConfig(array $tableColumn): array
    {
        $tcaConfig = [];

        $tcaConfig['foreign_table'] = $tableColumn['foreign_table'];
        if (!empty($tableColumn['foreign_field'])) {
            $tcaConfig['foreign_field'] = $tableColumn['foreign_field'];
        }
        if (!empty($tableColumn['foreign_sortby'])) {
            $tcaConfig['foreign_sortby'] = $tableColumn['foreign_sortby'];
        }

        if (!empty($tableColumn['select_minitems'])) {
            $tcaConfig['minitems'] = $tableColumn['select_minitems'];
        }
        if (!empty($tableColumn['select_maxitems'])) {
            $tcaConfig['maxitems'] = $tableColumn['select_maxitems'];
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        $mappingMode = $tableColumn['extbase_mapping_mode'];

        $isMultiple = true;
        if (1 === $tableColumn['select_maxitems']) {
            $isMultiple = false;
        }

        if ('models' === $mappingMode && !empty($tableColumn['foreign_table'])) {
            if (!empty($tableColumn['extbase_model_class'])) {
                $fqcn = '\\' . ltrim($tableColumn['extbase_model_class'], '\\');
                if (class_exists($fqcn)) {
                    return new PropertyValue($name, $fqcn, false, null, $isMultiple);
                }
            }

            /** @var ModelClassNameResolver $resolver */
            $resolver = GeneralUtility::makeInstance(ModelClassNameResolver::class);
            $fqcn = $resolver->getExtbaseModelByTablename($tableColumn['foreign_table']);
            if ($fqcn) {
                return new PropertyValue($name, '\\' . $fqcn, false, null, $isMultiple);
            }
            // Fallback
            $mappingMode = 'arrays';
        }

        if ('arrays' === $mappingMode) {
            $phpdoc = 'string[]';
            if ($isMultiple) {
                $phpdoc = 'array<int, ' . $phpdoc . '>';
            }

            return new PropertyValue($name, ['array', $phpdoc], false, []);
        }

        // Raw
        return new PropertyValue($name, 'string', false, $tableColumn['default'] ?? '');
    }
}
