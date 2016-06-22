<?php
/**
 * CATS
 * Database Search Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: DatabaseSearch.php 3592 2007-11-13 17:30:46Z brian $
 */

/**
 *	Database Search Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class DatabaseSearch
{
    private $_simpleReplaceHash = array(
        '+' => '_rPLUSr',
        '#' => '_rPOUNDr',
        '&' => '_rANDr',
        '@' => '_rATr'
    );


    /**
     * Translates a traditional AND/OR/NOT boolean query string into a
     * Sphinx-compatible +/-/! query string.
     *
     * @param string input query
     * @return string translated boolean query
     */
    public static function humanToSphinxBoolean($string)
    {
        /* Remove double operators. */
        $regexSearch = array(
            '/\bAND(?:\s+AND)+\b/i',
            '/\bOR(?:\s+OR)+\b/i',
            '/\bAND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i'
        );
        $regexReplace = array(
            'AND',
            'OR',
            'NOT',
            'NOT',
            'NOT'
        );
        $string = preg_replace($regexSearch, $regexReplace, $string);

        /* Translate AND/OR/NOT to +/-/!. */
        $stringSearch  = array(' AND ', ' NOT ', ' OR ', ',');
        $stringReplace = array(' +',    ' !',    ' | ',  ' | ');
        $string = str_ireplace($stringSearch, $stringReplace,  $string);

        return $string;
    }

    /**
     * Makes a string searchable via REGEXP in a MySQL query.
     * note that it produces double slashes rather than single
     * slashes because mysql interprets backslashes twice, once
     * in SQL and 2nd time in REGEXP.
     *
     * @param string text to escape
     * @return string REGEXP parameter of an sql query
     */
    public static function makeREGEXPString($string)
    {
         /* FIXME: Test this! */
        $search  = array(
            '\\',   '+',   '.',   '*',   '(',   ')',   '[',   ']',   '?',   '^',   '$'
        );
        $replace = array(
            '\\\\', '\\+', '\\.', '\\*', '\\(', '\\)', '\\[', '\\]', '\\?', '\\^', '\\$'
        );

        return str_replace($search, $replace, $string);
    }

    /**
     * Changes commas and spaces (normally delimiters) in quoted string with
     * _QCOMMAQ_ and _QSPACEQ_ respectively.
     *
     * @param string text to escape
     * @return string marked up string
     */
    public static function markUpQuotes($string)
    {
        while (strpos($string, '"') !== false)
        {
            /* Find the first quote. */
            $quoteStart = strpos($string, '"');
            $string = substr_replace($string, '', $quoteStart, 1);

            /* Find the second quote; if there isn't one, break out. */
            $quoteEnd = strpos($string, '"');
            if ($quoteEnd === false)
            {
                break;
            }

            /* Remove the second quote. */
            $string = substr_replace($string, '', $quoteEnd, 1);

            /* Grab the string that was inside the quotes. */
            $quoted = substr($string, $quoteStart, $quoteEnd - 1);

            /* Mark up the string that was inside the quotes. */
            $quoted = str_replace(
                array(' ', ','), array('_QSPACEQ_', '_QCOMMAQ_'), $quoted
            );

            /* Replace the string that was inside the quotes with the marked-up string. */
            $string = trim(
                substr_replace($string, $quoted, $quoteStart, $quoteEnd - 1)
            );
        }

        return $string;
    }

    /**
     * Removes _QCOMMAQ_ and _QSPACEQ_ from a string that was 
     * created with markUpQuotes.
     *
     * @param string text to unescape
     * @return string marked up string
     */
    public static function unMarkUpQuotes($string)
    {
        return str_replace(
            array('_QSPACEQ_', '_QCOMMAQ_'), array(' ', ','), $string
        );
    }
    
    /**
     * Returns true if for every ) we don't have an (, or vice versa.
     *
     * @param string string to evaluate
     * @return boolean parenthesis are unmatched
     */
    public static function containsUnmatchedParenthesis($string)
    {
        /* Counters for open and close paranthesis. */
        $open  = 0;
        $close = 0;
        
        /* Loop through each character of the string and ensure all paranthesis
         * are matched.
         */
        $length = strlen($string);
        for ($i = 0; $i < $length; ++$i)
        {
            /* Open paranthesis. */
            if ($string[$i] == '(')
            {
                ++$open;
            }
            
            /* Close paranthesis. */
            if ($string[$i] == ')')
            {
                /* If we found a ')' without any unclosed '(' before it... */
                if ($open < 1)
                {
                    return true;
                }
                
                ++$close;
            }
        }
        
        /* If we don't have the same number of ('s as )'s, fail. */
        if ($open != $close)
        {
            return true;
        }
        
        return false;
    }

    /**
     * Parses a query string into a series of SQL statments.
     *
     * @param string Search query string.
     * @param DatabaseConnection Database connection object.
     * @param string Field name in query to search.
     * @return string SQL WHERE clause.
     */
    public static function makeBooleanSQLWhere($string, $databaseConnection,
        $tableField)
    {
        /* Empty string handling. This makes the query "WHERE 0", thus no
         * results are returned.
         */
        $string = trim($string);
        if (empty($string))
        {
            return '0';
        }

        /* Mark up quoted strings with filler characters (no white space). */
        $string = self::markUpQuotes($string);

        /* Clean up ()'s. */
        $string = preg_replace('/\(\s*\)/', '', $string);
        if (self::containsUnmatchedParenthesis($string))
        {
            return '0';
        }
        
        /* Add spaces to the input string to make things easier. */
        $string = ' ' . $string . ' ';

        /* Special character handling. */
        $stringSearch  = array(
            ' -',
            ' !',
            ',',
            '|',
            '(',
            ')',
            '%'
        );
        $stringReplace = array(
            ' NOT ',
            ' NOT ',
            ' OR ',
            ' OR ',
            ' OOOPENPARENTH ',
            ' CCCLOSEPARENTH ',
            ''
        );
        $string = str_replace($stringSearch, $stringReplace, $string);

        /* Remove double operators and filter query. */
        $regexSearch = array(
            '/\bAND(?:\s+AND)+\b/i',
            '/\b(?:AND|OR)(?:\s+OR)+\b/i',
            '/\bOR(?:\s+AND)+\b/i',
            '/\b(?:OR\s+)*AND\s+NOT(?:\s+AND\s+NOT)+\b/i',
            '/\bAND\s+NOT\b/i',
            '/\bNOT(?:\s+NOT)+\b/i',
            '/\bOR\s+NOT\b/i',
            '/\b(?:AND\s+)?NOT(?:\s+OR)+\b/i'
        );
        $regexReplace = array(
            'AND',
            'OR',
            'OR',
            'NOT',
            'NOT',
            'NOT',
            'NOT',
            ' '
        );
        $string = preg_replace($regexSearch, $regexReplace, $string);

        /* Clean up extra spaces. */
        while (strpos($string, '  ') !== false)
        {
            $string = str_replace('  ', ' ', $string);
        }

        /* Mark up symbols so we can search propely. */
        $string = self::makeREGEXPString($string);

        /* Make the string database safe. */
        $string = $databaseConnection->escapeString($string);

        /* Everything that is a symbol gets translated into something else. */
        $string = urlencode($string);
        $string = str_replace('%5C%5C%2A', '*', $string);
        $string = str_replace('%', 'PPPERCENTTT', $string);
        $string = urldecode($string);
        
        /* Convert normal boolean operators to shortened syntax. */
        /* Translate AND/OR/NOT to +/,/-. */
        $stringSearch  = array(' AND ', ' NOT ', ' OR ');
        $stringReplace = array(' +',    ' -',    ',');
        $string = str_ireplace($stringSearch, $stringReplace,  $string);

        /* Strip excessive whitespace. */
        $string = str_replace('OOOPENPARENTH', '(', $string);
        $string = str_replace('CCCLOSEPARENTH ', ')', $string);
        $string = str_replace('( ', '(', $string);
        $string = str_replace(' )', ')', $string);
        $string = str_replace(', ', ',', $string);
        $string = str_replace(' ,', ',', $string);
        $string = str_replace('- ', '-', $string);

        /* Mark-up words. */
        $string = preg_replace(
            '/([A-Za-z0-9_]+[A-Za-z0-9\._-]*)/',
            'word[(\'\\0\')]full',
            $string
        );

        /* Remove leading and trailing whitespace from $string. */
        $string = trim($string);

        /* Strip empty or erroneous atoms. */
        $string = str_replace('word[(\'\')]full', '', $string);
        $string = str_replace('word[(\'-\')]full', '-', $string);

        /* Add needed space. */
        $string = str_replace(')word[(', ') word[(', $string);
        $string = str_replace(')]full(', ')]full (', $string);

        /* Deal with asterisks. */
        $string = str_replace(')]full*', ')]wild ', $string);
        $string = str_replace('*word[(', 'wild[(', $string);
        $string = str_replace('*', '', $string);

        /* Clean up extra spaces again. */
        while (strpos($string, '  ') !== false)
        {
            $string = str_replace('  ', ' ', $string);
        }

        /* Dispatch symbols. */
        $string = str_replace(' ',  ' AND ', $string);
        $string = str_replace(',',  ' OR ', $string);
        $string = str_replace(' -', ' NOT ', $string);
        $string = preg_replace('/^-/', 'NOT ', $string);

        /* At this point:
         * in:  c++ and java or linux and not basic
         * out: word[('cPPPERCENTTT2BPPPERCENTTT2B')]full AND word[('java')]full OR word[('linux')]full NOT word[('basic')]full
         */

        $string = str_replace('PPPERCENTTT', '%', $string);
        $string = urldecode($string);

        /* Word searches. */
        $string = preg_replace(
            "/word\[\(\'([^\)]+)\'\)\]full/",
            '(' . $tableField . ' REGEXP \'[[:<:]]\\1[[:>:]]\')',
            $string
        );

        /* Wildcard searches. */
        $search = array(
            '/(?:word|wild)\[\(\'(.+?)\'\)\]wild/',
            '/wild\[\(\'(.+?)\'\)\]full/'
        );
        $string = preg_replace(
            $search, '(' . $tableField . ' LIKE \'%\\1%\')', $string
        );

        /* WHERE clauses cannot start with NOT. */
        if (preg_match('/^\s*NOT/i', $string))
        {
            return '0';
        }

        /* WHERE clauses cannot start with AND or OR. */
        $string = preg_replace('/^\s*(?:(?:AND|OR)\s+)+/', ' ', $string);
        
        /* WHERE clauses cannot end with AND or OR. */
        $string = preg_replace('/\s*(?:(?:AND|OR|NOT|AND\s+NOT)\s*)+$/', ' ', $string);

        /* Move around NOT. */
        $array = explode(' ', $string);
        $count = count($array);
        for ($i = 0; $i < ($count - 1); $i++)
        {
            if ($array[$i] == 'NOT' && isset($array[$i + 2]) &&
                trim($array[$i + 2]) == 'LIKE')
            {
                $array[$i] = $array[$i + 1];
                $array[$i + 1] = 'NOT';
                $i++;
            }
        }
        $string = implode(' ', $array);

        /* Make quoted strings work again. */
        $string = self::unMarkUpQuotes($string);

        /* Empty string handling. This makes the query "WHERE 0", thus no
         * results are returned.
         */
        $string = trim($string);
        if (empty($string))
        {
            return '0';
        }

        return '(' . $string . ')';
    }

    /**
     * Encodes a string of text so that MySQL 's FULLTEXT searching will
     * correctly operate on certain special characters. This should be run
     * before the text is INSERTed or UPDATEd in the database. When SELECTing
     * text from the field later on, use the fulltextDecode() function to
     * reverse the encoding.
     *
     * @param string text to encode
     * @return string encoded text
     */
    public function fulltextEncode($text)
    {
        $_simpleReplaceHash = array(
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr'
        );

        foreach ($_simpleReplaceHash as $find => $replace)
        {
            $text = str_replace($find, $replace, $text);
        }

        return preg_replace('/\.([^\s])/', '_rDOTr${1}', $text);
    }

    /**
     * Reverses the operations of fulltextEncode().
     *
     * @param string text to decode
     * @return string decoded text
     */
    public function fulltextDecode($text)
    {
        $_simpleReplaceHash = array(
            '+' => '_rPLUSr',
            '#' => '_rPOUNDr',
            '&' => '_rANDr',
            '@' => '_rATr'
        );

        foreach ($_simpleReplaceHash as $replace => $find)
        {
            $text = str_replace($find, $replace, $text);
        }

        return str_replace('_rDOTr', '.', $text);
    }
}

?>
