<?php

namespace T3\Vici\Generator\Tca;

abstract class AbstractTcaGenerator
{
    /**
     * @param array<string, mixed>             $table
     * @param array<int, array<string, mixed>> $tableColumns
     */
    public function __construct(protected array $table, protected array $tableColumns)
    {
    }

    abstract protected function generatePhpCode(): string;

    public function __toString(): string
    {
        return $this->generatePhpCode();
    }
}
