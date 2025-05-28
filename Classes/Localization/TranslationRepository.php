<?php

namespace T3\Vici\Localization;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class TranslationRepository
{
    private const TABLE_NAME = 'tx_vici_translations';

    public function __construct(private ConnectionPool $connectionPool)
    {
    }

    public static function getIdentifier(string $tableName, int|string $uid, string $fieldName): string
    {
        return $tableName . '|' . $uid . '|' . $fieldName;
    }

    /**
     * @return array{tableName: string, uid: int|string, fieldName: string}
     */
    public static function parseIdentifier(string $identifier): array
    {
        $parts = GeneralUtility::trimExplode('|', $identifier, true);

        return [
            'tableName' => $parts[0],
            'uid' => $parts[1],
            'fieldName' => $parts[2],
        ];
    }

    public static function getLL(string $tableName, int $uid, string $fieldName): string
    {
        return 'LLL:EXT:vici/Resources/Private/Language/translate.vici:' . self::getIdentifier($tableName, $uid, $fieldName);
    }

    public function get(string $identifier, string $language, ?string $default = null): ?string
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->where($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter($identifier)))
            ->andWhere($queryBuilder->expr()->eq('language', $queryBuilder->createNamedParameter($language)))
            ->executeQuery()
            ->fetchAssociative()['translation'] ?? $default
        ;
    }

    public function save(string $identifier, string $language, string $value): void
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        $existingValue = $this->get($identifier, $language);
        if (null !== $existingValue) {
            if (!empty($value)) {
                if ($existingValue === $value) {
                    return;
                }

                // Update
                $queryBuilder
                    ->update(self::TABLE_NAME)
                    ->set('translation', $value)
                    ->where($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter($identifier)))
                    ->andWhere($queryBuilder->expr()->eq('language', $queryBuilder->createNamedParameter($language)))
                    ->executeStatement()
                ;
            } else {
                // Delete
                $queryBuilder
                    ->delete(self::TABLE_NAME)
                    ->where($queryBuilder->expr()->eq('identifier', $queryBuilder->createNamedParameter($identifier)))
                    ->andWhere($queryBuilder->expr()->eq('language', $queryBuilder->createNamedParameter($language)))
                    ->executeStatement()
                ;
            }
        } elseif (!empty($value)) {
            // Insert
            $queryBuilder
                ->insert(self::TABLE_NAME)
                ->values([
                    'identifier' => $identifier,
                    'language' => $language,
                    'translation' => $value,
                ])
                ->executeStatement()
            ;
        }
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function all(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLE_NAME);

        return $queryBuilder
            ->select('*')
            ->from(self::TABLE_NAME)
            ->executeQuery()
            ->fetchAllAssociative()
        ;
    }
}
