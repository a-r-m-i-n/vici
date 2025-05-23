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

    public function getViciTableUid(): int
    {
        return $this->row['tx_vici_table'] ?? 0;
    }

    public function getViciTemplate(): string
    {
        return $this->row['tx_vici_template'] ?? '';
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
