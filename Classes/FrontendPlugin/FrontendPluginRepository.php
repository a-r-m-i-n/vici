<?php

namespace T3\Vici\FrontendPlugin;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Service\FlexFormService;

readonly class FrontendPluginRepository
{
    public function __construct(
        private ConnectionPool $databaseConnectionPool,
        private FlexFormService $flexFormService
    ) {
    }

    /**
     * @return FrontendPlugin[]
     */
    public function findAll(): array
    {
        $queryBuilder = $this->databaseConnectionPool->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(new HiddenRestriction());

        $rows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('vici_frontend')))
            ->executeQuery()
            ->fetchAllAssociative()
        ;

        $pluginInstances = [];
        foreach ($rows as $row) {
            $pluginInstances[] = $this->createFrontendPluginInstance($row);
        }

        return $pluginInstances;
    }

    /**
     * @param array<string, mixed> $row
     */
    public function createFrontendPluginInstance(array $row): FrontendPlugin
    {
        $parsedFlexForm = [];
        if (!empty($row['tx_vici_options'])) {
            $parsedFlexForm = $this->flexFormService->convertFlexFormContentToArray($row['tx_vici_options']);
            $parsedFlexForm = $parsedFlexForm['settings'] ?? [];
        }

        return new FrontendPlugin($row, $parsedFlexForm);
    }
}
