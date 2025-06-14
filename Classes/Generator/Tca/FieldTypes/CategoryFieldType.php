<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\Category;

class CategoryFieldType extends AbstractFieldType
{
    protected string $iconClass = 'mimetypes-x-sys_category';

    public function getGroup(): string
    {
        return 'select';
    }

    /**
     * @return array{before: string[], after: string[]}
     */
    public function getOrdering(): array
    {
        return [
            'before' => [],
            'after' => ['inline', 'group'],
        ];
    }

    public function getTypePalettes(): array
    {
        return [
            'category_relationship' => 'cat_rel,is_required,select_minitems,select_maxitems',
            'category_appearance' => 'cat_header,cat_expand',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;category_relationship,
        --div--;Appearance,
            --palette--;;category_appearance,
            cat_start,
            cat_max_level,
            cat_non_select_levels,
            cat_size
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'cat_rel' => [
                'exclude' => false,
                'label' => 'Relationship',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Many to many (Default, using MM relations)',
                            'value' => 'manyToMany',
                        ],
                        [
                            'label' => 'One to many (using comma-separated uid list)',
                            'value' => 'oneToMany',
                        ],
                        [
                            'label' => 'One to one (just one category selectable)',
                            'value' => 'oneToOne',
                        ],
                    ],
                    'minitems' => 1,
                    'maxitems' => 1,
                    'default' => 'manyToMany',
                ],
                'onChange' => 'reload',
            ],
            'cat_size' => [
                'exclude' => false,
                'label' => 'Size',
                'description' => 'Number of items to be displayed. Default: 20',
                'config' => [
                    'type' => 'number',
                    'default' => 20,
                    'range' => [
                        'lower' => 8,
                        'upper' => 100,
                    ],
                    'slider' => [
                        'step' => 1,
                    ],
                ],
            ],
            'cat_start' => [
                'exclude' => false,
                'label' => 'Starting points',
                'description' => 'Comma-separated list of sys_category uids, being displayed on first level.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                ],
            ],
            'cat_header' => [
                'exclude' => false,
                'label' => 'Show header',
                'description' => 'Toggles header which allows to filter the categories and expand or collapse all nodes.',
                'config' => [
                    'type' => 'check',
                    'default' => 1,
                ],
            ],
            'cat_expand' => [
                'exclude' => false,
                'label' => 'Expand all',
                'description' => 'If enabled, show all nodes expanded.',
                'config' => [
                    'type' => 'check',
                    'default' => 1,
                ],
            ],
            'cat_max_level' => [
                'exclude' => false,
                'label' => 'Max levels',
                'description' => 'The maximal amount of levels to be rendered.',
                'config' => [
                    'type' => 'number',
                    'format' => 'integer',
                    'default' => 0,
                    'size' => 10,
                ],
            ],
            'cat_non_select_levels' => [
                'exclude' => false,
                'label' => 'Non-selectable levels',
                'description' => 'Comma-separated list of levels that will not be selectable.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim,is_in',
                    'is_in' => '0123456789,',
                    'size' => 20,
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

        $tcaConfig['relationship'] = $tableColumn['cat_rel'];
        if ('oneToOne' === $tableColumn['cat_rel']) {
            if (!empty($tableColumn['is_required'])) {
                $tcaConfig['minitems'] = 1;
            }
        } else {
            if (!empty($tableColumn['select_minitems'])) {
                $tcaConfig['minitems'] = $tableColumn['select_minitems'];
            }
            if (!empty($tableColumn['select_maxitems'])) {
                $tcaConfig['maxitems'] = $tableColumn['select_maxitems'];
            }
        }

        if (!empty($tableColumn['cat_size'])) {
            $tcaConfig['size'] = $tableColumn['cat_size'];
        }

        $treeConfig = [];
        if (!empty($tableColumn['cat_start'])) {
            $startingPoints = GeneralUtility::trimExplode(',', $tableColumn['cat_start'], true);
            $treeConfig['startingPoints'] = implode(',', $startingPoints);
        }
        $appearance = [];
        $appearance['showHeader'] = !empty($tableColumn['cat_header']);
        $appearance['expandAll'] = !empty($tableColumn['cat_expand']);
        if (!empty($tableColumn['cat_max_level'])) {
            $appearance['maxLevels'] = $tableColumn['cat_max_level'];
        }
        if ('' !== $tableColumn['cat_non_select_levels']) {
            $appearance['nonSelectableLevels'] = $tableColumn['cat_non_select_levels'];
        }
        $treeConfig['appearance'] = $appearance;
        $tcaConfig['treeConfig'] = $treeConfig;

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        $isMultiple = true;
        if ('oneToOne' === $tableColumn['cat_rel'] || 1 === $tableColumn['select_maxitems']) {
            $isMultiple = false;
        }

        return new PropertyValue($name, '\\' . Category::class, false, null, $isMultiple);
    }
}
