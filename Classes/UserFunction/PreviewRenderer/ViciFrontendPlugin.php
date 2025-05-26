<?php

namespace T3\Vici\UserFunction\PreviewRenderer;

use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Backend\Preview\StandardContentPreviewRenderer;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ViciFrontendPlugin extends StandardContentPreviewRenderer
{
    public function __construct(
        private readonly ViciRepository $viciRepository,
        private readonly FrontendPluginRepository $frontendPluginRepository
    ) {
    }

    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        return '';
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        $frontendPlugin = $this->frontendPluginRepository->createFrontendPluginInstance($item->getRecord());
        $tableRow = $this->viciRepository->findTableByUid($frontendPlugin->getViciTableUid());
        if (!$tableRow) {
            return '';
        }
        $icon = $this->getIconFactory()->getIcon($tableRow['icon'], IconSize::SMALL);
        $tableTitle = !empty($tableRow['title']) ? $tableRow['title'] : ucfirst($tableRow['name']);

        $startingPoint = '';
        foreach (GeneralUtility::intExplode(',', $frontendPlugin->row['pages'], true) as $pagesUid) {
            $pageRow = BackendUtility::getRecord('pages', $pagesUid) ?? [];
            $pageIcon = $this->getIconFactory()->getIconForRecord('pages', $pageRow, IconSize::SMALL);
            $startingPoint .= '<div>' . $pageIcon . ' ' . $pageRow['title'] . '</div>';
        }
        $recursive = $frontendPlugin->row['recursive'];
        if (250 === $recursive) {
            $recursive = 'Infinite levels';
        } elseif (1 === $recursive) {
            $recursive = '1 level';
        } elseif (0 === $recursive) {
            $recursive = 'Only on selected pages';
        } else {
            $recursive .= ' levels';
        }

        if ($frontendPlugin->isTranslation()) {
            $previewContent = '';
        } else {
            $previewContent = <<<HTML
                    <tr>
                        <th class="align-top">Record type</th>
                        <td class="align-top">$icon $tableTitle</td>
                    </tr>
                HTML;
        }
        $previewContent .= <<<HTML
                <tr>
                    <th class="align-top">Get records from</th>
                    <td class="align-top">$startingPoint</td>
                </tr>
                <tr>
                    <th class="align-top">Recursion depth</th>
                    <td class="align-top">$recursive</td>
                </tr>
            HTML;

        if (!$frontendPlugin->isTranslation() && ($frontendPlugin->isPaginationEnabled() || $frontendPlugin->isDetailpageEnabled())) {
            $paginationIndicator = '';
            if ($frontendPlugin->isPaginationEnabled()) {
                $icon = $this->getIconFactory()->getIcon('actions-check', IconSize::SMALL);
                $paginationIndicator = '<div><span class="text-success">' . $icon . '</span> Pagination enabled</div>';
            }
            $detailpageIndicator = '';
            if ($frontendPlugin->isDetailpageEnabled()) {
                $icon = $this->getIconFactory()->getIcon('actions-check', IconSize::SMALL);
                $detailpageIndicator = '<div><span class="text-success">' . $icon . '</span> Detail page enabled</div>';
            }

            $previewContent .= <<<HTML
                    <tr>
                        <th class="align-top">Options</th>
                        <td class="align-top">
                            $paginationIndicator
                            $detailpageIndicator
                        </td>
                    </tr>
                HTML;

        }

        $previewContent = '<table class="table table-striped table-sm">' . $previewContent . '</table>';

        return $this->linkEditContent($previewContent, $item->getRecord());
    }
}
