<?php

namespace T3\Vici\Hook;

use T3\Vici\Generator\ViciManager;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;

class TcemainHook
{
    /** @var array<int, string> Key is UID of table, value the old table name */
    private array $renamedTables = [];

    public function __construct(private readonly ViciManager $viciManager)
    {
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_postProcessFieldArray(string $status, string $table, string|int $id, array $fieldArray, DataHandler $dataHandler): void
    {
        if (ViciRepository::TABLENAME_TABLE === $table && 'update' === $status && array_key_exists('name', $fieldArray)) {
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
        if (ViciRepository::TABLENAME_TABLE === $table) {
            if (!empty($this->renamedTables)) {
                foreach ($this->renamedTables as $uid => $oldName) {
                    $this->viciManager->delete($oldName);
                }
                $this->renamedTables = [];
            }

            $this->viciManager->generate($uid);
        }
    }

    public function processCmdmap_deleteAction(string $table, int $id): void
    {
        if (ViciRepository::TABLENAME_TABLE === $table) {
            $this->viciManager->delete($id);
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
