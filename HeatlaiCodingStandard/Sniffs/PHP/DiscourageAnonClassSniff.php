<?php

namespace HeatlaiCodingStandard\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DiscourageAnonClassSniff implements Sniff
{
    public function register()
    {
        return [
            T_ANON_CLASS,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $phpcsFile->addWarning('[Anonymous Class] is discouraged', $stackPtr, 'Found');
    }
}
