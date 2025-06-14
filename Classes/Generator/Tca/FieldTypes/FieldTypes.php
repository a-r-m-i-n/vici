<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\UserFunction\TcaFieldValidator\LeadingLetterValidator;
use T3\Vici\UserFunction\TcaFieldValidator\ReservedTcaColumnsValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

enum FieldTypes: string
{
    case INPUT = 'input';
    case TEXT = 'text';
    case SELECT = 'select';
    case GROUP = 'group';

    case CATEGORY = 'category';
    case CHECK = 'check';
    case COLOR = 'color';
    case DATETIME = 'datetime';
    case EMAIL = 'email';
    case FILE = 'file';
    case FLEX = 'flex';
    case FOLDER = 'folder';
    case IMAGEMANIPULATION = 'imageManipulation';
    case INLINE = 'inline';
    case JSON = 'json';
    case LANGUAGE = 'language';
    case LINK = 'link';
    case NONE = 'none';
    case NUMBER = 'number';
    case PASSTHROUGH = 'passthrough';
    case PASSWORD = 'password';
    case RADIO = 'radio';
    case SLUG = 'slug';
    case USER = 'user';
    case UUID = 'uuid';

    public function getInstance(): ?AbstractFieldType
    {
        $namespace = 'T3\\Vici\\Generator\\Tca\\FieldTypes\\';
        $className = $namespace . ucfirst($this->value) . 'FieldType';
        if (class_exists($className)) {
            /** @var AbstractFieldType $instance */
            $instance = GeneralUtility::makeInstance($className);

            return $instance;
        }

        return null;
    }

    /**
     * @return AbstractFieldType[] Array key is the type as string (e.g. 'input' or 'text')
     */
    public static function list(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $fieldTypeInstance = $case->getInstance();
            if ($fieldTypeInstance) {
                $list[$case->value] = $fieldTypeInstance;
            }
        }

        return $list;
    }

    /**
     * @return array<string, string>
     */
    public static function listTypeiconClasses(): array
    {
        $typeiconClasses = [
            'default' => 'form-hidden',
        ];
        foreach (self::list() as $fieldType) {
            $typeiconClasses[$fieldType->getType()] = $fieldType->getIconClass();
        }

        return $typeiconClasses;
    }

    /**
     * @return array<string, array{showitem: string}>
     */
    public static function listTypePalettes(): array
    {
        $palettes = [
            // Common palettes (used for all field types)
            'general_header' => ['showitem' => 'type,excluded,l10n_mode_exclude,--linebreak--,name,title'],
        ];

        foreach (self::list() as $fieldType) {
            foreach ($fieldType->getTypePalettes() as $paletteName => $paletteConfig) {
                if (array_key_exists($paletteName, $palettes)) {
                    throw new \UnexpectedValueException('Palette with name "' . $paletteName . '" already existing!');
                }
                $palettes[$paletteName] = ['showitem' => $paletteConfig];
            }
        }

        return $palettes;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function listTypeColumns(): array
    {
        $columns = [
            // Common columns (used for all field types)
            'name' => [
                'exclude' => false,
                'label' => 'Column name',
                'description' => 'Allowed characters are: lowercase letters (a-z), numbers (0-9) and underscores (_)',
                'config' => [
                    'type' => 'input',
                    'size' => 30,
                    'required' => true,
                    'min' => 3,
                    'max' => 48,
                    'eval' => 'trim,is_in,' . LeadingLetterValidator::class . ',' . ReservedTcaColumnsValidator::class,
                    'is_in' => 'abcdefghijklmnopqrstuvwxyz01234567890_',
                    'placeholder' => 'new_column_name',
                ],
            ],
            'type' => [
                'exclude' => 0,
                'label' => 'Type',
                'description' => 'Defines the TCA type of the new column',
                'onChange' => 'reload',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => self::listTypeSelectItems(),
                    'itemGroups' => self::listTypeItemGroups(),
                    'default' => 'input',
                ],
            ],
            'title' => [
                'exclude' => false,
                'label' => 'Title',
                'description' => 'Title of field, being displayed in backend',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                    'required' => true,
                ],
            ],
            'description' => [
                'exclude' => false,
                'label' => 'Description',
                'description' => 'Optional description of this field.',
                'config' => [
                    'type' => 'user',
                    'renderType' => 'viciTranslatableInput',
                ],
            ],
            'excluded' => [
                'exclude' => false,
                'label' => 'Exclude',
                'description' => 'If enabled, non-admin backend users won\'t see this field until it is allowed in the backend user group settings',
                'config' => [
                    'type' => 'check',
                ],
            ],
            'l10n_mode_exclude' => [
                'exclude' => false,
                'label' => 'Translatable',
                'description' => 'Defines if this field is translatable and how it behaves in translated records',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'items' => [
                        [
                            'label' => 'Yes',
                            'value' => 0,
                        ],
                        [
                            'label' => 'No, hide the field',
                            'value' => 1,
                        ],
                        [
                            'label' => 'No, but show the default contents as read-only field',
                            'value' => 2,
                        ],
                    ],
                    'default' => 0,
                ],
                'displayCond' => 'USER:T3\\Vici\\UserFunction\\DisplayCondition\\ParentTableIsTranslatable->check',
            ],
            'parent' => [
                'exclude' => false,
                'label' => 'Parent table',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectSingle',
                    'foreign_table' => 'tx_vici_table',
                    'size' => 1,
                    'minitems' => 0,
                    'maxitems' => 1,
                    'default' => 0,
                ],
            ],
            'additional_config' => [
                'exclude' => true,
                'label' => 'Additional configuration (as JSON)',
                'config' => [
                    'type' => 'json',
                    'default' => '',
                ],
            ],
        ];
        foreach (self::list() as $fieldType) {
            foreach ($fieldType->getTypeColumns() as $columnName => $columnConfig) {
                if (array_key_exists($columnName, $columns)) {
                    throw new \UnexpectedValueException('Column with name "' . $columnName . '" already existing!');
                }
                $columns[$columnName] = $columnConfig;
            }
        }

        return $columns;
    }

    /**
     * @return array<int|string, array{showitem: string}>
     */
    public static function listTypeTypes(): array
    {
        $typeShowitems = [
            // Default type
            0 => ['showitem' => 'type'],
        ];

        foreach (self::list() as $fieldType) {
            $typeShowitems[$fieldType->getType()] = [
                'showitem' => $fieldType->getTypeConfiguration(),
            ];
        }

        return $typeShowitems;
    }

    /**
     * @return array<int, array{label: string, value: string, icon: string, group: string}>
     */
    private static function listTypeSelectItems(): array
    {
        $items = [];
        foreach (self::list() as $fieldType) {
            $items[] = [
                'label' => $fieldType->getLabel(),
                'value' => $fieldType->getType(),
                'icon' => $fieldType->getIconClass(),
                'group' => $fieldType->getGroup(),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, string> Key is group key, value is the group label
     */
    private static function listTypeItemGroups(): array
    {
        return [
            'input' => 'Inputs',
            'select' => 'Selections & Relations',
            'media' => 'Media',
            'misc' => 'Miscellaneous',
        ];
    }
}
