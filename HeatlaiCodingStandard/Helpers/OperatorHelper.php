<?php

namespace HeatlaiCodingStandard\Helpers;

use PHP_CodeSniffer\Util\Tokens;

/**
 * @internal
 */
class OperatorHelper
{
    /**
     * @param  array<int, array<string, array<int, int|string>|int|string>>  $tokens
     * @return array<int, array<string, array<int, int|string>|int|string>>
     */
    public static function getLeftSideTokens(array $tokens, int $comparisonTokenPointer): array
    {
        $parenthesisDepth = 0;
        $shortArrayDepth = 0;
        $examinedTokenPointer = $comparisonTokenPointer;
        $sideTokens = [];
        $stopTokenCodes = self::getStopTokenCodes();
        while (true) {
            $examinedTokenPointer--;
            $examinedToken = $tokens[$examinedTokenPointer];
            /** @var string|int $examinedTokenCode */
            $examinedTokenCode = $examinedToken['code'];
            if ($parenthesisDepth === 0 && $shortArrayDepth === 0 && isset($stopTokenCodes[$examinedTokenCode])) {
                break;
            }

            if ($examinedTokenCode === T_CLOSE_SHORT_ARRAY) {
                $shortArrayDepth++;
            } elseif ($examinedTokenCode === T_OPEN_SHORT_ARRAY) {
                if ($shortArrayDepth === 0) {
                    break;
                }

                $shortArrayDepth--;
            }

            if ($examinedTokenCode === T_CLOSE_PARENTHESIS) {
                $parenthesisDepth++;
            } elseif ($examinedTokenCode === T_OPEN_PARENTHESIS) {
                if ($parenthesisDepth === 0) {
                    break;
                }

                $parenthesisDepth--;
            }

            $sideTokens[$examinedTokenPointer] = $examinedToken;
        }

        return self::trimWhitespaceTokens(array_reverse($sideTokens, true));
    }

    /**
     * @param  array<int, array<string, array<int, int|string>|int|string>>  $tokens
     * @return array<int, array<string, array<int, int|string>|int|string>>
     */
    public static function getRightSideTokens(array $tokens, int $comparisonTokenPointer): array
    {
        $parenthesisDepth = 0;
        $shortArrayDepth = 0;
        $examinedTokenPointer = $comparisonTokenPointer;
        $sideTokens = [];
        $stopTokenCodes = self::getStopTokenCodes();
        while (true) {
            $examinedTokenPointer++;
            $examinedToken = $tokens[$examinedTokenPointer];
            /** @var string|int $examinedTokenCode */
            $examinedTokenCode = $examinedToken['code'];
            if ($parenthesisDepth === 0 && $shortArrayDepth === 0 && isset($stopTokenCodes[$examinedTokenCode])) {
                break;
            }

            if ($examinedTokenCode === T_OPEN_SHORT_ARRAY) {
                $shortArrayDepth++;
            } elseif ($examinedTokenCode === T_CLOSE_SHORT_ARRAY) {
                if ($shortArrayDepth === 0) {
                    break;
                }

                $shortArrayDepth--;
            }

            if ($examinedTokenCode === T_OPEN_PARENTHESIS) {
                $parenthesisDepth++;
            } elseif ($examinedTokenCode === T_CLOSE_PARENTHESIS) {
                if ($parenthesisDepth === 0) {
                    break;
                }

                $parenthesisDepth--;
            }

            $sideTokens[$examinedTokenPointer] = $examinedToken;
        }

        return self::trimWhitespaceTokens($sideTokens);
    }

    /**
     * @param  array<int, array<string, array<int, int|string>|int|string>>  $tokens
     * @return array<int, array<string, array<int, int|string>|int|string>>
     */
    public static function trimWhitespaceTokens(array $tokens): array
    {
        foreach ($tokens as $pointer => $token) {
            if ($token['code'] !== T_WHITESPACE) {
                break;
            }

            unset($tokens[$pointer]);
        }

        foreach (array_reverse($tokens, true) as $pointer => $token) {
            if ($token['code'] !== T_WHITESPACE) {
                break;
            }

            unset($tokens[$pointer]);
        }

        return $tokens;
    }

    /**
     * @return array<int|string, bool>
     */
    private static function getStopTokenCodes(): array
    {
        static $stopTokenCodes;

        if ($stopTokenCodes === null) {
            $stopTokenCodes = [
                T_BOOLEAN_AND => true,
                T_BOOLEAN_OR => true,
                T_SEMICOLON => true,
                T_OPEN_TAG => true,
                T_INLINE_THEN => true,
                T_INLINE_ELSE => true,
                T_LOGICAL_AND => true,
                T_LOGICAL_OR => true,
                T_LOGICAL_XOR => true,
                T_COALESCE => true,
                T_CASE => true,
                T_COLON => true,
                T_RETURN => true,
                T_COMMA => true,
                T_CLOSE_CURLY_BRACKET => true,
                T_MATCH_ARROW => true,
                T_FN_ARROW => true,
            ];

            $stopTokenCodes += array_fill_keys(array_keys(Tokens::$assignmentTokens), true);
            $stopTokenCodes += array_fill_keys(array_keys(Tokens::$commentTokens), true);
        }

        return $stopTokenCodes;
    }
}
