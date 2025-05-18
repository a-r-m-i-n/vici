<?php

namespace T3\Vici\Services;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Database\Schema\SchemaMigrator;
use TYPO3\CMS\Core\Database\Schema\SqlReader;

readonly class DatabaseMigrationService
{
    public function __construct(
        private SqlReader $sqlReader,
        private SchemaMigrator $schemaMigrator,
        private ViciRepository $viciRepository,
    ) {
    }

    /**
     * @return array{
     *     addColumn: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     createTable: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     changeTable: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     changeTableToZzzDeleted: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     changeIndex: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     changeColumn: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     changeColumnToZzzDeleted: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     dropColumn: array<string, array{tableUid: int, tableName: string, sql: string}>,
     *     dropTable: array<string, array{tableName: string, sql: string}>
     * }
     */
    public function getRelatedStatements(): array
    {
        $sqlStatements = [];
        $sqlStatements[] = $this->sqlReader->getTablesDefinitionString();
        $sqlStatements = $this->sqlReader->getCreateTableStatementArray(implode(LF . LF, array_filter($sqlStatements)));
        $updateStatements = $this->schemaMigrator->getUpdateSuggestions($sqlStatements);
        $removeStatements = $this->schemaMigrator->getUpdateSuggestions($sqlStatements, true);
        $statements = array_merge_recursive(...array_values($updateStatements), ...array_values($removeStatements));

        $relatedStatements = [
            'addColumn' => [],
            'createTable' => [],
            'changeTable' => [],
            'changeTableToZzzDeleted' => [],

            'changeIndex' => [],
            'changeColumn' => [],
            'changeColumnToZzzDeleted' => [],

            'dropColumn' => [],
            'dropTable' => [],
        ];
        if (array_key_exists('add', $statements)) {
            foreach ($statements['add'] as $hash => $sql) {
                if (str_contains($sql, 'tx_vici_custom_')) {
                    preg_match('/tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? null;
                    if ($tableName) {
                        $tableRow = $this->viciRepository->findTableByName($tableName);
                        if ($tableRow) {
                            $relatedStatements['addColumn'][$hash] = [
                                'tableUid' => $tableRow['uid'],
                                'tableName' => $tableName,
                                'sql' => $sql,
                            ];
                        }
                    }

                }
            }
        }

        if (array_key_exists('create_table', $statements)) {
            foreach ($statements['create_table'] as $hash => $sql) {
                if (str_contains($sql, 'tx_vici_custom_')) {
                    preg_match('/tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? null;
                    if ($tableName) {
                        $tableRow = $this->viciRepository->findTableByName($tableName);
                        if ($tableRow) {
                            $relatedStatements['createTable'][$hash] = [
                                'tableUid' => $tableRow['uid'],
                                'tableName' => $tableName,
                                'sql' => $sql,
                            ];
                        }
                    }

                }
            }
        }

        if (array_key_exists('change_table', $statements)) {
            foreach ($statements['change_table'] as $hash => $sql) {
                if (str_contains($sql, 'tx_vici_custom_')) {
                    preg_match('/tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? null;
                    if ($tableName) {
                        $tableRow = $this->viciRepository->findTableByName($tableName);
                        if ($tableRow) {
                            if (str_contains($sql, 'zzz_deleted_')) {
                                $relatedStatements['changeTableToZzzDeleted'][$hash] = [
                                    'tableUid' => $tableRow['uid'],
                                    'tableName' => $tableName,
                                    'sql' => $sql,
                                ];
                            } else {
                                $relatedStatements['changeTable'][$hash] = [
                                    'tableUid' => $tableRow['uid'],
                                    'tableName' => $tableName,
                                    'sql' => $sql,
                                ];
                            }
                        }
                    }

                }
            }
        }

        if (array_key_exists('change', $statements)) {
            foreach ($statements['change'] as $hash => $sql) {
                if (str_contains($sql, 'tx_vici_custom_')) {
                    preg_match('/tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? null;
                    if ($tableName) {
                        $tableRow = $this->viciRepository->findTableByName($tableName);
                        if ($tableRow) {
                            if (str_starts_with($sql, 'CREATE INDEX') || str_starts_with($sql, 'DROP INDEX')) {
                                $relatedStatements['changeIndex'][$hash] = [
                                    'tableUid' => $tableRow['uid'],
                                    'tableName' => $tableName,
                                    'sql' => $sql,
                                ];
                            } elseif (str_contains($sql, 'zzz_deleted_')) {
                                $relatedStatements['changeColumnToZzzDeleted'][$hash] = [
                                    'tableUid' => $tableRow['uid'],
                                    'tableName' => $tableName,
                                    'sql' => $sql,
                                ];
                            } else {
                                $relatedStatements['changeColumn'][$hash] = [
                                    'tableUid' => $tableRow['uid'],
                                    'tableName' => $tableName,
                                    'sql' => $sql,
                                ];
                            }
                        }
                    }

                }
            }
        }

        if (array_key_exists('drop', $statements)) {
            foreach ($statements['drop'] as $hash => $sql) {
                if (str_contains($sql, 'tx_vici_custom_')) {
                    preg_match('/tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableName = $matches[1] ?? null;
                    if ($tableName) {
                        $tableRow = $this->viciRepository->findTableByName($tableName);
                        if ($tableRow) {
                            $relatedStatements['dropColumn'][$hash] = [
                                'tableUid' => $tableRow['uid'],
                                'tableName' => $tableName,
                                'sql' => $sql,
                            ];
                        }
                    }

                }
            }
        }
        if (array_key_exists('drop_table', $statements)) {
            foreach ($statements['drop_table'] as $hash => $sql) {
                if (str_contains($sql, 'zzz_deleted_tx_vici_custom_')) {
                    preg_match('/zzz_deleted_tx_vici_custom_(\w+)/', $sql, $matches);
                    $tableNameToDrop = $matches[1] ?? null;
                    if ($tableNameToDrop) {
                        $relatedStatements['dropTable'][$hash] = [
                            'tableName' => $tableNameToDrop,
                            'sql' => $sql,
                        ];
                    }

                }
            }
        }

        return $relatedStatements;
    }

    /**
     * @param string[] $selectedHashes
     */
    public function migrate(array $selectedHashes): void
    {
        $sqlStatements = [];
        $sqlStatements[] = $this->sqlReader->getTablesDefinitionString();
        $sqlStatements = $this->sqlReader->getCreateTableStatementArray(implode(LF . LF, array_filter($sqlStatements)));
        $updateStatements = $this->schemaMigrator->getUpdateSuggestions($sqlStatements);
        $updateStatements = array_merge_recursive(...array_values($updateStatements));
        $selectedStatements = [];
        foreach (['add', 'change', 'create_table', 'change_table'] as $action) {
            if (empty($updateStatements[$action])) {
                continue;
            }
            $statements = array_combine(array_keys($updateStatements[$action]), array_fill(0, count($updateStatements[$action]), true));
            $selectedStatements = array_merge(
                $selectedStatements,
                $statements
            );
        }

        $selectedHashes = array_fill_keys($selectedHashes, true);
        $selectedStatements = array_intersect_key($selectedStatements, $selectedHashes);
        $selectedStatements = array_combine(array_keys($selectedStatements), array_keys($selectedStatements));

        $result = $this->schemaMigrator->migrate($sqlStatements, $selectedStatements);

        if (!empty($result)) {
            throw new \RuntimeException('Errors occurred while updating database schema. ' . implode('; ', $result));
        }
    }
}
