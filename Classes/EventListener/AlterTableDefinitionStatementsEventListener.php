<?php

namespace T3\Vici\EventListener;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

#[AsEventListener(
    identifier: 'vici/alter-table-definition-statements',
)]
readonly class AlterTableDefinitionStatementsEventListener
{
    public function __construct(private ViciRepository $viciRepository)
    {

    }

    public function __invoke(AlterTableDefinitionStatementsEvent $event): void
    {
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
