<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CheckFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-checkbox';

    public function getLabel(): string
    {
        return 'Checkbox';
    }

    public function getGroup(): string
    {
        return 'input';
    }

    /**
     * @return array{before: string[], after: string[]}
     */
    public function getOrdering(): array
    {
        return [
            'before' => [],
            'after' => ['text'],
        ];
    }

    public function getTypePalettes(): array
    {
        return [
            'check_general' => 'check_rendertype,check_default',
            'check_label' => 'check_label,label_checked,label_unchecked',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;check_general,
        invert_state,
        --palette--;;check_label
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'check_rendertype' => [
                'exclude' => false,
                'label' => 'Render type',
                'description' => 'Defines how the check field is rendered.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Default',
                            'value' => 'default',
                        ],
                        [
                            'label' => 'Toggle',
                            'value' => 'checkboxToggle',
                        ],
                        [
                            'label' => 'Labeled toggle',
                            'value' => 'checkboxLabeledToggle',
                        ],
                    ],
                    'default' => 'default',
                    'minitems' => 1,
                    'maxitems' => 1,
                ],
                'onChange' => 'reload',
            ],
            'check_default' => [
                'exclude' => false,
                'label' => 'Enabled by default',
                'description' => 'If enabled, the checkbox will be enabled by default.',
                'config' => [
                    'type' => 'check',
                ],
            ],
            'check_label' => [
                'exclude' => false,
                'label' => 'Label',
                'description' => 'Optional label for the checkbox.',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                ],
            ],
            'invert_state' => [
                'exclude' => false,
                'label' => 'Invert state display',
                'description' => 'If enabled, the checkbox will be checked if disabled.',
                'config' => [
                    'type' => 'check',
                ],
                'displayCond' => 'FIELD:check_rendertype:IN:checkboxToggle,checkboxLabeledToggle',
            ],
            'label_checked' => [
                'exclude' => false,
                'label' => 'Label checked',
                'description' => 'Optional label when the checkbox is checked.',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                ],
                'displayCond' => 'FIELD:check_rendertype:=:checkboxLabeledToggle',
            ],
            'label_unchecked' => [
                'exclude' => false,
                'label' => 'Label unchecked',
                'description' => 'Optional label when the checkbox is unchecked.',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                ],
                'displayCond' => 'FIELD:check_rendertype:=:checkboxLabeledToggle',
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

        $item = [];
        if (!empty($tableColumn['check_label'])) {
            $item['label'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'check_label');
        }

        if ('default' !== $tableColumn['check_rendertype']) {
            $tcaConfig['renderType'] = $tableColumn['check_rendertype'];
            if (!empty($tableColumn['invert_state'])) {
                $item['invertStateDisplay'] = true;
            }

            if ('checkboxLabeledToggle' === $tableColumn['check_rendertype']) {
                if (!empty($tableColumn['label_checked'])) {
                    $item['labelChecked'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'label_checked');
                }
                if (!empty($tableColumn['label_unchecked'])) {
                    $item['labelUnchecked'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'label_unchecked');
                }
            }
        }

        if (!empty($item)) {
            $tcaConfig['items'] = [$item];
        }

        if ($tableColumn['check_default']) {
            $tcaConfig['default'] = 1;
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        return new PropertyValue($name, 'bool', false, (bool)$tableColumn['default']);
    }
}
