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
 * @version    $Id: ArrayUtility.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Array Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ArrayUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Works like implode(), but only includes elements from (and including)
     * the first offset to (and including) the last offset.
     *
     * @param string "Glue" used to join $pieces.
     * @param array Pieces to join.
     * @param integer First index of pieces in range to join
     * @param integer Last index of pieces in range to join
     * @return string Imploded string.
     */
    public static function implodeRange($glue, $pieces, $firstOffset, $lastOffset)
    {
        $slicedArray = array();

        /* Get the last offset of $pieces). */
        $lastPiecesOffset = count($pieces) - 1;

        /* If the last index the user wants is not the last index of the array,
         * we need to slice the array from both sides.
         */
        if ($lastPiecesOffset > $lastOffset)
        {
            /* Convert the user's "last index" into the "number of elements"
             * array_slice() wants.
             */
            $limit = ($lastOffset - $firstOffset) + 1;

            /* Slice the array. */
            $slicedArray = array_slice($pieces, $firstOffset, $limit);
        }
        else
        {
            /* Slice the array. */
            $slicedArray = array_slice($pieces, $firstOffset);
        }

        /* Join the array and return it. */
        return implode($glue, $slicedArray);
    }

    /**
     * Works like array_map, but executes the provided function on
     * each key rather than each value.
     *
     * FIXME: Make this work for method calls!
     *
     * @param string Function name to execute.
     * @param array Array to process.
     * @return array Processed array.
     */    
    public static function arrayMapKeys($function, $array)
    {
        $returnArray = array();
        
        foreach ($array as $index => $data)
        {
            $returnArray[eval('return ' . $function . '($index);')] = $data;
        }
        
        return $returnArray;
    }
}

?>
