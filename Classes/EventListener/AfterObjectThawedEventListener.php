<?php

namespace T3\Vici\EventListener;

use Doctrine\DBAL\ParameterType;
use T3\Vici\Generator\Extbase\ModelClassNameResolver;
use T3\Vici\Generator\StaticValues;
use T3\Vici\Model\GenericViciModel;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Event\Persistence\AfterObjectThawedEvent;

#[AsEventListener(
    identifier: 'vici/after-object-thawed',
)]
readonly class AfterObjectThawedEventListener
{
    public function __construct(
        private ViciRepository $viciRepository,
        private StaticValues $staticValues,
        private ConnectionPool $connectionPool,
        private ModelClassNameResolver $classNameResolver,
    ) {
    }

    public function __invoke(AfterObjectThawedEvent $event): void
    {
        $object = $event->getObject();
        if ($object instanceof GenericViciModel) {
            $object->_record = $event->getRecord();

            // Set _tablename
            $classNameParts = GeneralUtility::trimExplode('\\', get_class($object), true);
            $name = array_pop($classNameParts);
            if (!$name) {
                return;
            }
            $object->_tablename = $this->staticValues->getFullTableName(GeneralUtility::camelCaseToLowerCaseUnderscored($name));

            // Process foreign_table values
            $tableRow = $this->viciRepository->findTableByName($name);
            if ($tableRow) {
                $tableColumns = $this->viciRepository->findTableColumnsByTableUid($tableRow['uid']);
                foreach ($tableColumns as $tableColumn) {
                    if ('raw' !== $tableColumn['extbase_mapping_mode']
                        && array_key_exists($tableColumn['name'], $object->_record)
                        && in_array($tableColumn['type'], ['select', 'group', 'inline'], true)
                    ) {
                        $rawValues = GeneralUtility::trimExplode(',', $object->_record[$tableColumn['name']] ?? '', true);
                        $propertyName = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);
                        $foreignTable = $tableColumn['foreign_table'] ?? $tableColumn['group_allowed'];
                        $foreignTableArray = GeneralUtility::trimExplode(',', $foreignTable ?? '', true);
                        if ('select' === $tableColumn['type'] && 'manual' === $tableColumn['select_type']) {
                            if (!$this->isMultiple($tableColumn)) {
                                $object->$propertyName = $object->_record[$tableColumn['name']] ?? '';
                            } else {
                                $object->$propertyName = $rawValues;
                            }
                        } elseif (!empty($foreignTable) && 1 === count($foreignTableArray)) {
                            if (('models' === $tableColumn['extbase_mapping_mode'])) {
                                if (!empty($tableColumn['extbase_model_class'])) {
                                    $fqcn = '\\' . ltrim($tableColumn['extbase_model_class'], '\\');
                                    if (class_exists($fqcn)) {
                                        continue;
                                    }
                                } else {
                                    // When "extbase_model_class" is empty (which stands for "auto")
                                    $fqcn = $this->classNameResolver->getExtbaseModelByTablename($foreignTable);
                                    if (!empty($fqcn) && class_exists($fqcn)) {
                                        continue;
                                    }
                                }
                            }

                            // extbase_mapping_mode = 'arrays' (also fallback if no Extbase models could get resolved)
                            $queryBuilder = $this->connectionPool->getQueryBuilderForTable($foreignTable);
                            $rows = [];
                            foreach ($rawValues as $rawValue) {
                                $row = $queryBuilder
                                    ->select('*')
                                    ->from($foreignTable)
                                    ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($rawValue, ParameterType::INTEGER)))
                                    ->executeQuery()
                                    ->fetchAssociative();
                                if ($row) {
                                    $rows[] = $row;
                                }
                            }

                            if (!$this->isMultiple($tableColumn)) {
                                // Only get first item
                                $rows = reset($rows) ?: [];
                            }

                            $object->$propertyName = $rows;
                        } elseif (count($foreignTableArray) > 1) {
                            // If type:group "allowed" contains more than one table
                            $rows = [];
                            foreach ($foreignTableArray as $allowedTable) {
                                $queryBuilder = $this->connectionPool->getQueryBuilderForTable($allowedTable);
                                foreach (array_filter($rawValues, fn ($item) => str_starts_with($item, $allowedTable . '_')) as $rawValue) {
                                    $uid = (int)substr($rawValue, strlen($allowedTable . '_'));
                                    $row = $queryBuilder
                                        ->select('*')
                                        ->from($allowedTable)
                                        ->where($queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, ParameterType::INTEGER)))
                                        ->executeQuery()
                                        ->fetchAssociative();
                                    if ($row) {
                                        $rows[$rawValue] = $row;
                                    }
                                }
                            }

                            if (!$this->isMultiple($tableColumn)) {
                                // Only get first item
                                $rows = reset($rows) ?: [];
                            }

                            $object->$propertyName = $rows;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $tableColumn
     */
    private function isMultiple(array $tableColumn): bool
    {
        $isMultiple = 'selectSingle' !== $tableColumn['select_render_type'];
        if (in_array($tableColumn['type'], ['group', 'inline'], true)) {
            $isMultiple = true;
        }
        if ($isMultiple && 1 === $tableColumn['select_maxitems']) {
            $isMultiple = false;
        }

        return $isMultiple;
    }
}
