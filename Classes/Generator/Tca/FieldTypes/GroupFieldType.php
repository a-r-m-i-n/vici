<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\ModelClassNameResolver;
use T3\Vici\Generator\Extbase\PropertyValue;
use T3\Vici\UserFunction\ItemsProcFunc\TcaTables;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GroupFieldType extends AbstractFieldType
{
    protected string $iconClass = 'content-listgroup';

    public function getTypePalettes(): array
    {
        return [
            'group_default_min_max_items' => 'default,select_minitems,select_maxitems',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        group_allowed,
        --palette--;;group_default_min_max_items,
        --div--;Field control,field_control,
        --div--;Extbase,extbase_mapping_mode,extbase_model_class
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'group_allowed' => [
                'exclude' => false,
                'label' => 'Allowed table(s)',
                'description' => 'Select one ore more tables, the user should choose an items from. Note: Extbase Mapping does not work, when choosing more than one table.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectMultipleSideBySide',
                    'itemsProcFunc' => TcaTables::class . '->get',
                    'minitems' => 1,
                ],
            ],

            'field_control' => [
                'exclude' => false,
                'label' => 'Field control',
                'description' => 'Defines which field control options should be available.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectCheckBox',
                    'items' => [
                        [
                            'label' => 'Element browser',
                            'value' => 'elementBrowser',
                        ],
                        [
                            'label' => 'Insert clipboard',
                            'value' => 'insertClipboard',
                        ],
                        [
                            'label' => 'Edit popup',
                            'value' => 'editPopup',
                        ],
                        [
                            'label' => 'Add record',
                            'value' => 'addRecord',
                        ],
                        [
                            'label' => 'List module',
                            'value' => 'listModule',
                        ],
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
        $tcaConfig = [];

        $tcaConfig['allowed'] = $tableColumn['group_allowed'];
        $allowed = GeneralUtility::trimExplode(',', $tableColumn['group_allowed'], true);
        if (1 === count($allowed)) {
            $tcaConfig['foreign_table'] = $tableColumn['group_allowed'];
        }

        if (!empty($tableColumn['select_minitems'])) {
            $tcaConfig['minitems'] = $tableColumn['select_minitems'];
        }
        if (!empty($tableColumn['select_maxitems'])) {
            $tcaConfig['maxitems'] = $tableColumn['select_maxitems'];
        }

        if (!empty($tableColumn['field_control'])) {
            $fieldControlValues = GeneralUtility::trimExplode(',', $tableColumn['field_control'], true);
            $tcaFieldControl = [];
            if (in_array('elementBrowser', $fieldControlValues, true)) {
                $tcaFieldControl['elementBrowser'] = ['disabled' => false];
            }
            if (in_array('insertClipboard', $fieldControlValues, true)) {
                $tcaFieldControl['insertClipboard'] = ['disabled' => false];
            }
            if (in_array('editPopup', $fieldControlValues, true)) {
                $tcaFieldControl['editPopup'] = ['disabled' => false];
            }
            if (in_array('addRecord', $fieldControlValues, true)) {
                $tcaFieldControl['addRecord'] = ['disabled' => false];
            }
            if (in_array('listModule', $fieldControlValues, true)) {
                $tcaFieldControl['listModule'] = ['disabled' => false];
            }
            if (!empty($tcaFieldControl)) {
                $tcaConfig['fieldControl'] = $tcaFieldControl;
            }
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);
        $allowed = GeneralUtility::trimExplode(',', $tableColumn['group_allowed'], true);

        $mappingMode = $tableColumn['extbase_mapping_mode'];
        if ('models' === $mappingMode && count($allowed) > 1) {
            $mappingMode = 'arrays';
        }

        $isMultiple = true;
        if (1 === $tableColumn['select_maxitems']) {
            $isMultiple = false;
        }

        if ('models' === $mappingMode) {
            if (!empty($tableColumn['extbase_model_class'])) {
                $fqcn = '\\' . ltrim($tableColumn['extbase_model_class'], '\\');
                if (class_exists($fqcn)) {
                    return new PropertyValue($name, $fqcn, false, null, $isMultiple);
                }
            }

            /** @var ModelClassNameResolver $resolver */
            $resolver = GeneralUtility::makeInstance(ModelClassNameResolver::class);
            $fqcn = $resolver->getExtbaseModelByTablename($tableColumn['group_allowed']);
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
