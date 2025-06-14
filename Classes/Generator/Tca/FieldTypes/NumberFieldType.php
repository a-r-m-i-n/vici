<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class NumberFieldType extends InputFieldType
{
    protected string $iconClass = 'content-widget-number';

    public function getGroup(): string
    {
        return 'input';
    }

    public function getTypePalettes(): array
    {
        return [
            'number_format_default' => 'number_format,default',
            'number_range' => 'lower,upper',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;number_format_default,
        --palette--;Range;number_range,
        slider,
        size,
        --div--;Field evaluation,
            is_required
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'number_format' => [
                'exclude' => false,
                'label' => 'Number format',
                'config' => [
                    'type' => 'radio',
                    'items' => [
                        [
                            'label' => 'Integer (e.g. 42)',
                            'value' => 'integer',
                        ],
                        [
                            'label' => 'Decimal (e.g. 13.37)',
                            'value' => 'decimal',
                        ],
                    ],
                    'default' => 'integer',
                ],
            ],

            'lower' => [
                'exclude' => false,
                'label' => 'Lower',
                'description' => 'Smallest number to be allowed. Keep empty for no limit.',
                'config' => [
                    'type' => 'input',
                    'size' => 15,
                    'default' => '',
                    'eval' => 'trim,is_in',
                    'is_in' => '0123456789.',
                ],
            ],
            'upper' => [
                'exclude' => false,
                'label' => 'Upper',
                'description' => 'Greatest number to be allowed. Keep empty for no limit.',
                'config' => [
                    'type' => 'input',
                    'size' => 15,
                    'default' => '',
                    'eval' => 'trim,is_in',
                    'is_in' => '0123456789.',
                ],
            ],
            'slider' => [
                'exclude' => false,
                'label' => 'Slider (steps)',
                'description' => 'If not empty (or zero), shows a slider and defines the step size the slider will use.',
                'config' => [
                    'type' => 'input',
                    'size' => 15,
                    'default' => '0',
                    'eval' => 'trim,is_in',
                    'is_in' => '0123456789.',
                    'placeholder' => '0 ',
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
        $parentTypeColumns = parent::getTypeColumns();
        $tcaConfig = [];

        $tcaConfig['format'] = !empty($tableColumn['number_format']) ? $tableColumn['number_format'] : 'integer';

        if ('' !== $tableColumn['lower'] || '' !== $tableColumn['upper']) {
            $tcaConfig['range'] = [];
            if ('' !== $tableColumn['lower']) {
                $tcaConfig['range']['lower'] = $tableColumn['lower'];
            }
            if ('' !== $tableColumn['upper']) {
                $tcaConfig['range']['upper'] = $tableColumn['upper'];
            }
        }

        if (!empty($tableColumn['slider'])) {
            $tcaConfig['slider'] = [
                'step' => $tableColumn['slider'],
            ];
        }

        if (!empty($tableColumn['is_required'])) {
            $tcaConfig['required'] = true;
        }

        if (!empty($tableColumn['size']) && $tableColumn['size'] !== $parentTypeColumns['size']['config']['default']) {
            $tcaConfig['size'] = $tableColumn['size'];
        }

        if (!empty($tableColumn['default'])) {
            $tcaConfig['default'] = $tableColumn['default'];
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        $default = (int)$tableColumn['default'];
        $type = 'int';
        if (empty($tableColumn['number_format']) || 'decimal' === $tableColumn['number_format']) {
            $default = (float)$tableColumn['default'];
            $type = 'float';
        }

        return new PropertyValue($name, $type, false, $default);
    }
}
