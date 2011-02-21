<?php
/**
 * CATS
 * Result Set Utility Library
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
 * @version    $Id: ResultSetUtility.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Result Set Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ResultSetUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Searches for a row where the specified column name has a value matching
     * the specified value. If a matching row is found, its index is returned;
     * otherwise, boolean false is returned.
     *
     * @param array result set to search
     * @param string column name in which to search
     * @param mixed value for which to search
     * @param integer number of matches to skip before returning a match
     * @return mixed integer row index if found, boolean false otherwise
     */
    public static function findRowByColumnValue($resultSet, $columnName,
        $value, $skip = 0)
    {
        /* If the column name doesn't exist, fail. */
        if (!isset($resultSet[0][$columnName]))
        {
            return false;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            if (isset($row[$columnName]) && $row[$columnName] == $value)
            {
                if ($skip > 0)
                {
                    --$skip;
                    continue;
                }

                return $rowIndex;
            }
        }

        return false;
    }

    /**
     * Searches for a row where the specified column name has a value matching
     * the specified value, using strict type matching. If a matching row is
     * found, its index is returned; otherwise, boolean false is returned.
     *
     * @param array result set to search
     * @param string column name in which to search
     * @param mixed value for which to search
     * @param integer number of matches to skip before returning a match
     * @return mixed integer row index if found, boolean false otherwise
     */
    public static function findRowByColumnValueStrict($resultSet, $columnName,
        $value, $skip = 0)
    {
        /* If the column name doesn't exist, fail. */
        if (!isset($resultSet[0][$columnName]))
        {
            return false;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            if (isset($row[$columnName]) && $row[$columnName] === $value)
            {
                if ($skip > 0)
                {
                    --$skip;
                    continue;
                }

                return $rowIndex;
            }
        }

        return false;
    }

    /**
     * Returns an array of only the values from the specified column from each
     * row in a two dimensional result-set array.
     *
     * Example:
     * $rs = array(
     *     0 => array('id' => 101, 'text' => 'blah1'),
     *     1 => array('id' => 244, 'text' => 'blah2'),
     *     2 => array('id' => 382, 'text' => 'blah3')
     * );
     *
     * getColumnValues($rs, 'id'); would return the following:
     *
     * Array
     * (
     *     [0] => 101
     *     [1] => 244
     *     [2] => 382
     * );
     *
     * @param array result set
     * @param string column name
     * @return array column values
     */
    public static function getColumnValues($resultSet, $columnName)
    {
        $outputArray = array();

        foreach ($resultSet as $value)
        {
            $outputArray[] = $value[$columnName];
        }

        return $outputArray;
    }

    /**
     * Returns the value of a specified column for a row with the specified ID
     * column value.
     *
     * Example:
     * $rs = array(
     *     0 => array('id' => 101, 'text' => 'blah1'),
     *     1 => array('id' => 244, 'text' => 'blah2'),
     *     2 => array('id' => 382, 'text' => 'blah3')
     * );
     *
     * getColumnValueByIDValue($rs, 'id', 244, 'text') would return 'blah2'.
     *
     * @param array result set
     * @param string ID column name
     * @param mixed ID column value
     * @param string return column name
     * @return mixed return column value
     */
    public static function getColumnValueByIDValue($resultSet, $IDColumnName,
        $IDValue, $returnColumnName)
    {
        /* If the ID column or return column name doesn't exist, fail. */
        if (!isset($resultSet[0][$IDColumnName]) ||
            !isset($resultSet[0][$returnColumnName]))
        {
            return false;
        }

        foreach ($resultSet as $rowIndex => $row)
        {
            /* If this isn't the row with the ID column value we're looking
             * for, fail.
             */
            if ($row[$IDColumnName] != $IDValue)
            {
                continue;
            }

            return $row[$returnColumnName];
        }

        return false;
    }

    /**
     * Sorts rows in a result set by the column specified.
     *
     * @param array result set
     * @param string sort-by column name
     * @return array sorted result set or false on failure
     */
    public static function sortByColumn($resultSet, $columnName)
    {
        /* If the sort-by column name doesn't exist, fail. */
        if (!isset($resultSet[0][$columnName]))
        {
            return false;
        }

        $sortArray = array();
        $outArray = array();

        foreach ($resultSet as $rowIndex => $row)
        {
            $sortArray[$rowIndex] = $row[$columnName];
        }

        asort($sortArray);

        foreach ($sortArray as $key => $value)
        {
            $outArray[$key] = $resultSet[$key];
        }

        return $outArray;
    }
}

?>
