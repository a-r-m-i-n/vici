<?php

namespace T3\Vici\UserFunction\ItemsProcFunc;

use T3\Vici\Generator\StaticValues;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Shows only available VICI tables, respecting the access permissions of current user.
 */
readonly class AvailableViciTables
{
    private BackendUserAuthentication $backendUser;

    public function __construct(
        private ViciRepository $viciRepository,
        private StaticValues $staticValues,
    ) {
        $this->backendUser = $GLOBALS['BE_USER'];
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function get(array &$parameters): void
    {
        foreach ($this->viciRepository->findAllTables() as $tableRow) {
            $columns = $this->viciRepository->findTableColumnsByTableUid($tableRow['uid']);
            if (empty($columns)) {
                continue;
            }

            $tableName = $this->staticValues->getFullTableName($tableRow['name']);
            if (!$this->backendUser->isAdmin() && !$this->backendUser->check('tables_modify', $tableName)) {
                continue;
            }

            $parameters['items'][] = [
                'label' => !empty($tableRow['title']) ? $tableRow['title'] : ucfirst($tableRow['name']),
                'value' => $tableRow['uid'],
                'icon' => $tableRow['icon'],
            ];
        }

        if (!empty($parameters['items'])) {
            usort($parameters['items'], static function ($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            });
        }
    }
}
