<?php
/*
   * OSATS
   * GNU License
   *
   *
   * @package    CATS
   * @subpackage Library
   * @copyright Open Source
   * @version    1.0
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
