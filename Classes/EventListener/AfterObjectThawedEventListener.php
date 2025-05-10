<?php

namespace T3\Vici\EventListener;

use T3\Vici\Model\GenericViciModel;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Extbase\Event\Persistence\AfterObjectThawedEvent;

#[AsEventListener(
    identifier: 'vici/after-object-thawed',
)]
class AfterObjectThawedEventListener
{
    public function __invoke(AfterObjectThawedEvent $event): void
    {
        $object = $event->getObject();
        if ($object instanceof GenericViciModel) {
            $object->_record = $event->getRecord();
        }
    }
}
