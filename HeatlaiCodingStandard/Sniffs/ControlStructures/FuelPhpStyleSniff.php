<?php

namespace HeatlaiCodingStandard\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use HeatlaiCodingStandard\Helpers\Str;

class FuelPhpStyleSniff implements Sniff
{
    public function register()
    {
        return [
            T_TRY,
            T_CATCH,
            T_DO,
            T_WHILE,
            T_FOR,
            T_FOREACH,
            T_IF,
            T_ELSEIF,
            T_ELSE,
            T_SWITCH,
        ];
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param  File  $phpcsFile  The file being scanned.
     * @param  int  $stackPtr  The position of the current token in the stack passed in $tokens.
     *
     * @return void
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        // Scope keyword should be on a new line.
        if (
            $this->hasBraces($token)
            || ($token['code'] === T_WHILE)
        ) {
            $this->detectScopeKeywordOnNewLine($phpcsFile, $stackPtr);
        }

        // Expect 1 space after keyword, Skip T_ELSE, T_DO, T_TRY.
        if (
            $this->hasBraces($token)
            && (in_array($token['code'], [T_ELSE, T_DO, T_TRY], true) === false)
        ) {
            $this->detectSpaceAfterScopeKeyword($phpcsFile, $stackPtr);
        }

        // Opening brace should be on a new line. Skip multi line statement
        if ($this->hasBraces($token)) {
            if ($this->isMultiLineStatement($tokens, $token)) {
                $this->detectClosingBracketAndOpeningBraceOnSameLine($phpcsFile, $stackPtr);
            } else {
                $this->detectOpeningBraceOnNewLine($phpcsFile, $stackPtr);
            }
        }

        // Closing brace should be on a new line.
        if ($this->hasBraces($token)) {
            $this->detectClosingBraceOnNewLine($phpcsFile, $stackPtr);
        }

        if (
            $this->hasBraces($token)
            && $token['code'] === T_SWITCH
        ) {
            $this->detectBreakIndentSameLevelAsSwitchCase($phpcsFile, $stackPtr);
        }
    }

    protected function isMultiLineStatement($tokens, $token): bool
    {
        if (isset($token['parenthesis_opener'], $token['parenthesis_closer']) === false) {
            return false;
        }

        $openingBracket = $token['parenthesis_opener']; // "("
        $closingBracket = $token['parenthesis_closer']; // ")"

        return $tokens[$openingBracket]['line'] !== $tokens[$closingBracket]['line'];
    }

    protected function hasBraces($token): bool
    {
        // Both "{" and "}"
        return isset($token['scope_opener'], $token['scope_closer']);
    }

    protected function detectScopeKeywordOnNewLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $prevContentPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($stackPtr - 1), null, true);

        $keywordLine = $token['line'];
        $prevContentLine = $tokens[$prevContentPtr]['line'];

        if ($keywordLine === $prevContentLine) {
            $error = 'Scope keyword "%s" should be on a new line';
            $data = [
                $token['content'],
            ];
            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'ScopeKeywordOnNewLine', $data);
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addNewlineBefore($stackPtr);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    protected function detectSpaceAfterScopeKeyword(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];
        $nextToken = $tokens[($stackPtr + 1)];

        $found = 1;
        if ($nextToken['code'] !== T_WHITESPACE) {
            $found = 0;
        } elseif ($nextToken['content'] !== ' ') { // if not only 1 space
            if (Str::contains($nextToken['content'], $phpcsFile->eolChar)) {
                $found = 'newline';
            } else {
                $found = Str::length($nextToken['content']);
            }
        }
        if ($found !== 1) {
            $error = 'Expected 1 space after scope keyword "%s", found "%s"';
            $data = [
                Str::upper($token['content']),
                $found,
            ];

            $fix = $phpcsFile->addFixableError($error, $stackPtr, 'SpaceAfterScopeKeyword', $data);
            if ($fix === true) {
                if ($found === 0) {
                    $phpcsFile->fixer->addContent($stackPtr, ' ');
                } else {
                    $phpcsFile->fixer->replaceToken(($stackPtr + 1), ' ');
                }
            }
        }
    }

    protected function detectOpeningBraceOnNewLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $openingBracePtr = $token['scope_opener']; // "{"
        $openingBraceLine = $tokens[$openingBracePtr]['line'];

        if (in_array($token['code'], [T_ELSE, T_TRY, T_DO], true)) {
            $keywordLine = $token['line'];
            // Measure from the scope opener.
            $lineDifference = ($openingBraceLine - $keywordLine);
        } else {
            $closingBracketLine = $tokens[$token['parenthesis_closer']]['line']; // ")"
            // Measure from the scope closing parenthesis.
            $lineDifference = ($openingBraceLine - $closingBracketLine);
        }

        if ($lineDifference !== 1) {
            $data = [
                $tokens[$openingBracePtr]['content'],
                $token['content'],
            ];
            if (isset($closingBracketLine) === true) {
                $error = 'Opening brace "%s" should be on a new line after "%s (...)"';
            } else {
                $error = 'Opening brace "%s" should be on a new line after the keyword "%s"';
            }

            $fix = $phpcsFile->addFixableError($error, $openingBracePtr, 'OpeningBraceOnNewLine', $data);
            if ($fix === true) {
                $prevContentPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($openingBracePtr - 1), null, true);
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($prevContentPtr + 1); $i < $openingBracePtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->addNewlineBefore($openingBracePtr);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    protected function detectClosingBraceOnNewLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $closingBracePtr = $token['scope_closer']; // "}"

        $prevContentPtr = $phpcsFile->findPrevious(T_WHITESPACE, ($closingBracePtr - 1), null, true);

        $closingBraceLine = $tokens[$closingBracePtr]['line'];
        $prevContentLine = $tokens[$prevContentPtr]['line'];

        $lineDifference = ($closingBraceLine - $prevContentLine);

        if ($lineDifference !== 1) {
            $data = [$tokens[$closingBracePtr]['content']];
            $error = 'Closing brace "%s" should be on a new line';
            $fix = $phpcsFile->addFixableError($error, $closingBracePtr, 'ClosingBraceOnNewLine', $data);
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($prevContentPtr + 1); $i < $closingBracePtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->addNewlineBefore($closingBracePtr);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    protected function detectClosingBracketAndOpeningBraceOnSameLine(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $token = $tokens[$stackPtr];

        $closingBracketPtr = $token['parenthesis_closer']; // ")"
        $openingBracePtr = $token['scope_opener']; // "{"

        $closingBracketLine = $tokens[$closingBracketPtr]['line'];
        $openingBraceLine = $tokens[$openingBracePtr]['line'];

        if ($closingBracketLine !== $openingBraceLine) {
            $error = 'Closing bracket "%s" and opening brace "%s" should be on same line';
            $data = [
                $tokens[$closingBracketPtr]['content'],
                $tokens[$openingBracePtr]['content'],
            ];
            $fix = $phpcsFile->addFixableError(
                $error,
                $openingBracePtr,
                'ClosingBracketAndOpeningBraceOnSameLine',
                $data
            );
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                for ($i = ($closingBracketPtr + 1); $i < $openingBracePtr; $i++) {
                    $phpcsFile->fixer->replaceToken($i, '');
                }
                $phpcsFile->fixer->addContent($closingBracketPtr, ' ');
                $phpcsFile->fixer->endChangeset();
            }
        }
    }

    protected function detectBreakIndentSameLevelAsSwitchCase(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $switch = $tokens[$stackPtr];
        $nextCase = $stackPtr;

        while (($nextCase = $this->findNextCase($phpcsFile, ($nextCase + 1), $switch['scope_closer'])) !== false) {
            $opener = $tokens[$nextCase]['scope_opener'];
            $nextCloser = $tokens[$nextCase]['scope_closer'];
            if (($tokens[$opener]['code'] === T_COLON) && $tokens[$nextCloser]['scope_condition'] === $nextCase) {
                $prev = $phpcsFile->findPrevious(T_WHITESPACE, ($nextCloser - 1), $nextCase, true);
                if ($tokens[$prev]['line'] !== $tokens[$nextCloser]['line']) {
                    $diff = ($tokens[$nextCase]['column'] - $tokens[$nextCloser]['column']);
                    if ($diff !== 0) {
                        $error = 'Terminating statement must be indented to the same level as the CASE';
                        $fix = $phpcsFile->addFixableError($error, $nextCloser, 'SwitchCaseBreakIndent');
                        if ($fix === true) {
                            if ($diff > 0) {
                                $phpcsFile->fixer->addContentBefore($nextCloser, str_repeat(' ', $diff));
                            } else {
                                $phpcsFile->fixer->substrToken(($nextCloser - 1), 0, $diff);
                            }
                        }
                    }
                }
            }
        }
    }

    private function findNextCase(File $phpcsFile, $stackPtr, $end)
    {
        $tokens = $phpcsFile->getTokens();
        while (($stackPtr = $phpcsFile->findNext([T_CASE, T_DEFAULT, T_SWITCH], $stackPtr, $end)) !== false) {
            // Skip nested SWITCH statements; they are handled on their own.
            if ($tokens[$stackPtr]['code'] === T_SWITCH) {
                $stackPtr = $tokens[$stackPtr]['scope_closer'];
                continue;
            }
            break;
        }
        return $stackPtr;
    }
}
