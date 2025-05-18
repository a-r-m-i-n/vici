<?php

namespace T3\Vici\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use T3\Vici\Generator\ViciManager;
use T3\Vici\Repository\ViciRepository;
use T3\Vici\Services\DatabaseMigrationService;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

#[AsController]
class ViciModuleController extends ActionController
{
    private ModuleTemplateFactory $moduleTemplateFactory;
    private ModuleTemplate $moduleTemplate;
    private BackendUserAuthentication $backendUser;

    public function __construct(
        ModuleTemplateFactory $moduleTemplateFactory,
        private readonly UriBuilder $backendUriBuilder,
        private readonly IconFactory $iconFactory,
        private readonly ViciRepository $viciRepository,
        private readonly ViciManager $viciManager,
        private readonly DatabaseMigrationService $databaseMigrationService,
        private readonly DataHandler $dataHandler,
    ) {
        $this->moduleTemplateFactory = $moduleTemplateFactory;
        $this->backendUser = $GLOBALS['BE_USER'];
    }

    public function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $this->moduleTemplate->setTitle('EXT:vici');
    }

    public function indexAction(): ResponseInterface
    {
        if (!$this->backendUser->check('tables_modify', 'tx_vici_table')) {
            return $this->moduleTemplate->renderResponse('Backend/InsufficientPermissions');
        }

        // Module buttons
        $this->addModuleButtons();

        // Get and process table rows
        $tableRowsGroupedByPage = $this->getTableRowsGroupedByPage();

        // Apply TCA file status and database compare changes to grouped table rows
        $databaseCompareChanges = $this->databaseMigrationService->getRelatedStatements();
        foreach ($tableRowsGroupedByPage as $indexPages => $row) {
            foreach ($row['tableRows'] as $indexTableRows => $tableRow) {
                $isUpToDate = $this->viciManager->checkIfTableTcaIsUpToDate($tableRow);
                if (null === $isUpToDate) {
                    $tableRow['_tcaFileStatus'] = 'missing';
                } elseif (false === $isUpToDate) {
                    $tableRow['_tcaFileStatus'] = 'update';
                } else {
                    $tableRow = $this->applyDatabaseCompareChanges($tableRow, $databaseCompareChanges);
                    $tableRow['_tcaFileStatus'] = 'ok';
                }
                $tableRow['_tcaExisting'] = $this->viciManager->checkIfTableIsExistingInCachedTca($tableRow);
                $tableRowsGroupedByPage[$indexPages]['tableRows'][$indexTableRows] = $tableRow;
            }
        }

        $this->moduleTemplate->assign('tableRows', $tableRowsGroupedByPage);
        $this->moduleTemplate->assign('userIsAdmin', $this->backendUser->isAdmin());

        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    public function editAction(int $tableUid): ResponseInterface
    {
        $uri = $this->backendUriBuilder->buildUriFromRoute('record_edit', [
            'edit' => [ViciRepository::TABLENAME_TABLE => [$tableUid => 'edit']],
            'returnUrl' => $this->backendUriBuilder->buildUriFromRoute('tools_ViciModule'),
        ]);

        return $this->redirectToUri($uri);
    }

    public function clearAllCachesAction(): ResponseInterface
    {
        $this->dataHandler->start([], []);
        $this->dataHandler->clear_cacheCmd('all');

        $this->addFlashMessage('All caches has been flushed successfully.');

        return $this->redirect('index');
    }

    public function showDatabaseChangesAction(int $tableUid): ResponseInterface
    {
        $tableRow = $this->viciRepository->findTableByUid($tableUid);
        if (!$tableRow) {
            throw new \UnexpectedValueException('Vici table with uid ' . $tableUid . ' not found!');
        }

        $this->checkUserPermissions($tableRow);

        $databaseCompareChanges = $this->databaseMigrationService->getRelatedStatements();
        $tableRow = $this->applyDatabaseCompareChanges($tableRow, $databaseCompareChanges);

        $this->moduleTemplate->assign('tableRow', $tableRow);

        return $this->moduleTemplate->renderResponse('Backend/ShowDatabaseChanges');
    }

    /**
     * @param string[] $selectedHashes
     */
    public function applyDatabaseChangesAction(int $tableUid, array $selectedHashes): ResponseInterface
    {
        $tableRow = $this->viciRepository->findTableByUid($tableUid);
        if (!$tableRow) {
            throw new \UnexpectedValueException('Vici table with uid ' . $tableUid . ' not found!');
        }

        $this->checkUserPermissions($tableRow);

        $this->databaseMigrationService->migrate($selectedHashes);

        $this->addFlashMessage('Database schema successfully updated for vici table "tx_vici_custom_' . $tableRow['name'] . '"');

        return $this->redirect('index');
    }

    /**
     * @return array<int, array{pageRow: array<string, mixed>, tableRows: array<int, array<string, mixed>>}>
     */
    private function getTableRowsGroupedByPage(): array
    {
        $tableRows = $this->viciRepository->findAllTables(true);
        $result = [];
        foreach ($tableRows as $tableRow) {
            $pageUid = $tableRow['pid'];

            $pageRow = [];
            if (!array_key_exists($pageUid, $result)) {
                if ($pageUid) {
                    $pageRow = BackendUtility::getRecord('pages', $pageUid) ?? [];
                }

                if (0 === $pageUid && !$this->backendUser->isAdmin()) {
                    continue;
                }

                $result[$pageUid] = [
                    'pageRow' => $pageRow,
                    'tableRows' => [],
                ];
            }

            $pageShow = $this->backendUser->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW);
            $pageEditContents = $this->backendUser->doesUserHaveAccess($pageRow, Permission::CONTENT_EDIT);
            if ($pageShow && $pageEditContents) {
                $result[$pageUid]['tableRows'][] = $tableRow;
            }
        }

        foreach ($result as $pageUid => $row) {
            if (empty($row['tableRows'])) {
                unset($result[$pageUid]);
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $tableRow
     * @param array<string, mixed> $databaseCompareChanges
     *
     * @return array<string, mixed>
     */
    private function applyDatabaseCompareChanges(array $tableRow, array $databaseCompareChanges): array
    {
        if ($tableRow['hidden']) {
            return $tableRow;
        }

        $necessaryChanges = [];
        $optionalChanges = [];

        $necessary = $databaseCompareChanges['addColumn'] +
            $databaseCompareChanges['createTable'] +
            $databaseCompareChanges['changeTable'] +
            $databaseCompareChanges['changeIndex'] +
            $databaseCompareChanges['changeColumn'];

        foreach ($necessary as $hash => $change) {
            if ($change['tableUid'] === $tableRow['uid']) {
                $necessaryChanges[$hash] = $change;
            }
        }

        $tableRow['_necessaryDatabaseCompareChanges'] = $necessaryChanges;

        $optional = $databaseCompareChanges['changeTableToZzzDeleted'] +
            $databaseCompareChanges['changeColumnToZzzDeleted'] +
            $databaseCompareChanges['dropColumn'];

        foreach ($optional as $hash => $change) {
            if ($change['tableUid'] === $tableRow['uid']) {
                $optionalChanges[$hash] = $change;
            }
        }

        $tableRow['_optionalDatabaseCompareChanges'] = $optionalChanges;

        return $tableRow;
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    private function checkUserPermissions(array $tableRow): void
    {
        if (0 === $tableRow['pid'] && !$this->backendUser->isAdmin()) {
            throw new \UnexpectedValueException('You are not allowed to edit vici table with uid=' . $tableRow['uid'] . ' on root page!');
        }
        if (0 !== $tableRow['pid']) {
            $pageRow = BackendUtility::getRecord('pages', $tableRow['pid']);
            if ($pageRow) {
                $pageShow = $this->backendUser->doesUserHaveAccess($pageRow, Permission::PAGE_SHOW);
                $pageEditContents = $this->backendUser->doesUserHaveAccess($pageRow, Permission::CONTENT_EDIT);
                if (!$pageShow || !$pageEditContents) {
                    throw new \UnexpectedValueException('You are not allowed to edit vici table with uid=' . $tableRow['uid'] . ' on pid=' . $pageRow['uid'] . '!');
                }
            }
        }
    }

    private function addModuleButtons(): void
    {
        $shortcutButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeShortcutButton()
            ->setDisplayName('EXT:vici')
            ->setRouteIdentifier('tools_ViciModule')
        ;
        $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($shortcutButton);

        $refreshButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeLinkButton()
            ->setTitle('Refresh')
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('actions-refresh', IconSize::SMALL->value))
            ->setHref($this->backendUriBuilder->buildUriFromRoute('tools_ViciModule'))
        ;
        $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($refreshButton);

        $allowedToClearCache = false;
        $userTsConfig = $this->backendUser->getTSConfig();
        if (($userTsConfig['options.']['clearCache.']['all'] ?? false)
            || ($this->backendUser->isAdmin() && (bool)($userTsConfig['options.']['clearCache.']['all'] ?? true))
        ) {
            $allowedToClearCache = true;
        }
        $flushAllCachesButton = $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->makeLinkButton()
            ->setTitle('Flush all caches')
            ->setShowLabelText(true)
            ->setIcon($this->iconFactory->getIcon('apps-toolbar-menu-cache', IconSize::SMALL->value))
            ->setHref($this->uriBuilder->uriFor('clearAllCaches'))
            ->setDisabled(!$allowedToClearCache)
        ;
        $this->moduleTemplate->getDocHeaderComponent()->getButtonBar()->addButton($flushAllCachesButton);

        $this->moduleTemplate->assign('allowedToClearCaches', $allowedToClearCache);
    }

    protected function getFlashMessageQueue(?string $identifier = null): FlashMessageQueue
    {
        if (!$identifier) {
            $identifier = 'core.template.flashMessages';
        }

        return parent::getFlashMessageQueue($identifier);
    }
}
