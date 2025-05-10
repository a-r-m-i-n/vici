<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\TcaLoader;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\Event\BeforeTcaOverridesEvent;

#[AsEventListener(
    identifier: 'vici/before-tca-overrides',
)]
readonly class BeforeTcaOverridesEventListener
{
    public function __construct(
        private TcaLoader $tcaLoader,
    ) {
    }

    public function __invoke(BeforeTcaOverridesEvent $event): void
    {
        $tca = $event->getTca();

        foreach (($this->tcaLoader)() as $tableName => $tcaConfig) {
            if (is_array($tcaConfig)) {
                $tca[$tableName] = $tcaConfig;
            }
        }

        $event->setTca($tca);
    }
}
