<?php

namespace T3\Vici\UserFunction\ItemsProcFunc;

use T3\Vici\Repository\ViciRepository;

readonly class AvailableViciColumnsInFlexForm
{
    public function __construct(
        private ViciRepository $viciRepository,
    ) {
    }

    /**
     * @param array<string, mixed> $parameters
     */
    public function get(array &$parameters): void
    {
        $flexParentDatabaseRow = $parameters['flexParentDatabaseRow'] ?? null;
        if (empty($flexParentDatabaseRow['tx_vici_table'])) {
            return;
        }

        $parameters['items'][] = ['label' => '', 'value' => ''];

        $availableColumns = $this->viciRepository->findTableColumnsByTableUid($flexParentDatabaseRow['tx_vici_table']);
        foreach ($availableColumns as $column) {
            $parameters['items'][] = [
                'label' => $column['title'] . ' (' . $column['name'] . ')',
                'value' => $column['uid'],
            ];
        }
    }
}
