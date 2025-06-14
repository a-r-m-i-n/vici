<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;

class ColorFieldType extends InputFieldType
{
    protected string $iconClass = 'actions-brush';

    public function getLabel(): string
    {
        return 'Color picker';
    }

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
            'before' => [],
            'after' => ['email'],
        ];
    }

    public function getTypePalettes(): array
    {
        return [];
    }

    protected string $typeConfiguration = <<<TXT
        opacity,
        size,
        placeholder,
        --div--;Field evaluation,
            is_nullable,
            is_required,
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'opacity' => [
                'exclude' => false,
                'label' => 'Opacity',
                'description' => 'If enabled, editors can select not only a color but also adjust its opacity.',
                'config' => [
                    'type' => 'check',
                    'default' => 0,
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

        if (!empty($tableColumn['opacity'])) {
            $tcaConfig['opacity'] = true;
        }

        return $tcaConfig;
    }
}
