<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatetimeFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-date-picker';

    public function getGroup(): string
    {
        return 'input_more';
    }

    public function getTypePalettes(): array
    {
        return [
            'date_types' => 'date_format,date_dbtype',
            'date_range' => 'date_lower,date_upper',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;date_types,
        --palette--;Date range;date_range
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'date_format' => [
                'exclude' => false,
                'label' => 'Date format',
                'description' => 'Defines the date format.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Date and time',
                            'value' => 'datetime',
                        ],
                        [
                            'label' => 'Date only',
                            'value' => 'date',
                        ],
                        [
                            'label' => 'Time only',
                            'value' => 'time',
                        ],
                        [
                            'label' => 'Time only, with seconds',
                            'value' => 'timesec',
                        ],
                    ],
                    'default' => 'datetime',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],
            'date_dbtype' => [
                'exclude' => false,
                'label' => 'Database type',
                'description' => 'Defines how the date is stored in database.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Auto (recommended)',
                            'value' => 'auto',
                        ],
                        [
                            'label' => 'Unix timestamp',
                            'value' => 'unixtime',
                        ],
                        [
                            'label' => 'Datetime',
                            'value' => 'datetime',
                        ],
                        [
                            'label' => 'Date',
                            'value' => 'date',
                        ],
                        [
                            'label' => 'Time',
                            'value' => 'time',
                        ],
                    ],
                    'default' => 'auto',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
            ],
            'date_lower' => [
                'exclude' => false,
                'label' => 'Lower',
                'description' => 'Defines the earliest date. Keep empty for no limit.',
                'config' => [
                    'type' => 'datetime',
                    'nullable' => true,
                    'format' => 'datetime',
                    'dbType' => 'datetime',
                ],
                'displayCond' => 'FIELD:date_format:IN:datetime,date',
            ],
            'date_upper' => [
                'exclude' => false,
                'label' => 'Upper',
                'description' => 'Defines the latest date. Keep empty for no limit.',
                'config' => [
                    'type' => 'datetime',
                    'nullable' => true,
                    'format' => 'datetime',
                    'dbType' => 'datetime',
                ],
                'displayCond' => 'FIELD:date_format:IN:datetime,date',
            ],

            //            'default' => [
            //                'exclude' => false,
            //                'label' => 'Default value',
            //                'config' => [
            //                    'type' => 'input',
            //                    'default' => '',
            //                ],
            //            ],
            //            'placeholder' => [
            //                'exclude' => false,
            //                'label' => 'Placeholder',
            //                'config' => [
            //                    'type' => 'user',
            //                    'renderType' => 'viciTranslatableInput',
            //                ],
            //                'displayCond' => [
            //                    'OR' => [
            //                        'FIELD:type:=:input',
            //                        'FIELD:text_type:IN:normal,rte',
            //                    ],
            //                ],
            //            ],
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

        $tcaConfig['nullable'] = true;

        $tcaConfig['format'] = !empty($tableColumn['date_format']) ? $tableColumn['date_format'] : 'datetime';
        if ('unixtime' !== $tableColumn['date_dbtype']) {
            $tcaConfig['dbType'] = $tableColumn['date_dbtype'];

            if ('auto' === $tableColumn['date_dbtype']) {
                $tcaConfig['dbType'] = $tcaConfig['format'];
                if ('timesec' === $tcaConfig['format']) {
                    $tcaConfig['dbType'] = 'time';
                }
            }
        }

        if ((!empty($tableColumn['date_lower']) || !empty($tableColumn['date_upper'])) && in_array($tcaConfig['format'], ['datetime', 'date'], true)) {
            $range = [];
            if (!empty($tableColumn['date_lower'])) {
                $dateLower = new \DateTime($tableColumn['date_lower']);
                if ('date' === $tcaConfig['format']) {
                    $dateLower->setTime(0, 0);
                }
                $range['lower'] = $dateLower->getTimestamp();
            }
            if (!empty($tableColumn['date_upper'])) {
                $dateUpper = new \DateTime($tableColumn['date_upper']);
                if ('date' === $tcaConfig['format']) {
                    $dateUpper->setTime(23, 59, 59, 1000);
                }
                $range['upper'] = $dateUpper->getTimestamp();
            }
            if (!empty($range)) {
                $tcaConfig['range'] = $range;
            }
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        return new PropertyValue(
            $name,
            '\\' . \DateTimeImmutable::class,
            true
        );
    }
}
