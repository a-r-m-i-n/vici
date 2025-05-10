<?php

namespace T3\Vici\Generator;

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
     * @see \T3\Vici\Hook\TcemainHook::processDatamap_afterDatabaseOperations
     */
    public function generate(int $tableUid): void
    {
        $this->generateTca($tableUid);
        $this->generateProxyClass($tableUid);
    }

    /**
     * @see \T3\Vici\Hook\TcemainHook::processCmdmap_deleteAction
     * @see \T3\Vici\Hook\TcemainHook::processDatamap_afterDatabaseOperations
     */
    public function delete(int|string $uidOrTableName): void
    {
        $this->deleteTca($uidOrTableName);
        $this->deleteProxyClass($uidOrTableName);
    }

    private function generateTca(int $tableUid): void
    {
        $tableRow = $this->repository->findTableByUid($tableUid);

        $tableName = $this->staticValues->getFullTableName($tableRow['name']);
        $destinationPath = $this->staticValues->getCachePathForTca($tableName . '.php');

        if (!file_exists(dirname($destinationPath))) {
            GeneralUtility::mkdir_deep(dirname($destinationPath));
        }
        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }
        if ($tableRow['hidden']) {
            return;
        }

        $tableColumns = $this->repository->findTableColumnsByTableUid($tableUid);
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

    private function generateProxyClass(int $tableUid): void
    {
        $tableRow = $this->repository->findTableByUid($tableUid);

        // Extbase Model generation
        $modelName = GeneralUtility::underscoredToUpperCamelCase($tableRow['name']);
        $destinationPath = $this->staticValues->getCachePathForProxyClasses($modelName . '.php');
        if (!file_exists(dirname($destinationPath))) {
            GeneralUtility::mkdir_deep(dirname($destinationPath));
        }
        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }

        $tableColumns = $this->repository->findTableColumnsByTableUid($tableUid);
        if (empty($tableColumns)) {
            return;
        }

        $propertiesCode = new PropertiesGenerator($tableRow, $tableColumns);
        $namespace = $this->staticValues->getProxyClassNamespace();
        GeneralUtility::writeFile($destinationPath, <<<PHP
            <?php

            namespace $namespace;

            class $modelName extends \T3\Vici\Model\GenericViciModel
            {
            $propertiesCode
            }
            PHP, true);
    }

    private function deleteTca(int|string $uidOrTableName): void
    {
        if (is_int($uidOrTableName)) {
            $table = $this->repository->findTableByUid($uidOrTableName);
            $tableName = $this->staticValues->getFullTableName($table['name']);
        } else {
            $tableName = $this->staticValues->getFullTableName($uidOrTableName);
        }
        $destinationPath = $this->staticValues->getCachePathForTca($tableName . '.php');

        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }
    }

    private function deleteProxyClass(int|string $uidOrTableName): void
    {
        // TODO
    }
}
