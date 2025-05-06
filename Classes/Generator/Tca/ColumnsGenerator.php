<?php

namespace T3\Vici\Generator\Tca;

class ColumnsGenerator extends AbstractTcaGenerator
{
    protected function generatePhpCode(): string
    {
        $data = [];

        // TODO
        foreach ($this->tableColumns as $tableColumn) {
            $data[$tableColumn['name']] = [
                'exclude' => false,
                'label' => $tableColumn['title'],
                'config' => [],
            ];

            $data[$tableColumn['name']]['config']['type'] = 'input';
        }

        return var_export($data, true);
    }
}
