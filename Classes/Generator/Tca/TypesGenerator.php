<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\Helper\SystemFieldsTrait;

class TypesGenerator extends AbstractPhpCodeGenerator
{
    use SystemFieldsTrait;

    protected function generatePhpCode(): string
    {
        $showItems = $this->getTableColumnNames();

        if ($this->requiresLanguageTab()) {
            $showItems[] = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language';
            $showItems[] = '--palette--;;language';
        }

        if ($this->requiresAccessTab()) {
            $showItems[] = '--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access';
            $showItems[] = '--palette--;;access';
        }

        $data = [0 => ['showitem' => implode(',', $showItems)]];

        // TODO Support own types

        return var_export($data, true);
    }

    /**
     * @return string[]
     */
    private function getTableColumnNames(): array
    {
        $names = [];
        foreach ($this->tableColumns as $tableColumn) {
            $names[] = $tableColumn['name'];
        }

        return $names;
    }
}
