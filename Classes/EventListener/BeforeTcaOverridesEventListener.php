<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\TcaManager;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\BeforeTcaOverridesEvent;

#[AsEventListener(
    identifier: 'vici/before-tca-overrides',
)]
readonly class BeforeTcaOverridesEventListener
{
    public function __construct(
        private TcaManager $tcaManager,
    ) {
    }

    public function __invoke(BeforeTcaOverridesEvent $event): void
    {
        $tca = $event->getTca();

        $generatedTca = $this->tcaManager->load();

        foreach ($generatedTca as $tableName => $tcaConfig) {
            if (is_array($tcaConfig)) {
                $tca[$tableName] = $tcaConfig;
            }
        }

        $event->setTca($tca);
    }
}
