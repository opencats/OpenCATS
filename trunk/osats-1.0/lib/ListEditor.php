<?php
/**
 * CATS
 * Array Utility Library
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
 * @version    $Id: ListEditor.php 3596 2007-11-13 17:43:03Z brian $
 */

define('LIST_EDITOR_UNKNOWN', -1);
define('LIST_EDITOR_UNCHANGED', 0);
define('LIST_EDITOR_ADD', 1);
define('LIST_EDITOR_REMOVE', 2);
define('LIST_EDITOR_MODIFY', 3);

include_once('./lib/StringUtility.php');


/**
 *	DHTML List Editor Library
 *	@package    CATS
 *	@subpackage Library
 */
class ListEditor
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Returns an array of values from CSV list.
     * // FIXME: Clean this up.
     *
     * @param string CSV list
     * @return array values
     */
    public static function getArrayVaulesfromCSV($string)
    {
        $string .= '';
        $string = trim($string);

        if (empty($string))
        {
            return array();
        }

        $string = str_replace('""', '!!DOUBLEQUOTE!!', $string);
        $string = str_replace('^', '!!EXPONENT!!', $string);

        while (strpos($string, '"') !== false)
        {
            $pos = strpos($string, '"');
            $string = StringUtility::JSSubString($string, 0, $pos) . '^'
                . StringUtility::JSSubString($string, ($pos + 1), strlen($string));

            $pos2 = strpos($string, '"');
            if ($pos2 !== false)
            {
                $string = StringUtility::JSSubString($string, 0, $pos) . '^'
                    . StringUtility::JSSubString($string, ($pos + 1), strlen($string));

                $stringSub = StringUtility::JSSubString($string, $pos + 1, $pos2);

                $stringSub = str_replace(',', '!!COMMA!!', $stringSub);

                $string = StringUtility::JSSubString($string, 0, $pos)
                    . '^' . $stringSub . '^'
                    . StringUtility::JSSubString($string, ($pos2 + 1), strlen($string));
            }
        }

        $string = str_replace('^', '', $string);

        $tArray = explode(',', $string);

        for ($i = 0; $i < count($tArray); $i++)
        {
            $tArray[$i] = str_replace('!!DOUBLEQUOTE!!', '"', $tArray[$i]);
            $tArray[$i] = str_replace('!!EXPONENT!!', '^', $tArray[$i]);
            $tArray[$i] = str_replace('!!COMMA!!', ',', $tArray[$i]);
        }

        return $tArray;
    }
    
    /**
     * Returns an CSV list from a 2 dimensional array with parameter 2 being
     * the index value for dimension 2.
     *
     * @param array response
     * @param string response array index
     * @return string CSV data
     */
    public static function getStringFromList($rs, $index)
    {
        if (empty($rs) || $rs == -1)
        {
            return '';
        }

        $output = '';
        foreach ($rs as $rsIndex => $rsEntry)
        {
            $string = '"' . str_replace('"', '""', $rsEntry[$index]) . '"';
            if ($rsIndex != count($rs) - 1)
            {
                $output .= $string . ',';
            }
            else
            {
                $output .= $string;
            }
        }

        return $output;
    }


    /**
     * Returns an array of the 'add' values from a listEditor.js array.
     *
     * @param listEditor array
     * @return listEditor array
     */
    public static function getAddValues($theArray)
    {
        $theArrayValues = array();

        for ($i = 0; $i < count($theArray); $i++)
        {
            if (strpos($theArray[$i], '!!EDIT!!') === false)
            {
                $theArrayValues[] = $theArray[$i];
            }
        }

        return $theArrayValues;
    }

    /**
     * Returns an array of the 'edit' values from a listEditor.js array.
     *
     * @param listEditor array
     * @return listEditor array
     */
    public static function getEditValues($theArray)
    {
        $theArrayValues = array();

        for ($i = 0; $i < count($theArray); $i++)
        {
            if (strpos($theArray[$i], '!!EDIT!!') === 0)
            {
                $from = StringUtility::JSSubString(
                    $theArray[$i],
                    8,
                    strpos($theArray[$i], '!!INTO!!')
                );
                $into = StringUtility::JSSubString(
                    $theArray[$i],
                    strpos($theArray[$i], '!!INTO!!') + 8,
                    strlen($theArray[$i])
                );
                $theArrayValues[] = array($from, $into);
            }
        }

        return $theArrayValues;
    }

    /**
     * Returns an array containing differences from the Original list.
     *
     * Input:  Array containing a list (database response),
     *         Field containing name in array,
     *         Field containing index in array,
     *         String returned from listEditor.js
     * Output: Array containing a name,
     *         ID (not set for LIST_EDITOR_ADD),
     *         action.
     * Actions could be:
     *       LIST_EDITOR_UNCHANGED
     *       LIST_EDITOR_ADD
     *       LIST_EDITOR_REMOVE
     *       LIST_EDITOR_MODIFY
     */
    public static function getDifferencesFromList($rsOriginal,
        $rsFieldNameOriginal, $rsFieldIndexOriginal, $stringListEditor)
    {
        /* Safeguard:  Do not delete anything unless we KNOW that the user did a delete. */
        $allowDelete = false;
        
        if (strpos($stringListEditor, '&DELETEALLOWED&') !== false)
        {
            $allowDelete = true;
            $stringListEditor = substr($stringListEditor, 0, strpos($stringListEditor, '&DELETEALLOWED&'));
        }
        
        $arrayDiff = array();

        $values = self::getArrayVaulesfromCSV($stringListEditor);
        $addValues = self::getAddValues($values);
        $editValues = self::getEditValues($values);

        if ($rsOriginal != -1)
        {
            foreach ($rsOriginal as $rsLine)
            {
                   $arrayDiff[] = array(
                    $rsLine[$rsFieldNameOriginal],
                    $rsLine[$rsFieldIndexOriginal],
                    LIST_EDITOR_UNKNOWN
                );
            }
        }

        foreach ($editValues as $editLine)
        {
            for ($i = 0; $i < count($arrayDiff); $i++)
            {
                if ($arrayDiff[$i][0] == $editLine[0])
                {
                    $arrayDiff[$i][0] = $editLine[1];
                    $arrayDiff[$i][2] = LIST_EDITOR_MODIFY;
                }
            }
        }

        foreach ($addValues as $addLine)
        {
            $foundValue = false;
            for ($i = 0; $i < count($arrayDiff); $i++)
            {
                if ($arrayDiff[$i][0] == $addLine)
                {
                    $foundValue = true;
                    if ($arrayDiff[$i][2] == LIST_EDITOR_UNKNOWN)
                    {
                        $arrayDiff[$i][2] = LIST_EDITOR_UNCHANGED;
                    }
                }
            }
            if (!$foundValue)
            {
                $arrayDiff[] = array($addLine, 0, LIST_EDITOR_ADD);
            }
        }

        foreach ($arrayDiff as $arrayDiffIndex => $arrayDiffLine)
        {
            if ($arrayDiffLine[2] == LIST_EDITOR_UNKNOWN)
            {
                /* Safeguard:  Do not delete anything unless we KNOW that the user did a delete. */
                if ($allowDelete == true)
                {
                    $arrayDiff[$arrayDiffIndex][2] = LIST_EDITOR_REMOVE;
                }
                else
                {
                    $arrayDiff[$arrayDiffIndex][2] = LIST_EDITOR_UNCHANGED;
                }
            }
        }

        return $arrayDiff;
    }
}

?>
