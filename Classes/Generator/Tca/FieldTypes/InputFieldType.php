<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class InputFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-text';

    public function getGroup(): string
    {
        return 'input';
    }

    public function getTypePalettes(): array
    {
        return [
            'input_min_max' => 'is_required,min,max',
            'input_default_placeholder' => 'default,placeholder',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        size,
        --palette--;;input_default_placeholder,
        --div--;Field evaluation,
            is_nullable,
            --palette--;;input_min_max,
            eval,
            is_in
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'min' => [
                'exclude' => false,
                'label' => 'Minimum amount of chars required',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
            ],
            'max' => [
                'exclude' => false,
                'label' => 'Maximum amount of chars allowed',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
            ],
            'size' => [
                'exclude' => false,
                'label' => 'Size',
                'config' => [
                    'type' => 'number',
                    'default' => 30,
                    'range' => [
                        'lower' => 10,
                        'upper' => 50,
                    ],
                    'slider' => [
                        'step' => 1,
                    ],
                ],
            ],

            'default' => [
                'exclude' => false,
                'label' => 'Default value',
                'config' => [
                    'type' => 'input',
                    'default' => '',
                ],
            ],
            'placeholder' => [
                'exclude' => false,
                'label' => 'Placeholder',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                ],
                'displayCond' => [
                    'OR' => [
                        'FIELD:type:=:input',
                        'FIELD:text_type:IN:normal,rte',
                    ],
                ],
            ],
            'is_nullable' => [
                'exclude' => false,
                'label' => 'Is nullable',
                'description' => 'If enabled, a checkbox is displayed right to the input field to set the value to NULL.',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
                ],
                'onChange' => 'reload',
            ],
            'is_required' => [
                'exclude' => false,
                'label' => 'Is required',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
                ],
                'displayCond' => [
                    'OR' => [
                        'AND' => [
                            'FIELD:type:IN:input,link,email,color',
                            'FIELD:is_nullable:=:0',
                        ],
                        'FIELD:type:=:number',
                        'OR' => [
                            'AND' => [
                                'FIELD:type:=:category',
                                'FIELD:cat_rel:=:oneToOne',
                            ],
                        ],
                    ],
                ],
            ],
            'eval' => [
                'exclude' => false,
                'label' => 'Eval',
                'description' => 'Defines field validation rules.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'items' => [
                        [
                            'label' => 'trim: The value in the field will have white spaces around it trimmed away',
                            'value' => 'trim',
                        ],
                        [
                            'label' => 'alpha: Allows only letter characters (a-z, A-Z)',
                            'value' => 'alpha',
                        ],
                        [
                            'label' => 'alphanum: Same as "alpha" but allows also numbers (0-9)',
                            'value' => 'alphanum',
                        ],
                        [
                            'label' => 'alphanum_x: Same as "alphanum" but allows also "_" and "-" chars',
                            'value' => 'alphanum_x',
                        ],
                        [
                            'label' => 'domainname: Allows a domain name such as example.org',
                            'value' => 'domainname',
                        ],
                        [
                            'label' => 'is_in: Will filter out any character, which is not found in the property "is_in"',
                            'value' => 'is_in',
                        ],
                        [
                            'label' => 'lower: Converts the string to lowercase',
                            'value' => 'lower',
                        ],
                        [
                            'label' => 'md5: Will convert the input value to its md5-hash',
                            'value' => 'md5',
                        ],
                        [
                            'label' => 'nospace: Removes all occurrences of space characters',
                            'value' => 'nospace',
                        ],
                        [
                            'label' => 'num: Allows only number characters (0-9) in the field',
                            'value' => 'num',
                        ],
                        [
                            'label' => 'unique: Requires the field to be unique for the whole table',
                            'value' => 'unique',
                        ],
                        [
                            'label' => 'uniqueInPid: Requires the field to be unique for the current PID',
                            'value' => 'uniqueInPid',
                        ],
                        [
                            'label' => 'upper: Converts the string to uppercase',
                            'value' => 'upper',
                        ],
                        [
                            'label' => 'year: Evaluates the input to any number',
                            'value' => 'year',
                        ],

                        // TODO Add user defined evaluations (?) from $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tce']['formevals']
                    ],
                    'default' => '',
                ],
            ],
            'is_in' => [
                'exclude' => false,
                'label' => 'Is in',
                'config' => [
                    'type' => 'input',
                ],
                'displayCond' => 'FIELD:eval:IN:is_in',
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
        $typeColumns = $this->getTypeColumns();
        $tcaConfig = [];

        if (!empty($tableColumn['eval'])) {
            $tcaConfig['eval'] = $tableColumn['eval'];

            $eval = GeneralUtility::trimExplode(',', $tcaConfig['eval'], true);
            if (!empty($tableColumn['is_in']) && in_array('is_in', $eval, true)) {
                $tcaConfig['is_in'] = $tableColumn['is_in'];
            }
        }
        if (!empty($tableColumn['is_nullable'])) {
            $tcaConfig['nullable'] = true;
        } elseif (!empty($tableColumn['is_required'])) {
            $tcaConfig['required'] = true;
        }

        if (!empty($tableColumn['size']) && $tableColumn['size'] !== $typeColumns['size']['config']['default']) {
            $tcaConfig['size'] = $tableColumn['size'];
        }

        if (!empty($tableColumn['min'])) {
            $tcaConfig['min'] = $tableColumn['min'];
        }
        if (!empty($tableColumn['max'])) {
            $tcaConfig['max'] = $tableColumn['max'];
        }

        if (!empty($tableColumn['default'])) {
            $tcaConfig['default'] = $tableColumn['default'];
        }

        if (!empty($tableColumn['placeholder'])) {
            $tcaConfig['placeholder'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'placeholder');
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        return new PropertyValue(
            $name,
            'string',
            !empty($tableColumn['is_nullable']),
            !empty($tableColumn['is_nullable']) ? null : ($tableColumn['default'] ?? '')
        );
    }
}
