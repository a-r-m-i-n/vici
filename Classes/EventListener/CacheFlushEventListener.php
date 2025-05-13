<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\ViciManager;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Event\CacheFlushEvent;

#[AsEventListener(
    identifier: 'vici/cache-flush',
)]
readonly class CacheFlushEventListener
{
    public function __construct(
        private ViciManager $viciManager,
    ) {
    }

    public function __invoke(CacheFlushEvent $event): void
    {
        $this->viciManager->clearAll();
        $this->viciManager->generateAll();
    }
}
