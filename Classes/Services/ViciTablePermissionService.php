<?php

namespace T3\Vici\Services;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

readonly class ViciTablePermissionService
{
    private BackendUserAuthentication $backendUser;

    public function __construct()
    {
        $this->backendUser = $GLOBALS['BE_USER'];
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    public function checkCurrentBackendUserPermissions(array $tableRow, bool $throwException = true): bool
    {
        if (!$this->backendUser->check('tables_modify', ViciRepository::TABLENAME_TABLE)
            || !$this->backendUser->check('tables_modify', ViciRepository::TABLENAME_COLUMN)
        ) {
            if ($throwException) {
                throw new \UnexpectedValueException('You are not allowed to edit VICI table and/or VICI table columns.');
            }

            return false;
        }

        if (0 === $tableRow['pid'] && !$this->backendUser->isAdmin()) {
            if ($throwException) {
                throw new \UnexpectedValueException('You are not allowed to edit VICI table with uid=' . $tableRow['uid'] . ' on root page!');
            }

            return false;
        }

        if (0 !== $tableRow['pid']) {
            $pageRow = BackendUtility::getRecord('pages', $tableRow['pid']);
            if ($pageRow) {
                $pageShow = $this->backendUser->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW);
                $pageEditContents = $this->backendUser->doesUserHaveAccess($pageRow, Permission::CONTENT_EDIT);
                if (!$pageShow || !$pageEditContents) {
                    if ($throwException) {
                        throw new \UnexpectedValueException('You are not allowed to edit VICI table with uid=' . $tableRow['uid'] . ' on pid=' . $pageRow['uid'] . '!');
                    }

                    return false;
                }
            }
        }

        return true;
    }
}
