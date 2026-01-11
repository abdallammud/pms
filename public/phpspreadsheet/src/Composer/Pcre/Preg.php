<?php
/**
 * Composer PCRE Preg stub
 * Minimal implementation to satisfy PHPSpreadsheet dependencies
 * Uses native PHP preg functions
 */

namespace Composer\Pcre;

class Preg
{
    /**
     * Perform a regular expression search and replace.
     *
     * @param string|array $pattern
     * @param string|array $replacement
     * @param string $subject
     * @param int $limit
     * @return string
     */
    public static function replace($pattern, $replacement, string $subject, int $limit = -1): string
    {
        $result = preg_replace($pattern, $replacement, $subject, $limit);
        return $result ?? $subject;
    }

    /**
     * Perform a regular expression match.
     *
     * @param string $pattern
     * @param string $subject
     * @param array|null $matches
     * @param int $flags
     * @param int|null $offset
     * @return int
     */
    public static function match(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, ?int $offset = 0): int
    {
        $result = preg_match($pattern, $subject, $matches, $flags, $offset ?? 0);
        return $result ?: 0;
    }

    /**
     * Perform a global regular expression match.
     *
     * @param string $pattern
     * @param string $subject
     * @param array|null $matches
     * @param int $flags
     * @param int|null $offset
     * @return int
     */
    public static function matchAll(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, ?int $offset = 0): int
    {
        $result = preg_match_all($pattern, $subject, $matches, $flags, $offset ?? 0);
        return $result ?: 0;
    }

    /**
     * Check if a regular expression matches.
     *
     * @param string $pattern
     * @param string $subject
     * @param array|null $matches
     * @param int $flags
     * @param int|null $offset
     * @return bool
     */
    public static function isMatch(string $pattern, string $subject, ?array &$matches = null, int $flags = 0, ?int $offset = 0): bool
    {
        return (bool) preg_match($pattern, $subject, $matches, $flags, $offset ?? 0);
    }
}
