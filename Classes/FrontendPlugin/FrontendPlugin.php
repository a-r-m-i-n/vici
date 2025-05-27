<?php

namespace T3\Vici\FrontendPlugin;

use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

readonly class FrontendPlugin
{
    /**
     * @param array<string, mixed> $row     The tt_content row
     * @param array<string, mixed> $options Parsed FlexForm configuration
     */
    public function __construct(public array $row, public array $options)
    {
    }

    public function getUid(): int
    {
        return $this->row['uid'];
    }

    public function getPid(): int
    {
        return $this->row['pid'];
    }

    public function isTranslation(): bool
    {
        return 0 !== $this->row['l18n_parent'];
    }

    public function getViciTableUid(): int
    {
        return $this->row['tx_vici_table'] ?? 0;
    }

    public function getViciTemplate(): string
    {
        return $this->row['tx_vici_template'] ?? '';
    }

    public function getViciDetailpageTemplate(): string
    {
        return $this->row['tx_vici_template_detail'] ?? '';
    }

    // Pagination options

    public function isPaginationEnabled(): bool
    {
        return $this->options['enablePagination'] ?? false;
    }

    /**
     * @return class-string<PaginationInterface>
     */
    public function getPaginationType(): string
    {
        if ('slidingWindow' === $this->options['paginationType']) {
            return SlidingWindowPagination::class;
        }

        return SimplePagination::class;
    }

    public function getPaginationItemsPerPage(): int
    {
        return $this->options['paginationItemsPerPage'] ?? 10;
    }

    public function getPaginationMaxLinks(): int
    {
        return $this->options['paginationSlidingWindowMaxLinks'] ?? 5;
    }

    public function getPaginationShowArrows(): bool
    {
        return $this->options['paginationShowPrevNextArrows'] ?? false;
    }

    // Detailpage options

    public function isDetailpageEnabled(): bool
    {
        return $this->options['enableDetailpage'] ?? false;
    }

    public function getSlugColumnUid(): ?int
    {
        return !empty($this->options['slugColumn']) ? $this->options['slugColumn'] : null;
    }

    public function getPageTitleMode(): ?string
    {
        return !empty($this->options['pageTitleMode']) ? $this->options['pageTitleMode'] : null;
    }

    public function getPageTitleColumnUid(): ?int
    {
        if ('keep' === $this->getPageTitleMode()) {
            return null;
        }

        return !empty($this->options['pageTitleColumn']) ? $this->options['pageTitleColumn'] : null;
    }

    public function isXmlSitemapEnabled(): bool
    {
        return $this->options['enableXmlSitemap'] ?? false;
    }

    public function getXmlSitemapIdentifier(): string
    {
        return $this->options['xmlSitemapIdentifier'] ?? '';
    }

    // Rootline check

    public function isInRootline(int $rootPageId): bool
    {
        foreach ($this->getRootline() as $pageRow) {
            if ($pageRow['uid'] === $rootPageId) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getRootline(): array
    {
        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $this->row['pid']);

        return $rootlineUtility->get();
    }
}
