<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\StaticValues;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Authentication\Event\AfterGroupsResolvedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(
    identifier: 'vici/after-groups-resolved',
)]
readonly class AfterGroupsResolvedEventListener
{
    public function __construct(
        private ViciRepository $viciRepository,
        private StaticValues $staticValues
    ) {
    }

    public function __invoke(AfterGroupsResolvedEvent $event): void
    {
        if ('be_groups' !== $event->getSourceDatabaseTable() || ($event->getUserData()['admin'] ?? false)) {
            return;
        }

        $hasAccess = false;
        $groups = $event->getGroups();
        foreach ($groups as $group) {
            $tablesSelect = GeneralUtility::trimExplode(',', $group['tables_select'], true);
            $tablesModify = GeneralUtility::trimExplode(',', $group['tables_modify'], true);
            if (in_array(ViciRepository::TABLENAME_TABLE, $tablesSelect) && in_array(ViciRepository::TABLENAME_TABLE, $tablesModify)) {
                $hasAccess = true;
                break;
            }
        }

        if ($hasAccess) {
            $viciTables = [];
            $nonExcludeFields = [];
            foreach ($this->viciRepository->findAllTables() as $tableRow) {
                $tableName = $this->staticValues->getFullTableName($tableRow['name']);
                $viciTables[] = $tableName;

                if (array_key_exists($tableName, $GLOBALS['TCA'])) {
                    foreach ($GLOBALS['TCA'][$tableName]['columns'] as $columnName => $columnConfig) {
                        if (array_key_exists('exclude', $columnConfig) && $columnConfig['exclude']) {
                            $nonExcludeFields[] = $tableName . ':' . $columnName;
                        }
                    }
                }
            }

            $virtualGroup = [
                'tables_select' => implode(',', $viciTables),
                'tables_modify' => implode(',', $viciTables),
                'non_exclude_fields' => implode(',', $nonExcludeFields),
                'category_perms' => [],
            ];

            $groups[] = $virtualGroup;
        }

        $event->setGroups($groups);
    }
}
