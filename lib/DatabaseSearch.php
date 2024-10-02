<?php
declare(strict_types=1);

/**
 * OPENCATS
 * Database Search Library
 *
 * @package    OPENCATS
 * @subpackage Library
 */

class DatabaseSearch
{
    private array $_simpleReplaceHash = [
        '+' => '_rPLUSr',
        '#' => '_rPOUNDr',
        '&' => '_rANDr',
        '@' => '_rATr',
    ];

    /**
     * Translates a traditional AND/OR/NOT boolean query string into a
     * Sphinx-compatible +/-/! query string.
     *
     * @param string $string Input query
     * @return string Translated boolean query
     */
    public static function humanToSphinxBoolean(string $string): string
    {
        /* Remove double operators. */
        $regexSearch = [
            '/\bAND(?:\s+AND)+\b/i',
            '/\bOR(?:\s+OR)+\b/i',
            '/\bAND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i',
        ];
        $regexReplace = [
            'AND',
            'OR',
            'NOT',
            'NOT',
            'NOT',
        ];
        $string = preg_replace($regexSearch, $regexReplace, $string);

        /* Translate AND/OR/NOT to +/-/!. */
        $stringSearch = [' AND ', ' NOT ', ' OR ', ','];
        $stringReplace = [' +', ' !', ' | ', ' | '];
        $string = str_ireplace($stringSearch, $stringReplace, $string);

        return $string;
    }

    /**
     * Makes a string searchable via REGEXP in a MySQL query.
     * This adds double slashes for proper MySQL handling.
     *
     * @param string $string Text to escape
     * @return string REGEXP parameter for SQL query
     */
    public static function makeREGEXPString($string)
    {
        // Escape special characters that should be treated literally in the regex
        $search = [
            '\\',   '+',   '.',   '(',   ')',   '[',   ']',   '?',   '^',   '$',
        ];
        $replace = [
            '\\\\', '\\+', '\\.', '\\(', '\\)', '\\[', '\\]', '\\?', '\\^', '\\$',
        ];
        $string = str_replace($search, $replace, $string);

        // Handle wildcard '*' conversion to '.*'
        if (strpos($string, '*') !== false) {
            $string = str_replace('*', '.*', $string);
            return $string;
        }

        // Split the string if it contains '|', handle each part separately
        if (strpos($string, '|') !== false) {
            $parts = explode('|', $string);
            foreach ($parts as &$part) {
                $part = '[[:<:]]' . trim($part) . '[[:>:]]';
            }
            return implode('|', $parts);
        }

        // Add word boundaries if there is no wildcard or '|' operator
        return '[[:<:]]' . $string . '[[:>:]]';
    }





    /**
     * Changes commas and spaces in quoted strings with special markers.
     *
     * @param string $string Text to escape
     * @return string Marked-up string
     */
    public static function markUpQuotes(string $string): string
    {
        while (strpos($string, '"') !== false) {
            $quoteStart = strpos($string, '"');
            $string = substr_replace($string, '', $quoteStart, 1);

            $quoteEnd = strpos($string, '"');
            if ($quoteEnd === false) {
                break;
            }

            $string = substr_replace($string, '', $quoteEnd, 1);

            $quoted = substr($string, $quoteStart, $quoteEnd - 1);
            $quoted = str_replace([' ', ','], ['_QSPACEQ_', '_QCOMMAQ_'], $quoted);

            $string = trim(substr_replace($string, $quoted, $quoteStart, $quoteEnd - 1));
        }

        return $string;
    }

    /**
     * Reverses the mark-up created by markUpQuotes().
     *
     * @param string $string Text to unescape
     * @return string Unmarked string
     */
    public static function unMarkUpQuotes(string $string): string
    {
        return str_replace(
            ['_QSPACEQ_', '_QCOMMAQ_'],
            [' ', ','],
            $string
        );
    }

    /**
     * Checks if a string contains unmatched parentheses.
     *
     * @param string $string String to evaluate
     * @return bool True if parentheses are unmatched, false otherwise
     */
    public static function containsUnmatchedParenthesis(string $string): bool
    {
        $open = substr_count($string, '(');
        $close = substr_count($string, ')');

        return $open !== $close;
    }

    /**
     * Parses a query string into a series of SQL statements.
     *
     * @param string $string Search query string
     * @param DatabaseConnection $databaseConnection Database connection object
     * @param string $tableField Field name to search
     * @return string SQL WHERE clause
     */
    public static function makeBooleanSQLWhere(
        string $string,
        $databaseConnection,
        string $tableField
    ): string {
        $string = trim($string);
        if (empty($string)) {
            return '0';
        }

        $string = self::markUpQuotes($string);
        $string = preg_replace('/\(\s*\)/', '', $string);

        if (self::containsUnmatchedParenthesis($string)) {
            return '0';
        }

        $stringSearch = [
            ' -', ' !', ',', '|', '(', ')', '%',
        ];
        $stringReplace = [
            ' NOT ', ' NOT ', ' OR ', ' OR ', ' OOOPENPARENTH ', ' CCCLOSEPARENTH ', '',
        ];
        $string = str_replace($stringSearch, $stringReplace, $string);

        $regexSearch = [
            '/\bAND(?:\s+AND)+\b/i',
            '/\b(?:AND|OR)(?:\s+OR)+\b/i',
            '/\bOR(?:\s+AND)+\b/i',
            '/\b(?:OR\s+)*AND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bOR\s+NOT\b/i',
            '/\b(?:AND\s+)?NOT(?:\s+OR)+\b/i',
        ];
        $regexReplace = [
            'AND', 'OR', 'OR', 'NOT', 'NOT', 'NOT', 'NOT', ' ',
        ];
        $string = preg_replace($regexSearch, $regexReplace, $string);

        while (strpos($string, '  ') !== false) {
            $string = str_replace('  ', ' ', $string);
        }

        // Use makeREGEXPString to create the regex for search term
        $string = self::makeREGEXPString($string);
        $string = $databaseConnection->escapeString($string);

        // Directly construct the REGEXP clause without adding boundaries around the field name
        $string = preg_replace(
            "/([A-Za-z0-9_]+[A-Za-z0-9\._-]*)/",
                               '(' . $tableField . ' REGEXP \'\\1\')',
                               $string
        );

        $string = trim($string);
        if (empty($string)) {
            return '0';
        }

        return $string;
    }


    /**
     * Encodes a string of text for MySQL FULLTEXT searching.
     *
     * @param string $text Text to encode
     * @return string Encoded text
     */
    public static function fulltextEncode(string $text): string
    {
        $_simpleReplaceHash = [
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr',
        ];

        foreach ($_simpleReplaceHash as $find => $replace) {
            $text = str_replace($find, $replace, $text);
        }

        return preg_replace('/\.([^\s])/', '_rDOTr${1}', $text);
    }

    /**
     * Decodes a string encoded with fulltextEncode().
     *
     * @param string $text Text to decode
     * @return string Decoded text
     */
    public static function fulltextDecode(string $text): string
    {
        $_simpleReplaceHash = [
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr',
        ];

        foreach ($_simpleReplaceHash as $replace => $find) {
            $text = str_replace($find, $replace, $text);
        }

        return str_replace('_rDOTr', '.', $text);
    }
}
