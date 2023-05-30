<?php

namespace HeatlaiCodingStandard\Sniffs\NamingConventions;

use HeatlaiCodingStandard\Helpers\Str;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class SnakeCaseVariableNameSniff implements Sniff
{
    protected $phpReservedVars = [
        '$_SERVER' => true,
        '$_GET' => true,
        '$_POST' => true,
        '$_REQUEST' => true,
        '$_SESSION' => true,
        '$_ENV' => true,
        '$_COOKIE' => true,
        '$_FILES' => true,
        '$GLOBALS' => true,
    ];

    public function register()
    {
        return [
            T_VARIABLE,
        ];
    }

    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $varName = trim($tokens[$stackPtr]['content']);

        // If it's a php reserved var, then it's ok.
        if (isset($this->phpReservedVars[$varName]) === true) {
            return;
        }

        $snakeVarName = Str::snake($varName);

        if ($varName === $snakeVarName) {
            $phpcsFile->recordMetric($stackPtr, 'snake_case variable name', 'yes');
            return;
        }

        $error = 'Variable "%s" is not in snake_case format. try "%s" or "%s"';
        $data = [
            $varName,
            $snakeVarName,
            Str::snake($varName, '')
        ];
        $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        $phpcsFile->recordMetric($stackPtr, 'snake_case variable name', 'no');
    }
}
