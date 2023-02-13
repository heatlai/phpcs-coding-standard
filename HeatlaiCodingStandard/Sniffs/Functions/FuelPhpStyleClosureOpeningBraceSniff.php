<?php

namespace HeatlaiCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\OpeningFunctionBraceBsdAllmanSniff;
use PHP_CodeSniffer\Util\Tokens;
use HeatlaiCodingStandard\Helpers\FunctionDeclaration;

class FuelPhpStyleClosureOpeningBraceSniff implements Sniff
{
    public function register()
    {
        return [
            T_CLOSURE,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        if (isset($tokens[$stackPtr]['scope_opener'], $tokens[$stackPtr]['scope_closer']) === false) {
            return;
        }

        if (FunctionDeclaration::isMultiLineDeclaration($phpcsFile, $stackPtr) === false) {
            $sniff = new OpeningFunctionBraceBsdAllmanSniff();
            $sniff->checkClosures = true;
            $sniff->process($phpcsFile, $stackPtr);
        }
    }
}
