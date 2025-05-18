<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\Helper\SystemFieldsTrait;

class PalettesGenerator extends AbstractPhpCodeGenerator
{
    use SystemFieldsTrait;

    protected function generatePhpCode(): string
    {
        $data = [];

        if ($this->requiresLanguageTab()) {
            $data['language'] = ['showitem' => 'sys_language_uid,l18n_parent'];
        }

        if ($this->requiresAccessTab()) {
            $accessPaletteItems = [];
            if ($this->table['enable_column_hidden']) {
                $accessPaletteItems[] = 'hidden;LLL:EXT:core/Resources/Private/Language/locallang_general.xlf:LGL.enabled';
                $accessPaletteItems[] = '--linebreak--';
            }

            if ($this->table['enable_column_start_end_time']) {
                $accessPaletteItems[] = 'starttime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:starttime_formlabel';
                $accessPaletteItems[] = 'endtime;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:endtime_formlabel';
                $accessPaletteItems[] = '--linebreak--';
            }

            if ($this->table['enable_column_fegroup']) {
                $accessPaletteItems[] = 'fe_group;LLL:EXT:frontend/Resources/Private/Language/locallang_ttc.xlf:fe_group_formlabel';
                $accessPaletteItems[] = '--linebreak--';
            }

            if ($this->table['enable_column_editlock']) {
                $accessPaletteItems[] = 'editlock';
                $accessPaletteItems[] = '--linebreak--';
            }

            array_pop($accessPaletteItems);
            $data['access'] = ['showitem' => implode(',', $accessPaletteItems)];
        }

        // TODO

        return var_export($data, true);
    }
}
