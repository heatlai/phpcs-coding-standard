<?php

namespace HeatlaiCodingStandard\Sniffs\CodeAnalysis;

use HeatlaiCodingStandard\Helpers\OperatorHelper;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DisallowBooleanEqualOperatorSniff implements Sniff
{
    protected $booleanTokens = [
        T_TRUE => T_TRUE,
        T_FALSE => T_FALSE,
    ];

    /**
     * @return array<int, (int|string)>
     */
    public function register(): array
    {
        return [
            T_IS_EQUAL,
            T_IS_NOT_EQUAL,
        ];
    }

    /**
     * @param  File  $phpcsFile  The file being scanned.
     * @param  int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $leftSideTokens = OperatorHelper::getLeftSideTokens($tokens, $stackPtr);
        $rightSideTokens = OperatorHelper::getRightSideTokens($tokens, $stackPtr);
        $firstLeftSideToken = current($leftSideTokens);
        $firstRightSideToken = current($rightSideTokens);

        if (
            isset($this->booleanTokens[$firstLeftSideToken['code']])
            || isset($this->booleanTokens[$firstRightSideToken['code']])
        ) {
            $error = 'Boolean comparisons should use "===" instead of "=="';
            $phpcsFile->addError($error, $stackPtr, 'Found');
        }
    }
}
