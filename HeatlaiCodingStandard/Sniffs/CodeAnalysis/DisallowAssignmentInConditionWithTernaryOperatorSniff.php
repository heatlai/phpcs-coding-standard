<?php

namespace HeatlaiCodingStandard\Sniffs\CodeAnalysis;

use HeatlaiCodingStandard\Helpers\OperatorHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class DisallowAssignmentInConditionWithTernaryOperatorSniff implements Sniff
{
    /**
     * Assignment tokens to trigger on.
     *
     * Set in the register() method.
     *
     * @var array
     */
    protected $assignmentTokens = [];

    public function register()
    {
        $this->assignmentTokens = Tokens::$assignmentTokens;
        unset($this->assignmentTokens[T_DOUBLE_ARROW]);

        return [
            T_INLINE_THEN,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $leftSideTokens = OperatorHelper::getLeftSideTokens($tokens, $stackPtr);

        foreach ($leftSideTokens as $pointer => $token) {
            if (isset($this->assignmentTokens[$token['code']])) {
                $error = 'Variable assignment found within a condition. Did you mean to do a comparison ?';
                $phpcsFile->addWarning($error, $pointer, 'Found');
                return;
            }
        }
    }
}
