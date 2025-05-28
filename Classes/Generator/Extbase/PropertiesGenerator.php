<?php

namespace T3\Vici\Generator\Extbase;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\FieldTypes\FieldTypes;

class PropertiesGenerator extends AbstractPhpCodeGenerator
{
    protected function generatePhpCode(): string
    {
        $code = '';
        /** @var string[] $objectStorageProperties */
        $objectStorageProperties = [];

        foreach ($this->tableColumns as $tableColumn) {
            $fieldType = FieldTypes::from($tableColumn['type']);
            $instance = $fieldType->getInstance();
            if (!$instance || empty($tableColumn['name'])) {
                continue;
            }

            $property = $instance->buildExtbaseModelProperty($tableColumn);
            $code .= $this->generatePropertyCode($property);
            if ($property->isObjectStorage) {
                $objectStorageProperties[$property->propertyName] = $property->typeOrClass;
            }
        }

        if (!empty($objectStorageProperties)) {
            $initializeObjectMethodCode = '';
            foreach ($objectStorageProperties as $propertyName => $className) {
                $initializeObjectMethodCode = <<<PHP
                        \$this->$propertyName = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();

                    PHP;
            }
            $initializeObjectMethodCode = rtrim($initializeObjectMethodCode);
            $code .= <<<PHP

                    public function __construct()
                    {
                        \$this->initializeObject();
                    }

                    public function initializeObject(): void
                    {
                    $initializeObjectMethodCode
                    }
                PHP;
        }

        return $code;
    }

    private function generatePropertyCode(PropertyValue $property): string
    {
        $name = $property->propertyName;
        $typeHint = $property->typeOrClass;

        if ($property->nullable) {
            $typeHint = '?' . $typeHint;
        }

        $default = '';
        if (!$property->isObjectStorage && (null !== $property->defaultValue || $property->nullable)) {
            $default = ' = ' . var_export($property->defaultValue, true);
        }

        if ($property->isObjectStorage) {
            $className = $property->typeOrClass;

            return <<<PHP

                    /**
                     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<$className>
                     */
                    public \TYPO3\CMS\Extbase\Persistence\ObjectStorage \$$name;

                PHP;
        }

        return <<<PHP
                public $typeHint \$$name$default;

            PHP;
    }
}
