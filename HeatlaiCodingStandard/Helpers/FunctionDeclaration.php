<?php

namespace HeatlaiCodingStandard\Helpers;

use PHP_CodeSniffer\Files\File;

class FunctionDeclaration
{
    /**
     * Determine if this is a multi-line function declaration.
     *
     * @param  File  $phpcsFile  The file being scanned.
     * @param  int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return bool
     */
    public static function isMultiLineDeclaration(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $openBracket = $tokens[$stackPtr]['parenthesis_opener'];
        $closeBracket = $tokens[$stackPtr]['parenthesis_closer'];
        if ($tokens[$openBracket]['line'] !== $tokens[$closeBracket]['line']) {
            return true;
        }

        // Closures may use the USE keyword and so be multi-line in this way.
        if ($tokens[$stackPtr]['code'] === T_CLOSURE) {
            $use = $phpcsFile->findNext(T_USE, ($closeBracket + 1), $tokens[$stackPtr]['scope_opener']);
            if ($use !== false) {
                // If the opening and closing parenthesis of the use statement
                // are also on the same line, this is a single line declaration.
                $open = $phpcsFile->findNext(T_OPEN_PARENTHESIS, ($use + 1));
                $close = $tokens[$open]['parenthesis_closer'];
                if ($tokens[$open]['line'] !== $tokens[$close]['line']) {
                    return true;
                }
            }
        }
        return false;
    }
}
