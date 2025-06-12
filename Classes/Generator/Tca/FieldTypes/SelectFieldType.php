<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\ModelClassNameResolver;
use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\Repository\ViciRepository;
use T3\Vici\UserFunction\ItemsProcFunc\TcaTables;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SelectFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-single-select';

    public function getTypePalettes(): array
    {
        return [
            'select_types' => 'select_type,select_render_type,use_radio',
            'select_default_min_max_items' => 'default,select_minitems,select_maxitems,add_empty_option,select_required',
            'select_foreign_table' => 'foreign_table,foreign_table_where',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;select_types,
        --palette--;;select_foreign_table,
        items,
        --palette--;;select_default_min_max_items,
        --div--;Extbase,extbase_mapping_mode,extbase_model_class
        TXT;

    /**
     * @param array<string, mixed>|null $tableColumn
     */
    public function getType(?array $tableColumn = null): string
    {
        if (!$tableColumn) {
            return parent::getType();
        }

        if ('selectSingle' === $tableColumn['select_render_type'] && 'manual' === $tableColumn['select_type'] && $tableColumn['use_radio']) {
            return 'radio';
        }

        return 'select';
    }

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'select_render_type' => [
                'exclude' => false,
                'label' => 'Render type',
                'description' => 'Defines how to render the select field.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'selectSingle',
                            'value' => 'selectSingle',
                        ],
                        [
                            'label' => 'selectSingleBox',
                            'value' => 'selectSingleBox',
                        ],
                        [
                            'label' => 'selectCheckBox',
                            'value' => 'selectCheckBox',
                        ],
                        [
                            'label' => 'selectMultipleSideBySide',
                            'value' => 'selectMultipleSideBySide',
                        ],
                    ],
                    'default' => 'selectSingle',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],
            'select_type' => [
                'exclude' => false,
                'label' => 'Type',
                'description' => 'Defines what to show.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Manual list of items',
                            'value' => 'manual',
                        ],
                        [
                            'label' => 'Items from a foreign (database) table',
                            'value' => 'foreign_table',
                        ],
                    ],
                    'default' => 'manual',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],

            'use_radio' => [
                'exclude' => false,
                'label' => 'Use radio buttons',
                'description' => 'When enabled, instead of a select box a list of radio buttons are displayed.',
                'config' => [
                    'type' => 'check',
                ],
                'displayCond' => [
                    'AND' => [
                        'FIELD:select_type:=:manual',
                        'FIELD:select_render_type:=:selectSingle',
                    ],
                ],
            ],
            'foreign_table' => [
                'exclude' => false,
                'label' => 'Foreign table',
                'description' => 'Select a table, the user should choose an item from.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'itemsProcFunc' => TcaTables::class . '->get',
                    'minitems' => 1,
                ],
                'displayCond' => [
                    'OR' => [
                        'FIELD:select_type:=:foreign_table',
                        'FIELD:type:=:inline',
                    ],
                ],
            ],
            'foreign_table_where' => [
                'exclude' => false,
                'label' => 'Foreign table where',
                'description' => 'Allows you to extend the WHERE statement, which items from selected foreign table should get displayed.',
                'config' => [
                    'type' => 'text',
                    'eval' => 'trim',
                    'placeholder' => 'e.g. "AND pid = 1"',
                ],
                'displayCond' => 'FIELD:select_type:=:foreign_table',
            ],

            'items' => [
                'exclude' => false,
                'label' => 'Items',
                'config' => [
                    'type' => 'inline',
                    'foreign_table' => 'tx_vici_table_column_item',
                    'foreign_sortby' => 'sorting',
                    'foreign_field' => 'column_select_items',
                    'minitems' => 0,
                    'maxitems' => PHP_INT_MAX,
                    'appearance' => [
                        'expandSingle' => true,
                        'newRecordLinkTitle' => 'Create new item',
                        'levelLinksPosition' => 'bottom',
                        'useSortable' => 1,
                    ],
                ],
            ],
            'select_minitems' => [
                'exclude' => false,
                'label' => 'Minimum amount selected items',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
                'displayCond' => [
                    'OR' => [
                        'FIELD:select_render_type:!=:selectSingle',
                        'FIELD:type:=:group',
                        'FIELD:type:=:inline',
                    ],
                ],
            ],
            'select_maxitems' => [
                'exclude' => false,
                'label' => 'Maximum amount of selected items',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
                'displayCond' => [
                    'OR' => [
                        'FIELD:select_render_type:!=:selectSingle',
                        'FIELD:type:=:group',
                        'FIELD:type:=:inline',
                    ],
                ],
            ],
            'add_empty_option' => [
                'exclude' => false,
                'label' => 'Add empty option',
                'config' => [
                    'type' => 'check',
                ],
                'displayCond' => 'FIELD:select_render_type:=:selectSingle',
            ],

            'extbase_mapping_mode' => [
                'exclude' => false,
                'label' => 'Extbase mapping mode',
                'description' => 'Defines how this field is processed for the Extbase model in frontend.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Try to resolve Extbase models, otherwise use associative arrays',
                            'value' => 'models',
                        ],
                        [
                            'label' => 'Always use associative arrays',
                            'value' => 'arrays',
                        ],
                        [
                            'label' => 'Return the value(s) as it is (no processing)',
                            'value' => 'raw',
                        ],
                    ],
                    'default' => 'models',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
                'displayCond' => [
                    'OR' => [
                        'FIELD:select_type:=:foreign_table',
                        'FIELD:type:=:group',
                        'FIELD:type:=:inline',
                    ],
                ],
            ],
            'extbase_model_class' => [
                'exclude' => false,
                'label' => 'Extbase model class',
                'description' => 'Allows you to define the Extbase model full qualified class name to get used.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                    'placeholder' => 'auto',
                ],
                'displayCond' => [
                    'AND' => [
                        'OR' => [
                            'FIELD:select_type:=:foreign_table',
                            'FIELD:type:=:group',
                            'FIELD:type:=:inline',
                        ],
                        'FIELD:extbase_mapping_mode:=:models',
                    ],
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
        $tcaConfig = [
            'renderType' => $tableColumn['select_render_type'],
        ];

        if (0 !== $tableColumn['items']) {
            /** @var ViciRepository $viciRepository */
            $viciRepository = GeneralUtility::makeInstance(ViciRepository::class);
            $items = $viciRepository->findListItemsByColumnUid($tableColumn['uid']);
            $tcaItems = [];
            if ('selectSingle' === $tableColumn['select_render_type'] && !empty($tableColumn['add_empty_option'])) {
                $tcaItems[] = [
                    'label' => '',
                    'value' => '',
                ];
            }
            foreach ($items as $item) {
                $tcaItems[] = [
                    'label' => $item['name'],
                    'value' => $item['value'],
                ];
            }
            $tcaConfig['items'] = $tcaItems;
        }

        if (!empty($tableColumn['default'])) {
            $tcaConfig['default'] = $tableColumn['default'];
        }

        if ('selectSingle' !== $tableColumn['select_render_type']) {
            if (!empty($tableColumn['select_minitems'])) {
                $tcaConfig['minitems'] = $tableColumn['select_minitems'];
            }
            if (!empty($tableColumn['select_maxitems'])) {
                $tcaConfig['maxitems'] = $tableColumn['select_maxitems'];
            }
        }

        if ('foreign_table' === $tableColumn['select_type'] && !empty($tableColumn['foreign_table'])) {
            $tcaConfig['foreign_table'] = $tableColumn['foreign_table'];

            if (!empty($tableColumn['foreign_table_where'])) {
                $tcaConfig['foreign_table_where'] = $tableColumn['foreign_table_where'];
            }
        }

        if ('selectSingle' === $tableColumn['select_render_type'] && 'manual' === $tableColumn['select_type'] && $tableColumn['use_radio']) {
            unset($tcaConfig['renderType']);
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        $mappingMode = $tableColumn['extbase_mapping_mode'];
        if ('manual' === $tableColumn['select_type'] && 'models' === $tableColumn['extbase_mapping_mode']) {
            $mappingMode = 'arrays';
        }

        $isMultiple = 'selectSingle' !== $tableColumn['select_render_type'];
        if ($isMultiple && 1 === $tableColumn['select_maxitems']) {
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
