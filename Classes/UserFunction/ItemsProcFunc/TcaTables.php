<?php

namespace T3\Vici\UserFunction\ItemsProcFunc;

class TcaTables
{
    private const EXCLUDED_TABLES = [
        'tx_vici_table',
        'tx_vici_table_column',
        'tx_vici_table_column_item',
    ];

    /**
     * @param array<string, mixed> $parameters
     */
    public function get(array &$parameters): void
    {
        $items = [];
        foreach (array_keys($GLOBALS['TCA']) as $tableName) {
            if (in_array($tableName, self::EXCLUDED_TABLES, true)) {
                continue;
            }
            $items[] = [
                'label' => $tableName,
                'value' => $tableName,
            ];
        }

        $parameters['items'] = $items;
    }
}
