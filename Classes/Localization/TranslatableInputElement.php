<?php

namespace T3\Vici\Localization;

use TYPO3\CMS\Backend\Form\Behavior\UpdateValueOnFieldChange;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\TcaSystemLanguageCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class TranslatableInputElement extends AbstractFormElement
{
    /**
     * @var array<string, mixed>
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    public function __construct(
        private readonly TcaSystemLanguageCollector $tcaSystemLanguageCollector,
        private readonly TranslationRepository $translationRepository,
        private readonly IconFactory $iconFactory
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function render(): array
    {
        $parameterArray = $this->data['parameterArray'];

        $tableName = $this->data['tableName'];
        $uid = $this->data['vanillaUid'];

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        $fieldId = StringUtility::getUniqueId('formengine-translatable-input-');
        $fieldLabel = $this->renderLabel($fieldId);
        $fieldName = htmlspecialchars($parameterArray['itemFormElName']);

        $itemValue = $parameterArray['itemFormElValue'];
        $fieldValue = htmlspecialchars($itemValue, ENT_QUOTES);

        $inputFields = '';
        foreach ($this->getAvailableBackendLanguages() as $languageKey => $languageName) {
            $fieldConfig = $parameterArray['fieldConf']['config'];
            if ('default' !== $languageKey) {
                if (!empty($uid)) {
                    $valueForLanguage = $this->translationRepository->get(
                        $this->translationRepository::getIdentifier($tableName, $uid, $this->data['fieldName']),
                        $languageKey
                    );
                    $fieldValue = $valueForLanguage ?? '';
                }

                if (array_key_exists('required', $fieldConfig)) {
                    unset($fieldConfig['required']);
                }
            }

            $languageKeyForIcon = $languageKey;
            if ('default' === $languageKey) {
                $languageKeyForIcon = 'en-us-gb';
                $languageName = 'Default (' . $languageName . ')';
            }
            $icon = $this->iconFactory->getIcon('flags-' . $languageKeyForIcon, IconSize::SMALL);

            $fieldChangeFunc = new UpdateValueOnFieldChange($tableName, $uid, $this->data['fieldName'], $fieldName . '[' . $languageKey . ']');
            $inputAttributes = array_merge(
                [
                    'type' => 'text',
                    'name' => $fieldName . '[' . $languageKey . ']',
                    'id' => $fieldId . $languageKey,
                    'class' => 'form-control form-control-clearable t3js-clearable rounded-start-0',
                    'value' => $fieldValue,
                    'placeholder' => $languageName,
                    'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($fieldConfig),
                    'data-formengine-input-name' => $fieldName . '[' . $languageKey . ']',
                ],
                $this->getOnFieldChangeAttrs('change', [$fieldChangeFunc])
            );

            $inputAttributes = GeneralUtility::implodeAttributes($inputAttributes, true);

            $inputFields .= <<<HTML
                <div class="form-control-wrap mb-2">
                    <div class="form-control-clearable-wrapper">
                        <div class="d-flex justify-content-start align-items-center">
                            <label for="$fieldId$languageKey" title="$languageName" class="input-group-text border-end-0 rounded-end-0"><span>$icon</span></label>

                            <input $inputAttributes />
                        </div>
                    </div>
                </div>
                HTML;
        }

        $resultArray['html'] = <<<HTML
            $fieldLabel
            <div class="formengine-field-item t3js-formengine-field-item">
                $fieldInformationHtml
                <div class="form-wizards-wrap">
                    <div class="form-wizards-item-element">
                    $inputFields
                    </div>
                </div>
            </div>
            HTML;

        return $resultArray;
    }

    /**
     * @return array<string, string>
     */
    private function getAvailableBackendLanguages(): array
    {
        $languages = [];
        $this->tcaSystemLanguageCollector->populateAvailableSystemLanguagesForBackend($languages);

        $availableLanguages = [];
        foreach ($languages['items'] as $language) {
            if ('installed' === $language['group']) {
                $availableLanguages[$language['value']] = $language['label'];
            }
        }

        return $availableLanguages;
    }
}
