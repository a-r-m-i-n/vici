<?php

namespace T3\Vici\UserFunction\ItemsProcFunc;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

/**
 * Shows only available vici tables, respecting the access permissions of current user.
 */
readonly class AvailableViciTables
{
    private BackendUserAuthentication $backendUser;

    public function __construct(
        private ViciRepository $viciRepository,
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

            $pageRow = BackendUtility::getRecord('pages', $tableRow['pid']) ?? [];
            $pageShow = $this->backendUser->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW);
            $pageEditContents = $this->backendUser->doesUserHaveAccess($pageRow, Permission::CONTENT_EDIT);

            if ($pageShow && $pageEditContents) {
                $parameters['items'][] = [
                    'label' => !empty($tableRow['title']) ? $tableRow['title'] : ucfirst($tableRow['name']),
                    'value' => $tableRow['uid'],
                    'icon' => $tableRow['icon'],
                ];
            }
        }

        if (!empty($parameters['items'])) {
            usort($parameters['items'], static function ($a, $b) {
                return strcasecmp($a['label'], $b['label']);
            });
        }
    }
}
