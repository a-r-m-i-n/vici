<?php

namespace T3\Vici\Hook;

use T3\Vici\Generator\ViciManager;
use T3\Vici\Localization\TranslationRepository;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SingletonInterface;

class DataHandlerHook implements SingletonInterface
{
    /**
     * @var array<string, array<string, string>>
     */
    private array $translationMap = [];

    public function __construct(
        private readonly ViciManager $viciManager,
        private readonly TranslationRepository $translationRepository
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public function clearCachePostProc(array $params): void
    {
        if ('all' === ($params['cacheCmd'] ?? null)) {
            $this->viciManager->clearAll();
            $this->viciManager->generateAll();
        }
    }

    /**
     * @param array<string, mixed> $incomingFieldArray
     */
    public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, string $table, string|int $id, DataHandler $dataHandler): void
    {
        if (!str_starts_with($table, 'tx_vici_table')) {
            return;
        }

        $tableTca = $GLOBALS['TCA'][$table]['columns'];
        foreach ($incomingFieldArray as $name => $value) {
            if (array_key_exists($name, $tableTca)
                && 'user' === $tableTca[$name]['config']['type']
                && 'viciTranslatableInput' === $tableTca[$name]['config']['renderType']
            ) {
                if (is_array($value)) {
                    $defaultValue = $value['default'];
                    if (!empty($value)) {
                        $identifier = TranslationRepository::getIdentifier($table, $id, $name);
                        $this->translationMap[$identifier] = [];
                        foreach ($value as $languageKey => $valueForLanguage) {
                            $this->translationMap[$identifier][$languageKey] = $valueForLanguage;
                        }
                    }
                    $incomingFieldArray[$name] = $defaultValue;
                } else {
                    // Update default value in translations table
                    $identifier = TranslationRepository::getIdentifier($table, $id, $name);
                    $this->translationMap[$identifier] = [
                        'default' => $value,
                    ];
                }
            }
        }
    }

    /**
     * @param array<string, mixed> $fieldArray
     */
    public function processDatamap_afterDatabaseOperations(
        string $status,
        string $table,
        int|string $id,
        array $fieldArray,
        DataHandler $dataHandler
    ): void {
        foreach ($this->translationMap as $identifier => $values) {
            $parsedIdentifier = TranslationRepository::parseIdentifier($identifier);
            if (!is_int($parsedIdentifier['uid'])) {
                $resolvedUid = $this->resolveUid($parsedIdentifier['uid'], $parsedIdentifier['tableName'], $status, $dataHandler);
                $identifier = TranslationRepository::getIdentifier($parsedIdentifier['tableName'], $resolvedUid, $parsedIdentifier['fieldName']);
            }
            foreach ($values as $languageKey => $value) {
                $this->translationRepository->save($identifier, $languageKey, $value);
            }
        }
        $this->translationMap = [];
    }

    private function resolveUid(string|int $id, string $table, string $status, DataHandler $pObj): int
    {
        $uid = $id;
        if ('new' === $status) {
            if (!($pObj->substNEWwithIDs[$id] ?? null)) {
                // postProcessFieldArray
                $uid = 0;
            } else {
                // afterDatabaseOperations
                $uid = $pObj->substNEWwithIDs[$id];
                if (isset($pObj->autoVersionIdMap[$table][$uid])) {
                    $uid = $pObj->autoVersionIdMap[$table][$uid];
                }
            }
        }

        return (int)$uid;
    }
}
