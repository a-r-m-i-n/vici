<?php

namespace T3\Vici\Generator;

use Symfony\Component\Finder\Finder;
use T3\Vici\Generator\Tca\ColumnsGenerator;
use T3\Vici\Generator\Tca\CtrlGenerator;
use T3\Vici\Generator\Tca\PalettesGenerator;
use T3\Vici\Generator\Tca\TypesGenerator;
use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaManager
{
    private const TABLE_PREFIX = 'tx_vici_custom_';

    public function __construct(private readonly ViciRepository $repository)
    {
    }

    public function generate(int $uid, string $status): void
    {
        $table = $this->repository->findTableByUid($uid);
        if (!$table) {
            throw new \UnexpectedValueException('No "tx_vici_table" entry found with uid ' . $uid);
        }

        $tableName = self::TABLE_PREFIX . $table['name'];
        $destinationPath = Environment::getVarPath() . '/cache/code/vici/' . $tableName . '.php';

        if (!file_exists(dirname($destinationPath))) {
            GeneralUtility::mkdir_deep(dirname($destinationPath));
        }

        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }

        if ($table['hidden']) {
            return;
        }

        // Start generation
        $tableColumns = $this->repository->findTableColumnsByTableUid($uid);

        if (empty($tableColumns)) {
            return;
        }

        $ctrlCode = new CtrlGenerator($table, $tableColumns);
        $palettesCode = new PalettesGenerator($table, $tableColumns);
        $typesCode = new TypesGenerator($table, $tableColumns);
        $columnsCode = new ColumnsGenerator($table, $tableColumns);

        $generationInfoComment = '// TCA for ' . $tableName . ' [uid=' . $table['uid'] . ', pid=' . $table['pid'] . ']' . PHP_EOL;
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

    public function delete(int|string $uidOrTableName): void
    {
        $tableName = self::TABLE_PREFIX . $uidOrTableName;
        if (is_int($uidOrTableName)) {
            $table = $this->repository->findTableByUid($uidOrTableName);
            if (!$table) {
                throw new \UnexpectedValueException('No "tx_vici_table" entry found with uid ' . $uidOrTableName);
            }
            $tableName = self::TABLE_PREFIX . $table['name'];
        }
        $destinationPath = Environment::getVarPath() . '/cache/code/vici/' . $tableName . '.php';

        if (file_exists($destinationPath)) {
            unlink($destinationPath);
        }
    }

    /**
     * @return array<string, mixed> Key is the table name, value the generated TCA
     */
    public function load(): array
    {
        $path = Environment::getVarPath() . '/cache/code/vici/';
        if (!file_exists($path)) {
            return [];
        }

        $finder = new Finder();
        $files = $finder->files()
            ->in($path)
            ->name('tx_vici_custom_*.php')
        ;

        $loadedTca = [];
        foreach ($files as $file) {
            $loadedTca[$file->getFilenameWithoutExtension()] = require $file->getRealPath();
        }

        return $loadedTca;
    }
}
