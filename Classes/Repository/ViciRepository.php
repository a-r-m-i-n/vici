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
    public const TABLENAME_ITEM = 'tx_vici_table_column_item';

    /**
     * @var array<int, array<string, mixed>> Key is uid of table, value is the table row
     */
    private static array $tableCache = [];
    /**
     * @var array<string, array<string, mixed>> Key is name of table, value is the table row
     */
    private static array $tableCacheByName = [];
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
    public function findAllTables(bool $includeHidden = false): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);
        if ($includeHidden) {
            $queryBuilder->getRestrictions()->removeByType(HiddenRestriction::class);
        }

        $tableRows = $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_TABLE)
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        // Do not return tableRows which PID is deleted or hidden
        $result = [];
        foreach ($tableRows as $tableRow) {
            if (0 === $tableRow['pid']) {
                $result[] = $tableRow;
                continue;
            }

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
            $queryBuilder->getRestrictions()->removeAll()->add(new DeletedRestriction());
            if (!$includeHidden) {
                $queryBuilder->getRestrictions()->add(new HiddenRestriction());
            }

            $pageRow = $queryBuilder
                ->select('*')
                ->from('pages')
                ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tableRow['pid'], Connection::PARAM_INT)))
                ->executeQuery()
                ->fetchAssociative()
            ;

            if ($pageRow) {
                $result[] = $tableRow;
            }
        }

        return $result;
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
     * @return array<string, mixed>|null Table row
     */
    public function findTableByName(string $tableName): ?array
    {
        if (array_key_exists($tableName, self::$tableCacheByName)) {
            return self::$tableCacheByName[$tableName];
        }

        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_TABLE);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_TABLE)
            ->where($queryBuilder->expr()->eq('name', $queryBuilder->createNamedParameter($tableName)))
        ;

        if ($row = $queryBuilder->executeQuery()->fetchAssociative()) {
            self::$tableCacheByName[$tableName] = $row;

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

    /**
     * @return array<string, mixed>|null Table row
     */
    public function findTableColumnByUid(int $tableColumnUid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_COLUMN);
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_COLUMN)
            ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($tableColumnUid, Connection::PARAM_INT)))
        ;

        if ($row = $queryBuilder->executeQuery()->fetchAssociative()) {
            return $row;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>> Key is the uid of the list item, value the list item row
     */
    public function findListItemsByColumnUid(
        int $tableColumnUid,
        string $parentColumnName = 'column_select_items',
        bool $includeHidden = false
    ): array {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_ITEM);
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        if (!$includeHidden) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        }

        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_ITEM)
            ->where($queryBuilder->expr()->eq($parentColumnName, $queryBuilder->createNamedParameter($tableColumnUid, Connection::PARAM_INT)))
            ->orderBy('sorting', 'ASC')
        ;

        $items = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $column) {
            $items[$column['uid']] = $column;
        }

        return $items;
    }

    /**
     * @return array<string, array<string, string[]>> Key is VICI table name, child array's key is the foreign_table with foreign_field as values
     */
    public function findInlineColumnsWith(string $fieldName): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_COLUMN);
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_COLUMN)
            ->where($queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter('inline')))
            ->andWhere($queryBuilder->expr()->neq('foreign_table', $queryBuilder->createNamedParameter('')))
            ->andWhere($queryBuilder->expr()->neq($fieldName, $queryBuilder->createNamedParameter('')))
        ;

        $relatedTables = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $columnRow) {
            $tableRow = $this->findTableByUid($columnRow['parent']);
            if (!isset($tableRow['name'])) {
                continue;
            }
            if (!array_key_exists($tableRow['name'], $relatedTables)) {
                $relatedTables[$tableRow['name']] = [];
            }
            if (!array_key_exists($columnRow['foreign_table'], $relatedTables[$tableRow['name']])) {
                $relatedTables[$tableRow['name']][$columnRow['foreign_table']] = [];
            }
            $relatedTables[$tableRow['name']][$columnRow['foreign_table']][] = $columnRow[$fieldName];
        }

        return $relatedTables;
    }

    /**
     * @return array<string, string[]>
     */
    public function findInlineColumnsWithForeignSortby(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME_COLUMN);
        $queryBuilder
            ->select('*')
            ->from(self::TABLENAME_COLUMN)
            ->where($queryBuilder->expr()->eq('type', $queryBuilder->createNamedParameter('inline')))
            ->andWhere($queryBuilder->expr()->neq('foreign_table', $queryBuilder->createNamedParameter('')))
            ->andWhere($queryBuilder->expr()->neq('foreign_field', $queryBuilder->createNamedParameter('')))
            ->andWhere($queryBuilder->expr()->neq('foreign_sortby', $queryBuilder->createNamedParameter('')))
        ;

        $relatedTables = [];
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $columnRow) {
            if (!array_key_exists($columnRow['foreign_table'], $relatedTables)) {
                $relatedTables[$columnRow['foreign_table']] = [];
            }
            $relatedTables[$columnRow['foreign_table']][] = $columnRow['foreign_sortby'];
        }

        return $relatedTables;
    }
}
