<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SlugFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-url';

    public function getGroup(): string
    {
        return 'input_more';
    }

    /**
     * @return array{before: string[], after: string[]}
     */
    public function getOrdering(): array
    {
        return [
            'before' => ['link'],
            'after' => [],
        ];
    }

    protected string $typeConfiguration = <<<TXT
        fields, field_separator, fallback_character, eval_slug
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'fields' => [
                'exclude' => false,
                'label' => 'Fields',
                'description' => 'Define fields to generate the slug. Multiple fields get separated by Field separator.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'foreign_table' => 'tx_vici_table_column',
                    'foreign_table_where' => 'AND tx_vici_table_column.parent=###REC_FIELD_parent### AND tx_vici_table_column.uid != ###THIS_UID### ORDER BY tx_vici_table_column.sorting',
                    'minitems' => 1,
                ],
            ],

            'field_separator' => [
                'exclude' => false,
                'label' => 'Field separator',
                'config' => [
                    'type' => 'input',
                    'size' => 5,
                    'default' => '/',
                    'eval' => 'trim',
                ],
            ],

            'fallback_character' => [
                'exclude' => false,
                'label' => 'Fallback character',
                'config' => [
                    'type' => 'input',
                    'size' => 5,
                    'default' => '-',
                    'eval' => 'trim',
                ],
            ],

            'eval_slug' => [
                'exclude' => false,
                'label' => 'Eval',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'No field evaluation',
                            'value' => '',
                        ],
                        [
                            'label' => 'Unique in PID',
                            'value' => 'uniqueInPid',
                        ],
                        [
                            'label' => 'Unique in site',
                            'value' => 'uniqueInSite',
                        ],
                        [
                            'label' => 'Unique (globally)',
                            'value' => 'unique',
                        ],
                    ],
                    'default' => '',
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

        if (!empty($tableColumn['fields'])) {
            $generatorOptions = [
                'fields' => [],
            ];

            $fieldUids = GeneralUtility::intExplode(',', $tableColumn['fields'], true);
            /** @var ViciRepository $viciRepository */
            $viciRepository = GeneralUtility::makeInstance(ViciRepository::class);
            foreach ($fieldUids as $fieldUid) {
                $columnRow = $viciRepository->findTableColumnByUid($fieldUid);
                if ($columnRow) {
                    $generatorOptions['fields'][] = $columnRow['name'];
                }
            }

            if (!empty($tableColumn['field_separator'])) {
                $generatorOptions['fieldSeparator'] = $tableColumn['field_separator'];
            }

            $tcaConfig['generatorOptions'] = $generatorOptions;
        }

        if (!empty($tableColumn['fallback_character'])) {
            $tcaConfig['fallbackCharacter'] = $tableColumn['fallback_character'];
        }

        if (!empty($tableColumn['eval_slug'])) {
            $tcaConfig['eval'] = $tableColumn['eval_slug'];
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        return new PropertyValue($name, 'string', false, '');
    }
}
