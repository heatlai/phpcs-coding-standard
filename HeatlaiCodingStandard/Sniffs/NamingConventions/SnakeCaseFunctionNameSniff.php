<?php

namespace HeatlaiCodingStandard\Sniffs\NamingConventions;

use HeatlaiCodingStandard\Helpers\Str;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;
use PHP_CodeSniffer\Util\Tokens;

class SnakeCaseFunctionNameSniff extends AbstractScopeSniff
{
    public function __construct()
    {
        parent::__construct(Tokens::$ooScopeTokens, [T_FUNCTION], true);
    }

    /**
     * Processes the tokens within the scope.
     *
     * @param  File  $phpcsFile  The file being processed.
     * @param  int  $stackPtr  The position where this token was found.
     * @param  int  $currScope  The position of the current scope.
     *
     * @return void
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $tokens = $phpcsFile->getTokens();

        // Determine if this is a function which needs to be examined.
        $conditions = $tokens[$stackPtr]['conditions'];
        end($conditions);
        $deepestScope = key($conditions);
        if ($deepestScope !== $currScope) {
            return;
        }

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        if ($methodName === null) {
            // Ignore closures.
            return;
        }

        $className = $phpcsFile->getDeclarationName($currScope);
        if (isset($className) === false) {
            $className = '[Anonymous Class]';
        }

        $methodProps = $phpcsFile->getMethodProperties($stackPtr);
        $snakeMethodName = Str::snake($methodName);
        if ($methodName === $snakeMethodName) {
            $phpcsFile->recordMetric($stackPtr, 'snake_case method name', 'yes');
            return;
        }

        $snakeMethodName2 = Str::snake($methodName, '');
        if ($methodProps['scope_specified'] === true) {
            $error = '%s method name "%s" is not in snake_case format. try "%s" or "%s"';
            $data = [
                Str::ucfirst($methodProps['scope']),
                "{$className}::{$methodName}",
                $snakeMethodName,
                $snakeMethodName2
            ];
            $phpcsFile->addError($error, $stackPtr, 'ScopeFound', $data);
        } else {
            $error = 'Method name "%s" is not in snake_case format. try "%s" or "%s"';
            $data = [
                "{$className}::{$methodName}",
                $snakeMethodName,
                $snakeMethodName2
            ];
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }

        $phpcsFile->recordMetric($stackPtr, 'snake_case method name', 'no');
    }

    /**
     * Processes the tokens outside the scope.
     *
     * @param  File  $phpcsFile  The file being processed.
     * @param  int  $stackPtr  The position where this token was found.
     *
     * @return void
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
        $functionName = $phpcsFile->getDeclarationName($stackPtr);
        if ($functionName === null) {
            // Ignore closures.
            return;
        }

        $snakeName = Str::snake($functionName);
        if ($functionName === $snakeName) {
            $phpcsFile->recordMetric($stackPtr, 'snake_case function name', 'yes');
            return;
        }

        $error = 'Function name "%s" is not in snake_case format. try "%s" or "%s"';
        $data = [
            $functionName,
            $snakeName,
            Str::snake($functionName, '')
        ];
        $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        $phpcsFile->recordMetric($stackPtr, 'snake_case function name', 'no');
    }
}
