<?php

namespace HeatlaiCodingStandard\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DisallowHashCommentsSniff implements Sniff
{
    /**
     * Returns the token types that this sniff is interested in.
     *
     * @return array
     */
    public function register()
    {
        return [T_COMMENT];
    }

    /**
     * Processes this sniff, when one of its tokens is encountered.
     *
     * @param  File  $phpcsFile  The current file being checked.
     * @param  int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $comment = trim($tokens[$stackPtr]['content']);
        if ($comment[0] === '#') {
            $error = 'Hash comments are prohibited; found "%s"';
            $data = [$comment];
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }
    }
}
