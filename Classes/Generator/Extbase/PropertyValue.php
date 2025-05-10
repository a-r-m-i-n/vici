<?php

namespace T3\Vici\Generator\Extbase;

readonly class PropertyValue
{
    public function __construct(
        public string $propertyName,
        public string $typeOrClass,
        public bool $nullable = false,
        public mixed $defaultValue = null,
        public bool $isObjectStorage = false,
    ) {
    }
}
