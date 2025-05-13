<?php

namespace T3\Vici\UserFunction\TcaFieldValidator;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class ReservedTcaColumnsValidator extends AbstractValidator
{
    private const RESERVED_COLUMNS = [
        'uid',
        'pid',
        'tstamp',
        'crdate',
        'deleted',
        'hidden',
        'starttime',
        'endtime',
        'fe_group',
        'sorting',
        'editlock',
        't3_origuid',
        'sys_language_uid',
        'l10n_parent',
        'l10n_source',
        'l10n_state',
        'l10n_diffsource',
        't3ver_oid',
        't3ver_wsid',
        't3ver_state',
        't3ver_stage',
        'perms_userid',
        'perms_groupid',
        'perms_user',
        'perms_group',
        'perms_everybody',
    ];

    public function evaluateFieldValue(string $value, ?string $isIn, bool &$set): string
    {
        if (in_array($value, self::RESERVED_COLUMNS, true)) {
            $set = false;
            $this->addFlashMessage(
                'The given column name is reserved and may not get used.',
                'Invalid column name "' . $value . '" given',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $value;
    }
}
