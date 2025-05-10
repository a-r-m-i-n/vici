<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;

class TypesGenerator extends AbstractPhpCodeGenerator
{
    protected function generatePhpCode(): string
    {
        $data = [0 => ['showitem' => implode(',', $this->getTableColumnNames())]];

        // TODO Support own types

        return var_export($data, true);
    }

    /**
     * @return string[]
     */
    private function getTableColumnNames(): array
    {
        $names = [];
        foreach ($this->tableColumns as $tableColumn) {
            $names[] = $tableColumn['name'];
        }

        return $names;
    }
}
