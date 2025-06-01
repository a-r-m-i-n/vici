<?php

namespace T3\Vici\Generator\Extbase;

readonly class PropertyValue
{
    /**
     * @param string|string[] $typeOrClass
     */
    public function __construct(
        public string $propertyName,
        public string|array $typeOrClass,
        public bool $nullable = false,
        public mixed $defaultValue = null,
        public bool $isObjectStorage = false,
    ) {
    }
}
