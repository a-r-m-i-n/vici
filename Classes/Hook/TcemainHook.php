<?php

namespace T3\Vici\Hook;

use T3\Vici\Generator\TcaManager;
use TYPO3\CMS\Core\DataHandling\DataHandler;

readonly class TcemainHook
{
    public function __construct(private TcaManager $tcaManager)
    {
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        string|int $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {

        $uid = $this->getUid($id, $table, $status, $dataHandler);
        if ('tx_vici_table' === $table) {
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
