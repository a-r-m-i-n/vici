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

        // System fields
        $data['enablecolumns'] = [];
        if (!empty($this->table['enable_column_hidden'])) {
            $data['enablecolumns']['disabled'] = 'hidden';
        }
        if (!empty($this->table['enable_column_start_end_time'])) {
            $data['enablecolumns']['starttime'] = 'starttime';
            $data['enablecolumns']['endtime'] = 'endtime';
        }
        if (!empty($this->table['enable_column_fegroup'])) {
            $data['enablecolumns']['fe_group'] = 'fe_group';
        }
        if (!empty($this->table['enable_column_editlock'])) {
            $data['enablecolumns']['editlock'] = 'editlock';
        }
        if (empty($data['enablecolumns'])) {
            unset($data['enablecolumns']);
        }

        if (!empty($this->table['enable_column_deleted'])) {
            $data['delete'] = 'deleted';
        }
        if (!empty($this->table['enable_column_sorting'])) {
            $data['sortby'] = 'sorting';
        }
        if (!empty($this->table['enable_column_timestamps'])) {
            $data['crdate'] = 'crdate';
            $data['tstamp'] = 'tstamp';
        }
        if (!empty($this->table['enable_column_versioning'])) {
            $data['versioningWS'] = true;
            $data['origUid'] = 't3_origuid';
        }
        if (!empty($this->table['enable_column_languages'])) {
            $data['languageField'] = 'sys_language_uid';
            $data['translationSource'] = 'l10n_source';
            $data['transOrigDiffSourceField'] = 'l10n_diffsource';
            $data['transOrigPointerField'] = 'l10n_parent';
        }

        return var_export($data, true);
    }
}
