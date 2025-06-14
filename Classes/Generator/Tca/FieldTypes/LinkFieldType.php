<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LinkFieldType extends InputFieldType
{
    protected string $iconClass = 'actions-link';

    public function getTypePalettes(): array
    {
        return [
            'link_nullable_required' => 'is_nullable,is_required',
            'link_default_placeholder' => 'default,placeholder',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        link_allowed_types,link_allowed_records,size,
        --palette--;;link_default_placeholder,
        --div--;Field evaluation,
            --palette--;;link_nullable_required,
        --div--;Appearance,
            link_disable_browser,
            link_disable_options,
            link_disable_options_mail,
            link_allowed_files,

        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'link_allowed_types' => [
                'exclude' => false,
                'label' => 'Allowed types',
                'description' => 'Defines the link types which are allowed to use',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectCheckBox',
                    'items' => [
                        [
                            'label' => 'Links to internal pages',
                            'value' => 'page',
                        ],
                        [
                            'label' => 'Links to external pages',
                            'value' => 'url',
                        ],
                        [
                            'label' => 'Links to a file',
                            'value' => 'file',
                        ],
                        [
                            'label' => 'Links to a folder',
                            'value' => 'folder',
                        ],
                        [
                            'label' => 'Creates an email link',
                            'value' => 'email',
                        ],
                        [
                            'label' => 'Creates a phone link',
                            'value' => 'telephone',
                        ],
                        [
                            'label' => 'Links to records',
                            'value' => 'record',
                        ],
                    ],
                ],
                'onChange' => 'reload',
            ],
            'link_allowed_records' => [
                'exclude' => false,
                'label' => 'Allowed records',
                'description' => 'By default all record link handlers are allowed to choose. Here you can limit the allowed link handlers, with a comma separated list.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                ],
                'displayCond' => 'FIELD:link_allowed_types:IN:record',
            ],
            'link_disable_browser' => [
                'exclude' => false,
                'label' => 'Disable link browser',
                'description' => 'When enabled, the user can not open the link browser to select item for link.',
                'config' => [
                    'type' => 'check',
                ],
                'onChange' => 'reload',
            ],
            'link_disable_options' => [
                'exclude' => false,
                'label' => 'Disable link options',
                'description' => 'Defines which link options are NOT available in link browser.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectCheckBox',
                    'items' => [
                        [
                            'label' => 'Target',
                            'value' => 'target',
                        ],
                        [
                            'label' => 'Title',
                            'value' => 'title',
                        ],
                        [
                            'label' => 'CSS class',
                            'value' => 'class',
                        ],
                        [
                            'label' => 'Additional link parameters',
                            'value' => 'params',
                        ],
                    ],
                ],
                'displayCond' => 'FIELD:link_disable_browser:=:0',
            ],
            'link_disable_options_mail' => [
                'exclude' => false,
                'label' => 'Disable mail options',
                'description' => 'Defines which mail options are NOT available in link browser.',
                'config' => [
                    'type' => 'select',
                    'renderType' => 'selectCheckBox',
                    'items' => [
                        [
                            'label' => 'Subject',
                            'value' => 'subject',
                        ],
                        [
                            'label' => 'Body',
                            'value' => 'body',
                        ],
                        [
                            'label' => 'CC',
                            'value' => 'cc',
                        ],
                        [
                            'label' => 'BCC',
                            'value' => 'bcc',
                        ],
                    ],
                ],
                'displayCond' => [
                    'AND' => [
                        'FIELD:link_disable_browser:=:0',
                        'FIELD:link_allowed_types:IN:email',
                    ],
                ],
            ],
            'link_allowed_files' => [
                'exclude' => false,
                'label' => 'Allowed file extensions',
                'description' => 'Comma separated list of file extensions, to be allowed to link. Keep empty for all file extensions.',
                'config' => [
                    'type' => 'input',
                    'eval' => 'trim',
                    'placeholder' => 'e.g. jpg,png,webp,svg',
                ],
                'displayCond' => [
                    'AND' => [
                        'FIELD:link_disable_browser:=:0',
                        'FIELD:link_allowed_types:IN:file',
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
        $parentTypeColumns = parent::getTypeColumns();
        $tcaConfig = [];

        $allowedTypes = GeneralUtility::trimExplode(',', $tableColumn['link_allowed_types'], true);
        if (!empty($tableColumn['link_allowed_records']) && in_array('record', $allowedTypes, true)) {
            $index = array_search('record', $allowedTypes);
            if (false !== $index) {
                unset($allowedTypes[$index]);
            }
            $allowedRecords = GeneralUtility::trimExplode(',', $tableColumn['link_allowed_records'], true);
            $allowedTypes = array_merge($allowedTypes, $allowedRecords);
        }
        if (!empty($allowedTypes)) {
            $tcaConfig['allowedTypes'] = $allowedTypes;
        }

        if (!empty($tableColumn['is_nullable'])) {
            $tcaConfig['nullable'] = true;
        } elseif (!empty($tableColumn['is_required'])) {
            $tcaConfig['required'] = true;
        }

        if (!empty($tableColumn['size']) && $tableColumn['size'] !== $parentTypeColumns['size']['config']['default']) {
            $tcaConfig['size'] = $tableColumn['size'];
        }

        if (!empty($tableColumn['default'])) {
            $tcaConfig['default'] = $tableColumn['default'];
        }

        if (!empty($tableColumn['placeholder'])) {
            $tcaConfig['placeholder'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'placeholder');
        }

        $appearance = [];
        if (!empty($tableColumn['link_disable_browser'])) {
            $appearance['enableBrowser'] = false;
        } else {
            if (!empty($tableColumn['link_allowed_files']) && in_array('file', $allowedTypes, true)) {
                $allowedFileExtensions = GeneralUtility::trimExplode(',', $tableColumn['link_allowed_files'], true);
                $appearance['allowedFileExtensions'] = $allowedFileExtensions;
            }

            $allOptions = [];
            $allowedOptions = [];
            if (in_array('email', $allowedTypes, true)) {
                $allMailOptions = ['subject', 'body', 'cc', 'bcc'];
                $allOptions = array_merge($allOptions, $allMailOptions);
                if (!empty($tableColumn['link_disable_options_mail'])) {
                    $disabledMailOptions = GeneralUtility::trimExplode(',', $tableColumn['link_disable_options_mail'], true);
                    $allMailOptions = array_diff($allMailOptions, $disabledMailOptions);
                }
                $allowedOptions = array_merge($allowedOptions, $allMailOptions);
            }

            $allLinkOptions = ['target', 'title', 'class', 'params'];
            $allOptions = array_merge($allOptions, $allLinkOptions);
            if (!empty($tableColumn['link_disable_options'])) {
                $disabledLinkOptions = GeneralUtility::trimExplode(',', $tableColumn['link_disable_options'], true);
                $allLinkOptions = array_diff($allLinkOptions, $disabledLinkOptions);
            }
            $allowedOptions = array_merge($allowedOptions, $allLinkOptions);

            if (count($allOptions) !== count($allowedOptions)) {
                $appearance['allowedOptions'] = $allowedOptions;
            }
        }

        if (!empty($appearance)) {
            $tcaConfig['appearance'] = $appearance;
        }

        return $tcaConfig;
    }
}
