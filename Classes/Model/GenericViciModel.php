<?php

namespace T3\Vici\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

abstract class GenericViciModel extends AbstractEntity
{
    /**
     * @var array<string, mixed>
     */
    public array $_record = [];

    public string $_tablename = '';

    public function __toString(): string
    {
        $labelField = $GLOBALS['TCA'][$this->_tablename]['ctrl']['label'] ?? null;
        $label = '';
        if ($labelField && array_key_exists($labelField, $this->_record)) {
            $label = $this->_record[$labelField] ?? '';
        }

        if (!empty($label)) {
            return $label;
        }

        return $this->_tablename . ':' . $this->_record['uid'];
    }
}
