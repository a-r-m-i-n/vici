<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TextFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-textarea';

    public function getTypePalettes(): array
    {
        return [
            'text_type_options' => 'text_type,rte_preset,code_format',
            'text_min_max' => 'is_required,text_min,text_max',
            'text_cols_rows' => 'rows,cols',
            'text_appearance' => 'fixed_font,enable_tab',
            'text_default_placeholder' => 'text_default,placeholder',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;text_type_options,
        --palette--;;text_default_placeholder,
        --palette--;;text_cols_rows,
        --palette--;;text_appearance,
        --div--;Field evaluation,
        --palette--;;text_min_max,
        eval_trim,
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'text_type' => [
                'exclude' => false,
                'label' => 'Text type',
                'description' => 'Defines the type of the textarea field.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Regular textarea',
                            'value' => 'normal',
                        ],
                        [
                            'label' => 'Rich Text Editor (RTE)',
                            'value' => 'rte',
                        ],
                        [
                            'label' => 'Code Editor',
                            'value' => 'code',
                        ],
                        [
                            'label' => 'Text table',
                            'value' => 'table',
                        ],
                    ],
                    'default' => 'normal',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],

            'cols' => [
                'exclude' => false,
                'label' => 'Amount of columns (cols)',
                'description' => 'For full-width use value 0.',
                'config' => [
                    'type' => 'number',
                    'default' => 0,
                    'range' => [
                        'lower' => 0,
                        'upper' => 50,
                    ],
                    'slider' => [
                        'step' => 1,
                    ],
                ],
                'displayCond' => 'FIELD:text_type:IN:normal',
            ],
            'rows' => [
                'exclude' => false,
                'label' => 'Amount of rows (rows)',
                'description' => 'Note: Height of textarea fields, get increased automatically, based on given contents.',
                'config' => [
                    'type' => 'number',
                    'default' => 5,
                    'range' => [
                        'lower' => 0,
                        'upper' => 20,
                    ],
                    'slider' => [
                        'step' => 1,
                    ],
                ],
                'displayCond' => 'FIELD:text_type:IN:normal,code',
            ],

            'text_min' => [
                'exclude' => false,
                'label' => 'Minimum amount of chars required',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
                'displayCond' => 'FIELD:text_type:IN:normal',
            ],
            'text_max' => [
                'exclude' => false,
                'label' => 'Maximum amount of chars allowed',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
                'displayCond' => 'FIELD:text_type:IN:normal,table',
            ],

            'fixed_font' => [
                'exclude' => false,
                'label' => 'Use fixed font',
                'description' => 'Enables a fixed-width font (monospace) for the text field. This is useful when using code.',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
                ],
                'displayCond' => 'FIELD:text_type:IN:normal',
            ],
            'enable_tab' => [
                'exclude' => false,
                'label' => 'Enable tabulator',
                'description' => 'Enabling this allows to use tabs in a text field. This works well together with fixed-width fonts (monospace).',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
                ],
                'displayCond' => 'FIELD:text_type:IN:normal',
            ],

            'text_default' => [
                'exclude' => false,
                'label' => 'Default value',
                'config' => [
                    'type' => 'text',
                    'default' => '',
                ],
            ],
            'eval_trim' => [
                'exclude' => false,
                'label' => 'Enable trim',
                'description' => 'If enabled, trailing whitespaces get trimmed.',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
                ],
                'displayCond' => 'FIELD:text_type:IN:normal',
            ],
            'rte_preset' => [
                'exclude' => false,
                'label' => 'RTE preset',
                'description' => 'Choose the preset for this richt text editor field (e.g. minimal, default, full)',
                'config' => [
                    'type' => 'input',
                    'isRequired' => true,
                    'default' => 'default',
                ],
                'displayCond' => 'FIELD:text_type:IN:rte',
            ],
            'code_format' => [
                'exclude' => false,
                'label' => 'Code format',
                'description' => 'Defines which type of code should be displayed (used for syntax highlighting).',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => '',
                            'value' => 'none',
                        ],
                        [
                            'label' => 'HTML',
                            'value' => 'html',
                        ],
                        [
                            'label' => 'CSS',
                            'value' => 'css',
                        ],
                        [
                            'label' => 'JavaScript',
                            'value' => 'javascript',
                        ],
                        [
                            'label' => 'PHP',
                            'value' => 'php',
                        ],
                        [
                            'label' => 'TypoScript',
                            'value' => 'typoscript',
                        ],
                        [
                            'label' => 'XML',
                            'value' => 'xml',
                        ],
                    ],
                    'default' => 'none',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'displayCond' => 'FIELD:text_type:IN:code',
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

        if (!empty($tableColumn['text_default'])) {
            $tcaConfig['default'] = $tableColumn['text_default'];
        }

        if (!empty($tableColumn['is_required'])) {
            $tcaConfig['required'] = true;
        }

        if (!empty($tableColumn['cols'])) {
            $tcaConfig['cols'] = $tableColumn['cols'];
        }
        if (!empty($tableColumn['rows'])) {
            $tcaConfig['rows'] = $tableColumn['rows'];
        }

        if ('normal' === $tableColumn['text_type']) {
            if (!empty($tableColumn['text_min'])) {
                $tcaConfig['min'] = $tableColumn['text_min'];
            }
            if (!empty($tableColumn['text_max'])) {
                $tcaConfig['max'] = $tableColumn['text_max'];
            }
            if (!empty($tableColumn['eval_trim'])) {
                $tcaConfig['eval'] = 'trim';
            }
            if (!empty($tableColumn['fixed_font'])) {
                $tcaConfig['fixedFont'] = true;
            }
            if (!empty($tableColumn['enable_tab'])) {
                $tcaConfig['enableTabulator'] = true;
            }
            if (!empty($tableColumn['placeholder'])) {
                $tcaConfig['placeholder'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'placeholder');
            }
        } elseif ('rte' === $tableColumn['text_type']) {
            $tcaConfig['enableRichtext'] = true;
            $tcaConfig['richtextConfiguration'] = empty($tableColumn['rte_preset']) ? 'default' : $tableColumn['rte_preset'];
            if (!empty($tableColumn['placeholder'])) {
                $tcaConfig['placeholder'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'placeholder');
            }
        } elseif ('code' === $tableColumn['text_type']) {
            $tcaConfig['renderType'] = 'codeEditor';
            if (!empty($tableColumn['code_format']) && 'none' !== $tableColumn['code_format']) {
                $tcaConfig['format'] = $tableColumn['code_format'];
            }
        } elseif ('table' === $tableColumn['text_type']) {
            $tcaConfig['renderType'] = 'textTable';
            if (!empty($tableColumn['text_max'])) {
                $tcaConfig['max'] = $tableColumn['text_max'];
            }
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        return new PropertyValue($name, 'string', false, $tableColumn['default'] ?? '');
    }
}
