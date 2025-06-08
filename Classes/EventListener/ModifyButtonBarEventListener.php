<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\StaticValues;
use T3\Vici\Repository\ViciRepository;
use T3\Vici\Services\ViciTablePermissionService;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\Components\ButtonBar;
use TYPO3\CMS\Backend\Template\Components\ModifyButtonBarEvent;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(
    identifier: 'vici/modify-button-bar',
)]
readonly class ModifyButtonBarEventListener
{
    private ServerRequest $request;

    public function __construct(
        private StaticValues $staticValues,
        private ViciRepository $viciRepository,
        private UriBuilder $backendUriBuilder,
        private ViciTablePermissionService $permissionService,
    ) {
        $this->request = $GLOBALS['TYPO3_REQUEST'];
    }

    public function __invoke(ModifyButtonBarEvent $event): void
    {
        if (array_key_exists('edit', $this->request->getQueryParams())) {
            $tableName = array_key_first($this->request->getQueryParams()['edit']);
            if (str_starts_with($tableName, $this->staticValues->getTableNamePrefix())) {
                $name = substr($tableName, strlen($this->staticValues->getTableNamePrefix()));
                $tableRow = $this->viciRepository->findTableByName($name);
                if ($tableRow && $this->permissionService->checkCurrentBackendUserPermissions($tableRow, false)) {
                    $buttonHref = $this->backendUriBuilder->buildUriFromRoute('record_edit', [
                        'edit' => [ViciRepository::TABLENAME_TABLE => [$tableRow['uid'] => 'edit']],
                        'returnUrl' => (string)$this->request->getUri(),
                    ]);

                    /** @var IconFactory $iconFactory */
                    $iconFactory = GeneralUtility::makeInstance(IconFactory::class);

                    $button = $event->getButtonBar()->makeLinkButton();
                    $button->setIcon($iconFactory->getIcon('vici-extension-icon', IconSize::SMALL));
                    $button->setTitle('Edit VICI table');
                    $button->setShowLabelText(true);
                    $button->setHref($buttonHref);
                    $buttons = $event->getButtons();
                    $buttons[ButtonBar::BUTTON_POSITION_LEFT][] = [$button];
                    $event->setButtons($buttons);
                }
            }
        }
    }
}
