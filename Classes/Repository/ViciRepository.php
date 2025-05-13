<?php

namespace T3\Vici\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ViciRepository
{
    public const TABLENAME_TABLE = 'tx_vici_table';
    public const TABLENAME_COLUMN = 'tx_vici_table_column';

    /**
     * @var array<int, array<string, mixed>> Key is uid of table, value is the table row
     */
    private static array $tableCache = [];
    /**
     * @var array<int, array<int, array<string, mixed>>> Key is uid of table, value is an array of table columns
     */
    private static array $tableColumnsCache = [];

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllTables(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);

        return $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_TABLE)
            ->executeQuery()
            ->fetchAllAssociative()
        ;
    }

    /**
     * @return array<string, mixed>|null Table row
     */
    public function findTableByUid(int $tableUid): ?array
    {
        if (array_key_exists($tableUid, self::$tableCache)) {
            return self::$tableCache[$tableUid];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tableUid, Connection::PARAM_INT)))
        ;

        if ($row = $queryBuilder->executeQuery()->fetchAssociative()) {
            self::$tableCache[$tableUid] = $row;

            return $row;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>> Array of column rows (key is the uid)
     */
    public function findTableColumnsByTableUid(int $tableUid, bool $includeHidden = false): array
    {
        if (array_key_exists($tableUid, self::$tableColumnsCache)) {
            return self::$tableColumnsCache[$tableUid];
        }
        // TODO add caching
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        if (!$includeHidden) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        }

        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_COLUMN)
            ->where($queryBuilder->expr()->eq('parent', $queryBuilder->createNamedParameter($tableUid, Connection::PARAM_INT)))
            ->orderBy('sorting', 'ASC')
        ;

        $columns = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $column) {
            $columns[$column['uid']] = $column;
        }

        self::$tableColumnsCache[$tableUid] = $columns;

        return $columns;
    }
}
