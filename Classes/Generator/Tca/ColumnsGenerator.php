<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\FieldTypes\FieldTypes;
use T3\Vici\Localization\TranslationRepository;
use T3\Vici\Repository\ViciRepository;

class ColumnsGenerator extends AbstractPhpCodeGenerator
{
    protected function generatePhpCode(): string
    {
        $data = [];

        foreach ($this->tableColumns as $tableColumn) {
            $fieldType = FieldTypes::from($tableColumn['type']);
            $instance = $fieldType->getInstance();
            if (!$instance || empty($tableColumn['name'])) {
                continue;
            }

            $tca = [
                'exclude' => (bool)$tableColumn['excluded'],
                'label' => TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'title'),
                'config' => $instance->buildTcaConfig($tableColumn),
            ];
            $tca['config']['type'] = $instance->getType($tableColumn);

            if (!empty($tableColumn['description'])) {
                $tca['description'] = TranslationRepository::getLL(ViciRepository::TABLENAME_COLUMN, $tableColumn['uid'], 'description');
            }

            // Translatable options
            if (!empty($this->table['enable_column_languages'])) {
                if ($tableColumn['l10n_mode_exclude'] > 0) {
                    $tca['l10n_mode'] = 'exclude';
                }
                if (2 === $tableColumn['l10n_mode_exclude']) {
                    $tca['l10n_display'] = 'defaultAsReadonly';
                }
            }

            if (!empty($tableColumn['additional_config'])) {
                $additionalConfig = json_decode($tableColumn['additional_config'], true);
                if (!empty($additionalConfig) && is_array($additionalConfig)) {
                    $tca = array_merge_recursive($tca, $additionalConfig);
                }
            }

            $data[$tableColumn['name']] = $tca;
        }

        return var_export($data, true);
    }
}
