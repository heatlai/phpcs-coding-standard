<?php

namespace HeatlaiCodingStandard\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Functions\OpeningFunctionBraceBsdAllmanSniff;
use PHP_CodeSniffer\Standards\Squiz\Sniffs\Functions\MultiLineFunctionDeclarationSniff;

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

        if (
            isset($tokens[$stackPtr]['scope_opener']) === false
            || isset($tokens[$stackPtr]['scope_closer']) === false
            || isset($tokens[$stackPtr]['parenthesis_opener']) === false
            || isset($tokens[$stackPtr]['parenthesis_closer']) === false
        ) {
            return;
        }

        $multiLineSniff = new MultiLineFunctionDeclarationSniff();
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        if ($multiLineSniff->isMultiLineDeclaration($phpcsFile, $stackPtr, $openBracket, $tokens) === false) {
            $sniff = new OpeningFunctionBraceBsdAllmanSniff();
            $sniff->checkClosures = true;
            $sniff->process($phpcsFile, $stackPtr);
        }
    }
}
