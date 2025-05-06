<?php

namespace T3\Vici\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ViciRepository
{
    private const TABLENAME_TABLE = 'tx_vici_table';
    private const TABLENAME_COLUMN = 'tx_vici_table_column';

    public function __construct(private readonly ConnectionPool $connectionPool)
    {
    }

    /**
     * @return array<string, mixed>|null Table row
     */
    public function findTableByUid(int $uid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_TABLE)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)))
        ;

        if ($row = $queryBuilder->executeQuery()->fetchAssociative()) {
            return $row;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>> Array of column rows (key is the uid)
     */
    public function findTableColumnsByTableUid(int $tableUid, bool $includeHidden = false): array
    {
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

        return $columns;
    }
}
