<?php

namespace T3\Vici\Hook;

use T3\Vici\Generator\TcaManager;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class TcemainHook
{
    /** @var array<int, string> Key is UID of table, value the old table name */
    private array $renamedTables = [];

    public function __construct(private readonly TcaManager $tcaManager)
    {
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_postProcessFieldArray(string $status, string $table, string|int $id, array $fieldArray, DataHandler $dataHandler): void
    {
        if ('tx_vici_table' === $table && 'update' === $status && array_key_exists('name', $fieldArray)) {
            $originalRow = $dataHandler->recordInfo($table, (int)$id) ?? [];

            if (array_key_exists('name', $originalRow) && $originalRow['name'] !== $fieldArray['name']) {
                $this->renamedTables[(int)$id] = $originalRow['name'];
            }
        }
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_afterDatabaseOperations(string $status, string $table, string|int $id, array $fieldArray, DataHandler $dataHandler): void
    {
        $uid = $this->getUid($id, $table, $status, $dataHandler);
        if ('tx_vici_table' === $table) {
            if (!empty($this->renamedTables)) {
                foreach ($this->renamedTables as $uid => $oldName) {
                    $this->tcaManager->delete($oldName);
                }
                $this->renamedTables = [];
            }

            $this->tcaManager->generate($uid, $status);
        }
    }

    /**
     * @param array<string, mixed> $recordToDelete
     */
    public function processCmdmap_deleteAction(
        string $table,
        int $id,
        array $recordToDelete,
        bool &$recordWasDeleted,
        DataHandler $dataHandler
    ): void {
        if ('tx_vici_table' === $table) {
            $this->tcaManager->delete($id);
        }
    }

    private function getUid(int|string $id, string $table, string $status, DataHandler $dataHandler): int
    {
        $uid = $id;
        if ('new' === $status) {
            if (!($dataHandler->substNEWwithIDs[$id] ?? null)) {
                // postProcessFieldArray
                $uid = 0;
            } else {
                // afterDatabaseOperations
                $uid = $dataHandler->substNEWwithIDs[$id];
                if (isset($dataHandler->autoVersionIdMap[$table][$uid])) {
                    $uid = $dataHandler->autoVersionIdMap[$table][$uid];
                }
            }
        }

        return (int)$uid;
    }
}
