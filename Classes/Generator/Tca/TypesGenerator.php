<?php

namespace T3\Vici\Generator\Tca;

class TypesGenerator extends AbstractTcaGenerator
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
