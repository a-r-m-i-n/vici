<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;

class FileFieldType extends AbstractFieldType
{
    protected string $iconClass = 'form-file-upload';

    public function getTypePalettes(): array
    {
        return [
            'input_minitems_maxitems' => 'minitems,maxitems',
        ];
    }

    protected string $typeConfiguration = <<<TXT
        --palette--;;input_minitems_maxitems,
        allowed,disallowed
        TXT;

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     */
    public function getTypeColumns(): array
    {
        return [
            'minitems' => [
                'exclude' => false,
                'label' => 'Minimum amount of files required',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
            ],
            'maxitems' => [
                'exclude' => false,
                'label' => 'Maximum amount of files allowed',
                'config' => [
                    'type' => 'number',
                    'size' => 15,
                    'default' => 0,
                ],
            ],
            'allowed' => [
                'exclude' => false,
                'label' => 'Allowed file extensions',
                'config' => [
                    'type' => 'input',
                    'placeholder' => 'e.g. jpg,jpeg,png',
                ],
            ],
            'disallowed' => [
                'exclude' => false,
                'label' => 'Allowed file extensions',
                'config' => [
                    'type' => 'input',
                    'placeholder' => 'e.g. jpg,jpeg,png',
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

        if (!empty($tableColumn['allowed'])) {
            $tcaConfig['allowed'] = implode(',', GeneralUtility::trimExplode(',', $tableColumn['allowed'], true));
        }
        if (!empty($tableColumn['disallowed'])) {
            $tcaConfig['disallowed'] = implode(',', GeneralUtility::trimExplode(',', $tableColumn['disallowed'], true));
        }

        if (!empty($tableColumn['minitems'])) {
            $tcaConfig['minitems'] = $tableColumn['minitems'];
        }
        if (!empty($tableColumn['maxitems'])) {
            $tcaConfig['maxitems'] = $tableColumn['maxitems'];
        }

        return $tcaConfig;
    }

    public function buildExtbaseModelProperty(array $tableColumn): PropertyValue
    {
        $name = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);

        $nullable = false;
        if (0 === $tableColumn['minitems']) {
            $nullable = true;
        }

        $isObjectStorage = false;
        if (empty($tableColumn['maxitems']) || $tableColumn['maxitems'] > 1) {
            $isObjectStorage = true;
        }

        return new PropertyValue($name, '\\' . FileReference::class, $nullable, null, $isObjectStorage);
    }
}
