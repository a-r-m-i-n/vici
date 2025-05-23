<?php

namespace T3\Vici\EventListener;

use T3\Vici\Generator\StaticValues;
use T3\Vici\Model\GenericViciModel;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Event\Persistence\AfterObjectThawedEvent;

#[AsEventListener(
    identifier: 'vici/after-object-thawed',
)]
readonly class AfterObjectThawedEventListener
{
    public function __construct(private StaticValues $staticValues)
    {
    }

    public function __invoke(AfterObjectThawedEvent $event): void
    {
        $object = $event->getObject();
        if ($object instanceof GenericViciModel) {
            $object->_record = $event->getRecord();

            // Set _tablename
            $classNameParts = GeneralUtility::trimExplode('\\', get_class($object), true);
            $name = array_pop($classNameParts);
            if ($name) {
                $object->_tablename = $this->staticValues->getFullTableName(GeneralUtility::camelCaseToLowerCaseUnderscored($name));
            }
        }
    }
}
