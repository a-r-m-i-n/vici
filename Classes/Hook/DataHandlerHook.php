<?php

namespace T3\Vici\Hook;

use T3\Vici\Generator\ViciManager;
use TYPO3\CMS\Core\SingletonInterface;

readonly class DataHandlerHook implements SingletonInterface
{
    public function __construct(private ViciManager $viciManager)
    {
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
}
