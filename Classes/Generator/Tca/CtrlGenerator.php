<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class CtrlGenerator extends AbstractPhpCodeGenerator
{
    protected function generatePhpCode(): string
    {
        $data = [];

        $data['groupName'] = 'tx_vici_custom';

        // Title
        $data['title'] = $this->table['title'] ?? '';
        if (empty($data['title'])) {
            $data['title'] = GeneralUtility::underscoredToUpperCamelCase($this->table['name']);
        }

        // Label
        $labelColumn = $this->table['label'];
        if (empty($labelColumn)) {
            $labelColumn = array_key_first($this->tableColumns);
        }
        $data['label'] = $this->tableColumns[$labelColumn]['name'];

        // Icon
        $icon = $this->table['icon'];
        if (empty($icon)) {
            $icon = 'content-database';
        }
        $data['typeicon_classes'] = ['default' => $icon];

        return var_export($data, true);
    }
}
