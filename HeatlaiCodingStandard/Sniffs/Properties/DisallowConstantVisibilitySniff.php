<?php

namespace HeatlaiCodingStandard\Sniffs\Properties;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class DisallowConstantVisibilitySniff implements Sniff
{
    public function register()
    {
        return [T_CONST];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Make sure this is a class constant.
        if ($phpcsFile->hasCondition($stackPtr, Tokens::$ooScopeTokens) === false) {
            return;
        }

        $ignore   = Tokens::$emptyTokens;
        $ignore[] = T_FINAL;

        $prev = $phpcsFile->findPrevious($ignore, ($stackPtr - 1), null, true);
        if (isset(Tokens::$scopeModifiers[$tokens[$prev]['code']]) === true) {
            $error = 'Visibility must not be declared on all constants; use "const" instead of "%s const"';
            $data = [
                $tokens[$prev]['content'],
            ];
            $phpcsFile->addWarning($error, $stackPtr, 'Found', $data);
        }
    }
}
