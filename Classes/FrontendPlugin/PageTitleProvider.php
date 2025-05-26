<?php

namespace T3\Vici\FrontendPlugin;

use T3\Vici\Model\GenericViciModel;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;
use TYPO3\CMS\Core\PageTitle\RecordPageTitleProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class PageTitleProvider extends AbstractPageTitleProvider
{
    /**
     * @var array<string, mixed>|null
     */
    private static ?array $typoScriptSettings = null;

    public function __construct(private readonly ViciRepository $viciRepository)
    {
        $this->setRequest($GLOBALS['TYPO3_REQUEST']);
    }

    public function generate(GenericViciModel $record, FrontendPlugin $frontendPlugin): void
    {
        if (!$frontendPlugin->getPageTitleMode() || !$frontendPlugin->getPageTitleColumnUid()) {
            return;
        }

        $method = $frontendPlugin->getPageTitleMode() . 'Title';
        if (method_exists($this, $method)) {
            $pageTitleColumn = $this->viciRepository->findTableColumnByUid($frontendPlugin->getPageTitleColumnUid());
            if ($pageTitleColumn) {
                $title = $record->_record[$pageTitleColumn['name']] ?? null;
                if ($title) {
                    $this->$method($title);
                }
            }
        }
    }

    private function replaceTitle(string $title): void
    {
        $this->title = $title;
    }

    private function prependTitle(string $title): void
    {
        /** @var RecordPageTitleProvider $pageTitle */
        $pageTitle = GeneralUtility::makeInstance(RecordPageTitleProvider::class);
        $pageTitle->setRequest($this->request);
        $originalPageTitle = $pageTitle->getTitle();

        $settings = $this->getTypoScriptSettings();
        $titleWithWrap = $this->wrapTitle($title, $settings['prependWrap'] ?? '');

        $this->title = $titleWithWrap . $originalPageTitle;
    }

    private function appendTitle(string $title): void
    {
        /** @var RecordPageTitleProvider $pageTitle */
        $pageTitle = GeneralUtility::makeInstance(RecordPageTitleProvider::class);
        $pageTitle->setRequest($this->request);
        $originalPageTitle = $pageTitle->getTitle();

        $settings = $this->getTypoScriptSettings();
        $titleWithWrap = $this->wrapTitle($title, $settings['appendWrap'] ?? '');

        $this->title = $originalPageTitle . $titleWithWrap;
    }

    /**
     * @return array<string, mixed>
     */
    private function getTypoScriptSettings(): array
    {
        if (null === self::$typoScriptSettings) {
            $configManager = GeneralUtility::makeInstance(ConfigurationManager::class);
            $ts = $configManager->getConfiguration(ConfigurationManager::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
            self::$typoScriptSettings = $ts['config.']['pageTitleProviders.']['vici.'] ?? [];
        }

        return self::$typoScriptSettings;
    }

    private function wrapTitle(string $title, string $noTrimWrapSetting): string
    {
        /** @var ContentObjectRenderer $cObj */
        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);

        return $cObj->stdWrap($title, ['noTrimWrap' => $noTrimWrapSetting]) ?? $title;
    }
}
