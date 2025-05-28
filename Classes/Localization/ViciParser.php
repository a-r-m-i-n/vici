<?php

namespace T3\Vici\Localization;

use TYPO3\CMS\Core\Localization\Parser\LocalizationParserInterface;

readonly class ViciParser implements LocalizationParserInterface
{
    public function __construct(private TranslationRepository $translationRepository)
    {
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function getParsedData($sourcePath, $languageKey): array
    {
        $allTranslations = $this->translationRepository->all();

        $parsedData = [];
        foreach ($allTranslations as $translation) {
            if (!array_key_exists($translation['language'], $parsedData)) {
                $parsedData[$translation['language']] = [];
            }

            $parsedData[$translation['language']][$translation['identifier']] = $translation['translation'];
        }

        if (!array_key_exists('en', $parsedData)) {
            $parsedData['en'] = [];
        }

        $parsedData['en'] = array_merge($parsedData['default'], $parsedData['en']);
        $parsedData['default'] = $parsedData['en'];

        return $parsedData;
    }
}
