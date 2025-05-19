<?php

namespace T3\Vici\Generator;

use Symfony\Component\Finder\Finder;
use T3\Vici\Generator\Extbase\PropertiesGenerator;
use T3\Vici\Generator\Tca\ColumnsGenerator;
use T3\Vici\Generator\Tca\CtrlGenerator;
use T3\Vici\Generator\Tca\PalettesGenerator;
use T3\Vici\Generator\Tca\TypesGenerator;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class ViciManager
{
    public function __construct(
        private StaticValues $staticValues,
        private ViciRepository $repository
    ) {
    }

    /**
     * @see \T3\Vici\Hook\DataHandlerHook::clearCachePostProc
     */
    public function clearAll(): void
    {
        if (file_exists($this->staticValues->getCachePathForTca())) {
            $finder = new Finder();
            $tcaFiles = $finder
                ->files()
                ->in($this->staticValues->getCachePathForTca())
                ->name($this->staticValues->getTableNamePrefix() . '*.php')
                ->depth(0)
            ;
            foreach ($tcaFiles as $tcaFile) {
                unlink($tcaFile->getRealPath());
            }
        }
        if (file_exists($this->staticValues->getCachePathForProxyClasses())) {
            $finder = new Finder();
            $proxyClassFiles = $finder
                ->files()
                ->in($this->staticValues->getCachePathForProxyClasses())
                ->name('*.php')
                ->depth(0)
            ;
            foreach ($proxyClassFiles as $proxyClassFile) {
                unlink($proxyClassFile->getRealPath());
            }
        }
    }

    /**
     * @see \T3\Vici\Hook\DataHandlerHook::clearCachePostProc
     */
    public function generateAll(): void
    {
        $this->ensureExistingDirectories();

        foreach ($this->repository->findAllTables() as $tableRow) {
            $this->generateTca($tableRow);
            $this->generateProxyClass($tableRow);
        }
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    public function checkIfTableTcaIsUpToDate(array $tableRow): ?bool
    {
        $latestTstamp = $tableRow['tstamp'];
        $tableColumns = $this->repository->findTableColumnsByTableUid($tableRow['uid']);
        foreach ($tableColumns as $tableColumn) {
            if ($tableColumn['tstamp'] > $latestTstamp) {
                $latestTstamp = $tableColumn['tstamp'];
            }
        }

        $tableName = $this->staticValues->getFullTableName($tableRow['name']);
        $tcaFilePath = $this->staticValues->getCachePathForTca($tableName . '.php');

        if (!file_exists($tcaFilePath)) {
            return null;
        }

        return filectime($tcaFilePath) > $latestTstamp;
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    public function checkIfTableIsExistingInCachedTca(array $tableRow): bool
    {
        $tableName = $this->staticValues->getFullTableName($tableRow['name']);

        return array_key_exists($tableName, $GLOBALS['TCA'] ?? []);
    }

    private function ensureExistingDirectories(): void
    {
        $cachePathForTca = $this->staticValues->getCachePathForTca();
        if (!file_exists($cachePathForTca)) {
            GeneralUtility::mkdir_deep($cachePathForTca);
        }

        $cachePathForProxyClasses = $this->staticValues->getCachePathForProxyClasses();
        if (!file_exists($cachePathForProxyClasses)) {
            GeneralUtility::mkdir_deep($cachePathForProxyClasses);
        }
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    private function generateTca(array $tableRow): void
    {
        $tableName = $this->staticValues->getFullTableName($tableRow['name']);

        if ($tableRow['hidden']) {
            return;
        }

        $tableColumns = $this->repository->findTableColumnsByTableUid($tableRow['uid']);
        if (empty($tableColumns)) {
            return;
        }

        // TCA generation
        $ctrlCode = new CtrlGenerator($tableRow, $tableColumns);
        $palettesCode = new PalettesGenerator($tableRow, $tableColumns);
        $typesCode = new TypesGenerator($tableRow, $tableColumns);
        $columnsCode = new ColumnsGenerator($tableRow, $tableColumns);

        $generationInfoComment = '// TCA for ' . $tableName . ' [uid=' . $tableRow['uid'] . ', pid=' . $tableRow['pid'] . ']' . PHP_EOL;
        $time = new \DateTimeImmutable();
        $generationInfoComment .= '// generated at ' . $time->format('d.m.Y H:i:s') . ' (' . $time->getTimestamp() . ')';

        $destinationPath = $this->staticValues->getCachePathForTca($tableName . '.php');
        GeneralUtility::writeFile($destinationPath, <<<PHP
            <?php
            $generationInfoComment

            return array(
              'ctrl' => $ctrlCode,
              'palettes' => $palettesCode,
              'types' => $typesCode,
              'columns' => $columnsCode,
            );
            PHP, true);
    }

    /**
     * @param array<string, mixed> $tableRow
     */
    private function generateProxyClass(array $tableRow): void
    {
        // Extbase Model generation
        $modelName = GeneralUtility::underscoredToUpperCamelCase($tableRow['name']);

        if ($tableRow['hidden']) {
            return;
        }

        $tableColumns = $this->repository->findTableColumnsByTableUid($tableRow['uid']);
        if (empty($tableColumns)) {
            return;
        }

        $propertiesCode = new PropertiesGenerator($tableRow, $tableColumns);
        $namespace = $this->staticValues->getProxyClassNamespace();

        $destinationPath = $this->staticValues->getCachePathForProxyClasses($modelName . '.php');
        GeneralUtility::writeFile($destinationPath, <<<PHP
            <?php

            namespace $namespace;

            class $modelName extends \T3\Vici\Model\GenericViciModel
            {
            $propertiesCode
            }
            PHP, true);
    }
}
