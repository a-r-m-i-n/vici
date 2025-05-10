<?php

namespace T3\Vici\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class GenericViciModel extends AbstractEntity
{
    /**
     * @var array<string, mixed>
     */
    public array $_record = [];
}
