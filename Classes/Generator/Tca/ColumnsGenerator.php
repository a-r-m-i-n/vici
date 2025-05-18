<?php

namespace T3\Vici\Generator\Tca;

use T3\Vici\Generator\AbstractPhpCodeGenerator;
use T3\Vici\Generator\Tca\FieldTypes\FieldTypes;

class ColumnsGenerator extends AbstractPhpCodeGenerator
{
    protected function generatePhpCode(): string
    {
        $data = [];

        foreach ($this->tableColumns as $tableColumn) {
            $fieldType = FieldTypes::from($tableColumn['type']);
            $instance = $fieldType->getInstance();
            if (!$instance) {
                continue;
            }

            $tca = [
                'exclude' => (bool)$tableColumn['excluded'],
                'label' => $tableColumn['title'],
                'config' => $instance->buildTcaConfig($tableColumn),
            ];
            $tca['config']['type'] = $instance->getType();

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
