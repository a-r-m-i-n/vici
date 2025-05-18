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

    private const RESERVED_COLUMN_PARTS = [
        'zzz_deleted',
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

        foreach (self::RESERVED_COLUMN_PARTS as $columnPart) {
            if (str_contains($value, $columnPart)) {
                $set = false;
                $this->addFlashMessage(
                    'Column name contains reserved name',
                    'Invalid column name "' . $value . '" given. The string "' . $columnPart . '" may not be contained.',
                    ContextualFeedbackSeverity::ERROR
                );
            }
        }

        return $value;
    }
}
