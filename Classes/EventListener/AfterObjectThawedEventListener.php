<?php

namespace T3\Vici\EventListener;

use Doctrine\DBAL\ParameterType;
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
                        $rawValues = GeneralUtility::trimExplode(',', $object->_record[$tableColumn['name']], true);
                        $propertyName = GeneralUtility::underscoredToLowerCamelCase($tableColumn['name']);
                        if ('manual' === $tableColumn['select_type']) {
                            $object->$propertyName = $rawValues;
                        } elseif (!empty($tableColumn['foreign_table'])) {
                            $foreignTable = $tableColumn['foreign_table'];
                            if (('models' === $tableColumn['extbase_mapping_mode']) && !empty($tableColumn['extbase_model_class'])) {
                                $fqcn = '\\' . ltrim($tableColumn['extbase_model_class'], '\\');
                                if (class_exists($fqcn)) {
                                    continue;
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

                            $isMultiple = 'selectSingle' !== $tableColumn['select_render_type'];
                            if ($isMultiple && 1 === $tableColumn['select_maxitems']) {
                                $isMultiple = false;
                            }

                            if (!$isMultiple) {
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
}
