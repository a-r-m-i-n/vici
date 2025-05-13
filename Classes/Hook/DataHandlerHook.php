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
     * @param array{cacheCmd: string, tags: string[]} $params
     */
    public function clearCachePostProc(array $params): void
    {
        if ('all' === $params['cacheCmd']) {
            $this->viciManager->clearAll();
            $this->viciManager->generateAll();
        }
    }
}
