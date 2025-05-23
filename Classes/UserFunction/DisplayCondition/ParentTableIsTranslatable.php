<?php

namespace T3\Vici\UserFunction\DisplayCondition;

use T3\Vici\Repository\ViciRepository;
use TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions;
use TYPO3\CMS\Core\Utility\GeneralUtility;

readonly class ParentTableIsTranslatable
{
    /**
     * @param array{record: array<string, mixed>, flexContext: array<string, mixed>, flexformValueKey: string, conditionParamters: array<int|string, mixed>} $params
     */
    public function check(array $params, EvaluateDisplayConditions $evaluateDisplayConditions): bool
    {
        $columnRecord = $params['record'];

        if (empty($columnRecord['parent']) || !is_int($columnRecord['parent'])) {
            return false;
        }

        /** @var ViciRepository $viciRepository */
        $viciRepository = GeneralUtility::makeInstance(ViciRepository::class);
        $viciTable = $viciRepository->findTableByUid($columnRecord['parent']);

        return (bool)($viciTable['enable_column_languages'] ?? false);
    }
}
