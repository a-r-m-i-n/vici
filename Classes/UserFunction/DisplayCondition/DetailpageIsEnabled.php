<?php

namespace T3\Vici\UserFunction\DisplayCondition;

use T3\Vici\FrontendPlugin\FrontendPluginRepository;
use TYPO3\CMS\Backend\Form\FormDataProvider\EvaluateDisplayConditions;
use TYPO3\CMS\Backend\Utility\BackendUtility;

readonly class DetailpageIsEnabled
{
    public function __construct(private FrontendPluginRepository $frontendPluginRepository)
    {
    }

    /**
     * @param array{record: array<string, mixed>, flexContext: array<string, mixed>, flexformValueKey: string, conditionParamters: array<int|string, mixed>} $params
     */
    public function check(array $params, EvaluateDisplayConditions $evaluateDisplayConditions): bool
    {
        if (empty($params['record']['uid']) || !is_int($params['record']['uid'])) {
            return false;
        }

        $rawRow = BackendUtility::getRecord('tt_content', $params['record']['uid']);
        if (!$rawRow) {
            return false;
        }

        $frontendPlugin = $this->frontendPluginRepository->createFrontendPluginInstance($rawRow);

        return $frontendPlugin->isDetailpageEnabled();
    }
}
