<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;

class EmailFieldType extends InputFieldType
{
    protected string $iconClass = 'actions-envelope';

    public function getGroup(): string
    {
        return 'input_more';
    }

    public function getTypePalettes(): array
    {
        return [];
    }

    protected string $typeConfiguration = <<<TXT
        size,
        placeholder,
        --div--;Field evaluation,
            is_nullable,
            is_required,
            eval_unique
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'eval_unique' => [
                'exclude' => false,
                'label' => 'Eval',
                'description' => 'Defines field validation rules.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => '',
                            'value' => '',
                        ],
                        [
                            'label' => 'unique: Requires the field to be unique for the whole table',
                            'value' => 'unique',
                        ],
                        [
                            'label' => 'uniqueInPid: Requires the field to be unique for the current PID',
                            'value' => 'uniqueInPid',
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
        $parentTypeColumns = parent::getTypeColumns();
        $tcaConfig = [];

        if (!empty($tableColumn['eval_unique'])) {
            $tcaConfig['eval'] = $tableColumn['eval_unique'];
        }

        if (!empty($tableColumn['is_nullable'])) {
            $tcaConfig['nullable'] = true;
        } elseif (!empty($tableColumn['is_required'])) {
            $tcaConfig['required'] = true;
        }

        if (!empty($tableColumn['size']) && $tableColumn['size'] !== $parentTypeColumns['size']['config']['default']) {
            $tcaConfig['size'] = $tableColumn['size'];
        }

        if (!empty($tableColumn['placeholder'])) {
            $tcaConfig['placeholder'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'placeholder');
        }

        return $tcaConfig;
    }
}
