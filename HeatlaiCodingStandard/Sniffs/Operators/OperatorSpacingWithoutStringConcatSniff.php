<?php

namespace HeatlaiCodingStandard\Sniffs\Operators;

use PHP_CodeSniffer\Standards\PSR12\Sniffs\Operators\OperatorSpacingSniff;
use PHP_CodeSniffer\Util\Tokens;

class OperatorSpacingWithoutStringConcatSniff extends OperatorSpacingSniff
{
    public function register()
    {
        parent::register();

        $targets   = Tokens::$comparisonTokens;
        $targets  += Tokens::$operators;
        $targets  += Tokens::$assignmentTokens;
        $targets  += Tokens::$booleanOperators;
        $targets[] = T_INLINE_THEN;
        $targets[] = T_INLINE_ELSE;
        // $targets[] = T_STRING_CONCAT;
        $targets[] = T_INSTANCEOF;

        return $targets;
    }
}
