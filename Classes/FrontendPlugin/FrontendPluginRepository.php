<?php

namespace T3\Vici\FrontendPlugin;

use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
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

        $rows = $queryBuilder->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('vici_frontend')))
            ->andWhere($queryBuilder->expr()->eq('l18n_parent', $queryBuilder->createNamedParameter(0, ParameterType::INTEGER)))
            ->orderBy('pid')
            ->addOrderBy('sys_language_uid')
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
     * @return array<int, array<string, mixed>>
     */
    public function findTranslations(FrontendPlugin $frontendPlugin): array
    {
        $queryBuilder = $this->databaseConnectionPool->getQueryBuilderForTable('tt_content');

        return $queryBuilder->select('*')
            ->from('tt_content')
            ->where($queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('vici_frontend')))
            ->andWhere($queryBuilder->expr()->eq('l18n_parent', $queryBuilder->createNamedParameter($frontendPlugin->getUid(), ParameterType::INTEGER)))
            ->orderBy('pid')
            ->addOrderBy('sys_language_uid')
            ->executeQuery()
            ->fetchAllAssociative()
        ;
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
