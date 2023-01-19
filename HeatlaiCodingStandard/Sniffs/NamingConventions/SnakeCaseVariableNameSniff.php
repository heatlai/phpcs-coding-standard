<?php

namespace HeatlaiCodingStandard\Sniffs\NamingConventions;

use HeatlaiCodingStandard\Helpers\Str;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class SnakeCaseVariableNameSniff implements Sniff
{
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
        if (! $this->isSnakeOrConstFormat($varName)) {
            $error = 'Variable "%s" is not in snake_case or CONST_NAME format.';
            $data = [$varName];
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }
    }

    private function isSnakeOrConstFormat($varName): bool
    {
        return (
            $varName === Str::snake($varName) // snake_case
            || $varName === Str::upper($varName) // CONST_FORMAT
        );
    }
}
