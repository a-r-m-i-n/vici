<?php

namespace T3\Vici\EventListener;

use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use T3\Vici\Generator\StaticValues;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\TypoScript\IncludeTree\Event\AfterTemplatesHaveBeenDeterminedEvent;

#[AsEventListener(
    identifier: 'vici/after-templates-determined',
)]
readonly class AfterTemplatesHaveBeenDeterminedEventListener
{
    public function __construct(
        private PackageManager $packageManager,
        private FrontendPluginRepository $frontendPluginRepository,
        private ViciRepository $viciRepository,
        private StaticValues $staticValues,
    ) {
    }

    /**
     * Adds dynamically sys_template rows with additional TypoScript to register XML Sitemap
     * configuration, for EXT:vici frontend plugins with detail page enabled.
     */
    public function __invoke(AfterTemplatesHaveBeenDeterminedEvent $event): void
    {
        // Check if EXT:seo is active
        if (!$this->packageManager->isPackageActive('seo')) {
            return;
        }

        $rootPageId = $event->getSite()?->getRootPageId();
        if (!$rootPageId) {
            return;
        }

        $templateRows = $event->getTemplateRows();
        $firstTemplateRow = reset($templateRows);

        $newTemplateRows = [];
        foreach ($this->frontendPluginRepository->findAll() as $frontendPlugin) {
            if ($frontendPlugin->isInRootline($rootPageId) && $frontendPlugin->isDetailpageEnabled() && $frontendPlugin->isXmlSitemapEnabled() && !empty($frontendPlugin->getXmlSitemapIdentifier())) {
                $newTemplateRow = array_fill_keys(array_keys($firstTemplateRow), null);
                $newTemplateRow['title'] = 'Dynamically added by EXT:vici for frontend plugin with uid ' . $frontendPlugin->getUid() . ' [pid=' . $frontendPlugin->getPid() . ']';
                $newTemplateRow['uid'] = 0;
                $newTemplateRow['pid'] = 0;
                $newTemplateRow['root'] = 0;
                $newTemplateRow['clear'] = 0;

                $viciTable = $this->viciRepository->findTableByUid($frontendPlugin->getViciTableUid());
                if (!$viciTable) {
                    continue;
                }

                $sitemapIdentifier = $frontendPlugin->getXmlSitemapIdentifier();
                $tableName = $this->staticValues->getFullTableName($viciTable['name']);
                $lastModifiedField = !empty($viciTable['enable_column_timestamps']) ? 'tstamp' : '';
                $pid = $frontendPlugin->row['pages'] ?? 0;
                $recursive = $frontendPlugin->row['recursive'] ?? 0;
                $pageId = $frontendPlugin->getPid();

                $frontendPluginUid = $frontendPlugin->getUid();
                $frontendPluginPid = $frontendPlugin->getPid();

                $newTemplateRow['config'] = <<<TYPOSCRIPT
                    # Generated TypoScript for EXT:vici frontend plugin with uid $frontendPluginUid [pid=$frontendPluginPid]
                    plugin.tx_seo {
                        config {
                            xmlSitemap {
                                sitemaps {
                                    $sitemapIdentifier {
                                        provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
                                        config {
                                            table = $tableName
                                            lastModifiedField = $lastModifiedField
                                            pid = $pid
                                            recursive = $recursive
                                            url {
                                                pageId = $pageId
                                                fieldToParameterMap {
                                                    uid = tx_vici_frontend[uid]
                                                }
                                                additionalGetParameters {
                                                    tx_vici_frontend.controller = Frontend
                                                    tx_vici_frontend.action = show
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    TYPOSCRIPT;

                // Check for translated plugin instances
                $translations = $this->frontendPluginRepository->findTranslations($frontendPlugin);
                if (!empty($translations)) {
                    foreach ($translations as $translation) {
                        // If pages and recursive is identically, no additional typoscript required
                        if ($translation['pages'] === $frontendPlugin->row['pages'] && $translation['recursive'] === $frontendPlugin->row['recursive']) {
                            continue;
                        }

                        $language = $event->getSite()?->getLanguageById($translation['sys_language_uid']);
                        if (!$language) {
                            continue;
                        }

                        $additionalTypoScript = [
                            '# Additional TypoScript for translated frontend plugin with uid ' . $translation['uid'] . ' [pid=' . $translation['pid'] . ']',
                            '[siteLanguage("languageId") == ' . $language->getLanguageId() . ']',
                        ];
                        if ($translation['pages'] !== $frontendPlugin->row['pages']) {
                            $additionalTypoScript[] = '    plugin.tx_seo.config.xmlSitemap.sitemaps.' . $sitemapIdentifier . '.config.pid = ' . $translation['pages'];
                        }
                        if ($translation['recursive'] !== $frontendPlugin->row['recursive']) {
                            $additionalTypoScript[] = '    plugin.tx_seo.config.xmlSitemap.sitemaps.' . $sitemapIdentifier . '.config.recursive = ' . $translation['recursive'];
                        }
                        $additionalTypoScript[] = '[end]';

                        $newTemplateRow['config'] .= PHP_EOL . PHP_EOL . implode(PHP_EOL, $additionalTypoScript);
                    }
                }

                $newTemplateRows[] = $newTemplateRow;
            }
        }

        $templateRows = array_merge($templateRows, $newTemplateRows);

        $event->setTemplateRows($templateRows);
    }
}
