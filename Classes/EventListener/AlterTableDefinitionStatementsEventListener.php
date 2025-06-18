<?php

namespace T3\Vici\EventListener;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

#[AsEventListener(
    identifier: 'vici/alter-table-definition-statements',
)]
readonly class AlterTableDefinitionStatementsEventListener
{
    public function __construct(private ViciRepository $viciRepository, private ConnectionPool $connectionPool)
    {
    }

    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
        $connection = $this->connectionPool->getConnectionForTable(ViciRepository::TABLENAME_COLUMN);
        $schemaManager = $connection->createSchemaManager();
        if (!$schemaManager->tableExists(ViciRepository::TABLENAME_TABLE)
            || !$schemaManager->tableExists(ViciRepository::TABLENAME_COLUMN)
            || !$schemaManager->tableExists(ViciRepository::TABLENAME_ITEM)
        ) {
            return;
        }

        $sortby = $this->viciRepository->findInlineColumnsWithForeignSortby();
        foreach ($sortby as $tablename => $columns) {
            $columnsSql = [];
            foreach ($columns as $column) {
                $columnsSql[] = $column . " int(11) DEFAULT '0' NOT NULL,";
            }

            $columnsSql = rtrim(implode(PHP_EOL, $columnsSql), ',');
            $sql = <<<SQL
                CREATE TABLE $tablename
                (
                    $columnsSql
                );
                SQL;

            $event->addSqlData($sql);
        }
    }
}
