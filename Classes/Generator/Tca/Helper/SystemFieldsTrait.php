<?php

namespace T3\Vici\Generator\Tca\Helper;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\PalettesGenerator;
use T3\Vici\Generator\Tca\TypesGenerator;

/**
 * @mixin PalettesGenerator
 * @mixin TypesGenerator
 *
 * @property array<string, mixed> $table
 * @property array<string, mixed> $tableColumns
 *
 * @see AbstractPhpCodeGenerator
 */
trait SystemFieldsTrait
{
    public function requiresAccessTab(): bool
    {
        return $this->table['enable_column_hidden']
            || $this->table['enable_column_start_end_time']
            || $this->table['enable_column_fegroup']
            || $this->table['enable_column_editlock']
        ;
    }

    public function requiresLanguageTab(): bool
    {
        return $this->table['enable_column_languages'];
    }
}
