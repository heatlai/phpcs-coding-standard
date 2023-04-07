<?php

namespace HeatlaiCodingStandard\Sniffs\OperatorSpacing;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class AssignmentOneSpaceEachSideSniff implements Sniff
{
    public function register()
    {
        $tokens = Tokens::$assignmentTokens;
        unset($tokens[T_DOUBLE_ARROW]);
        return $tokens;
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // check spaces before assignment
        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
            $content = $tokens[($stackPtr - 1)]['content'];
            $error = 'Expected 1 space between "%s" and "%s"; 0 found';
            $data = [
                $content,
                $tokens[$stackPtr]['content'],
            ];
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'NoSpaceBeforeAssignment', $data);
            if ($fix === true) {
                $phpcsFile->fixer->addContentBefore($stackPtr, ' ');
            }
        } else {
            $spaceLength = $tokens[($stackPtr - 1)]['length'];
            if ($spaceLength !== 1) {
                $content = $tokens[($stackPtr - 2)]['content'];
                $error = 'Expected 1 space between "%s" and "%s"; %s found';
                $data = [
                    $content,
                    $tokens[$stackPtr]['content'],
                    $spaceLength,
                ];

                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceBeforeAssignment', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($stackPtr - 1), ' ');
                }
            }
        }//end if

        // check spaces after assignment
        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
            $content = $tokens[($stackPtr + 1)]['content'];
            $error = 'Expected 1 space between "%s" and "%s"; 0 found';
            $data = [
                $tokens[$stackPtr]['content'],
                $content
            ];
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'NoSpaceAfterAssignment', $data);
            if ($fix === true) {
                $phpcsFile->fixer->addContent($stackPtr, ' ');
            }
        } else {
            $spaceLength = $tokens[($stackPtr + 1)]['length'];
            if ($spaceLength !== 1) {
                $content = $tokens[($stackPtr + 2)]['content'];
                $error = 'Expected 1 space between "%s" and "%s"; %s found';
                $data = [
                    $tokens[$stackPtr]['content'],
                    $content,
                    $spaceLength,
                ];

                $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterAssignment', $data);
                if ($fix === true) {
                    $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                }
            }
        }//end if
    }
}
