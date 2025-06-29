<?php

namespace Nandan108\PropAccess\Support;

/**
 * @api
 */
final class CaseConverter
{
    /**
     * Breaks an identifier into normalized lowercase words,
     * then applies optional transformations to each word.
     * Returns the words joined by $separator.
     *
     * @param string     $str        The input string
     * @param callable[] $transforms Callable(s) to apply to each word (e.g. ['strtolower', 'ucfirst'])
     * @param string     $separator  The separator to use when joining the words
     *
     * @return string The final transformed string
     */
    public static function normalizeWords(string $str, array $transforms, $separator): string
    {
        // Step 1a: remove non-alphabetic characters and replace them with underscores
        $str = preg_replace('/[^a-z]+/i', '_', $str) ?? '';
        // Step 1b: split the string into words
        /** @var string[] $words */
        $words = preg_split('/_|(?<=[a-z])(?=[A-Z])/', $str);

        // Step 2.b: apply transformations
        foreach ($transforms as $fn) {
            /** @var array<string> */
            $words = array_map($fn, $words);
        }

        // Step 3: return words joined with the specified separator
        return implode($separator, $words);
    }

    /**
     * Converts a string to camelCase.
     *
     * @param string $str The input string
     *
     * @return string The camelCase version of the input string
     */
    public static function toCamel(string $str): string
    {
        return lcfirst(self::normalizeWords($str, ['\strtolower', '\ucfirst'], ''));
    }

    /**
     * Converts a string to PascalCase.
     *
     * @param string $str The input string
     *
     * @return string The PascalCase version of the input string
     */
    public static function toPascal(string $str): string
    {
        return self::normalizeWords($str, ['\strtolower', '\ucfirst'], '');
    }

    /**
     * Converts a string to snake_case.
     *
     * @param string $str The input string
     *
     * @return string The snake_case version of the input string
     */
    public static function toSnake(string $str): string
    {
        return self::normalizeWords($str, ['strtolower'], '_');
    }

    /**
     * Converts a string to kebab-case.
     *
     * @param string $str The input string
     *
     * @return string The kebab-case version of the input string
     */
    public static function toKebab(string $str): string
    {
        return self::normalizeWords($str, ['strtolower'], '-');
    }

    /**
     * Converts a string to UPPER_SNAKE_CASE.
     *
     * @param string $str The input string
     *
     * @psalm-suppress PossiblyUnusedMethod
     *
     * @return string The UPPER_SNAKE_CASE version of the input string
     */
    public static function toUpperSnake(string $str): string
    {
        return self::normalizeWords($str, ['strtoupper'], '_');
    }

    /**
     * Converts a string to a specified case format.
     *
     * @param string $case one of 'camel', 'pascal', 'snake', 'kebab', 'upper_snake'
     * @param string $str  the string to convert
     *
     * @throws \InvalidArgumentException if the case is unknown
     *
     * @psalm-suppress PossiblyUnusedMethod
     */
    public static function to(string $case, string $str): string
    {
        return match ($case) {
            'camel'       => self::toCamel($str),
            'pascal'      => self::toPascal($str),
            'snake'       => self::toSnake($str),
            'kebab'       => self::toKebab($str),
            'upper_snake' => self::toUpperSnake($str),
            default       => throw new \InvalidArgumentException("Unknown case: $case"),
        };
    }
}
