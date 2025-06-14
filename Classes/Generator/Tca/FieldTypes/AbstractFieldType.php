<?php

namespace T3\Vici\Generator\Tca\FieldTypes;

use T3\Vici\Generator\Extbase\PropertyValue;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractFieldType
{
    protected string $iconClass = 'form-hidden';

    /**
     * @var string Used as type configuration for all field types (prepended)
     */
    private string $baseTypeConfigurationPrepended = <<<TXT
        --div--;General,
        --palette--;;general_header,
        TXT;

    /**
     * @var string Used as type configuration for all field types (appended)
     */
    private string $baseTypeConfigurationAppended = <<<TXT
        --div--;Additional config,
        description,additional_config
        TXT;

    /**
     * @var string Additional type configuration for field types
     */
    protected string $typeConfiguration = '';

    /**
     * @param array<string, mixed>|null $tableColumn
     */
    public function getType(?array $tableColumn = null): string
    {
        $identifier = preg_replace('/.*\\\\(.*?)FieldType$/', '$1', get_class($this));

        return lcfirst($identifier ?? '');
    }

    /**
     * @see FieldTypes::listTypeItemGroups
     */
    public function getGroup(): string
    {
        return 'misc';
    }

    public function getLabel(): string
    {
        return ucfirst($this->getType());
    }

    public function getIconClass(): string
    {
        return $this->iconClass;
    }

    final public function getTypeConfiguration(): string
    {
        $basePrepended = GeneralUtility::trimExplode(',', $this->baseTypeConfigurationPrepended, true);
        $type = GeneralUtility::trimExplode(',', $this->typeConfiguration, true);
        $baseAppended = GeneralUtility::trimExplode(',', $this->baseTypeConfigurationAppended, true);

        return implode(',', array_merge($basePrepended, $type, $baseAppended));
    }

    /**
     * @return array<string, string> Key is name of palettes, value the showitem value
     *
     * @see FieldTypes::listTypePalettes for common palettes configuration
     */
    public function getTypePalettes(): array
    {
        return [];
    }

    /**
     * @return array<string, array<string, mixed>> New columns for field type
     *
     * @see FieldTypes::listTypeColumns for common type columns
     */
    public function getTypeColumns(): array
    {
        return [];
    }

    /**
     * Returns TCA config.
     *
     * @param array<string, mixed> $tableColumn
     *
     * @return array<string, mixed>
     */
    abstract public function buildTcaConfig(array $tableColumn): array;

    /**
     * @param array<string, mixed> $tableColumn
     */
    abstract public function buildExtbaseModelProperty(array $tableColumn): PropertyValue;
}
