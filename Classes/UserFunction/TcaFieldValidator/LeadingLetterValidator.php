<?php

namespace T3\Vici\UserFunction\TcaFieldValidator;

use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class LeadingLetterValidator extends AbstractValidator
{
    public function evaluateFieldValue(string $value, ?string $isIn, bool &$set): string
    {
        if (!preg_match('/^[a-z]/', $value)) {
            $set = false;
            $this->addFlashMessage(
                'The value must start with a lowercase letter (a-z).',
                'Invalid value "' . $value . '" given',
                ContextualFeedbackSeverity::ERROR
            );
        }

        return $value;
    }
}
