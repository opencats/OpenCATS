<?php
/**
 * CATS
 * Graph Interface Library
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
 * @version    $Id: Graphs.php 3814 2007-12-06 17:54:28Z brian $
 */

/**
 * Statistics library.
 */
include_once(LEGACY_ROOT . '/lib/Statistics.php');

/**
 *	Graph Interface Library
 *	@package    CATS
 *	@subpackage Library
 */
class Graphs
{
    private $_graphsEnabled;


    public function __construct()
    {
        if (function_exists('ImageCreateFromJpeg'))
        {
            $this->_graphsEnabled = true;
        }
        else
        {
            $this->_graphsEnabled = false;
        }
    }


    // FIXME: Document me.
    public static function getColorOptions()
    {
        return ['Black'           => [0, 0, 0], 'AlmostBlack'     => [48, 48, 48], 'VeryDarkGray'    => [88, 88, 88], 'DarkGray'        => [128, 128, 128], 'MidGray'         => [160, 160, 160], 'LightGray'       => [195, 195, 195], 'VeryLightGray'   => [220, 220, 220], 'White'           => [255, 255, 255], 'VeryDarkRed'     => [64, 0, 0], 'DarkRed'         => [128, 0, 0], 'MidRed'          => [192, 0, 0], 'Red'             => [255, 0, 0], 'LightRed'        => [255, 192, 192], 'VeryDarkGreen'   => [0, 64, 0], 'DarkGreen'       => [0, 128, 0], 'MidGreen'        => [0, 192, 0], 'Green'           => [0, 255, 0], 'LightGreen'      => [192, 255, 192], 'VeryDarkBlue'    => [0, 0, 64], 'DarkBlue'        => [0, 0, 128], 'MidBlue'         => [0, 0, 192], 'Blue'            => [0, 0, 255], 'LightBlue'       => [192, 192, 255], 'VeryDarkYellow'  => [64, 64, 0], 'DarkYellow'      => [128, 128, 0], 'MidYellow'       => [192, 192, 0], 'Yellow'          => [255, 255, 2], 'LightYellow'     => [255, 255, 192], 'VeryDarkCyan'    => [0, 64, 64], 'DarkCyan'        => [0, 128, 128], 'MidCyan'         => [0, 192, 192], 'Cyan'            => [0, 255, 255], 'LightCyan'       => [192, 255, 255], 'VeryDarkMagenta' => [64, 0, 64], 'DarkMagenta'     => [128, 0, 128], 'MidMagenta'      => [192, 0, 192], 'Magenta'         => [255, 0, 255], 'LightMagenta'    => [255, 192, 255], 'DarkOrange'      => [192, 88, 0], 'Orange'          => [255, 128, 0], 'LightOrange'     => [255, 168, 88], 'VeryLightOrange' => [255, 220, 168], 'DarkPink'        => [192, 0, 88], 'Pink'            => [255, 0, 128], 'LightPink'       => [255, 88, 168], 'VeryLightPink'   => [255, 168, 220], 'DarkPurple'      => [88, 0, 192], 'Purple'          => [128, 0, 255], 'LightPurple'     => [168, 88, 255], 'VeryLightPurple' => [220, 168, 255]];
    }

    // FIXME: Document me.
    public function activity($width, $height)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('activity', $width, $height);
    }

    // FIXME: Document me.
    public function newCandidates($width, $height)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('newCandidates', $width, $height);
    }

    // FIXME: Document me.
    public function newJobOrders($width, $height)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('newJobOrders', $width, $height);
    }

    // FIXME: Document me.
    public function newSubmissions($width, $height)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('newSubmissions', $width, $height);
    }

    // FIXME: Document me.
    public function miniPipeline($width, $height, $params)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('miniPipeline', $width, $height, $params);
    }

    // FIXME: Document me.
    public function miniJobOrderPipeline($width, $height, $params)
    {
        if (!$this->_graphsEnabled)
        {
             return '';
        }

        return $this->_getGraphHTML('miniJobOrderPipeline', $width, $height, $params, '#AAA 1px solid; float:right');
    }

    // FIXME: Document me.
    private function _getGraphHTML($graphName, $width, $height, $params = [], $borderStyle = "none")
    {
        $indexName = CATSUtility::getIndexName();

        $newWindowImage = sprintf(
            '%s?m=graphs&a=%s&width=640&height=480',
            $indexName,
            $graphName
        );

        $imageSRC = sprintf(
            '%s?m=graphs&amp;a=%s&amp;width=%s&amp;height=%s',
            $indexName,
            $graphName,
            $width,
            $height
        );

        if (!empty($params))
        {
            $parameterString = urlencode(implode(',', $params));
            $newWindowImage .= '&params=' . $parameterString;
            $imageSRC       .= '&amp;params=' . $parameterString;
        }

        return sprintf(
            '<a href="#" onclick="window.open(\'%s?m=reports&amp;a=graphView' .
            '&amp;theImage=%s\',\'fs\',\'fullscreen,scrollbars\');">' .
            '<img src="%s" style="border: %s; width:%s; height:%s;" width="%s" height="%s" alt="Graph" /></a>',
            $indexName,
            urlencode($newWindowImage),
            $imageSRC,
            $borderStyle,
            $width,
            $height,
            $width,
            $height
        );
    }

    // FIXME: Document me.
    public function verificationImage()
    {
        // FIXME: mt_rand()?!
        mt_srand((double) microtime() * 10000);
        $string = strtoupper(md5(random_int(0, 10000)));
        $verifyString = substr($string, 0, 6);

        /* Replace some numbers so all of the characters are quite obviousally
         * as they look.
         */
        $verifyString = str_replace('9', 'T', $verifyString);
        $verifyString = str_replace('6', 'Q', $verifyString);
        $verifyString = str_replace('5', 'R', $verifyString);
        $verifyString = str_replace('0', 'X', $verifyString);
        $verifyString = str_replace('1', 'P', $verifyString);

        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "INSERT INTO word_verification (
                word
             )
             VALUES (
                %s
             )",
             $db->makeQueryString($verifyString)
        );
        $db->query($sql);

        $wordVerifyID = $db->getLastInsertID();

        $HTML = '<img src="' . CATSUtility::getIndexName() . '?m=graphs&amp;a=wordVerify&amp;wordVerifyID=' . $wordVerifyID . '" alt="Graph" />';
        $HTML .= '<input type="hidden" name="wordVerifyID" id="wordVerifyID" value="' . $wordVerifyID . '" />';
        return $HTML;
    }

    // FIXME: Document me.
    public function getVerificationImageText($wordVerifyID)
    {
        $db = DatabaseConnection::getInstance();
        $sql = sprintf(
            "SELECT
                word AS word
             FROM
                word_verification
             WHERE
                word_verification_id = %s",
             $db->makeQueryInteger($wordVerifyID)
        );
        $rs = $db->getAssoc($sql);

        if (isset($rs['word']))
        {
            $text = $rs['word'];
        }
        else
        {
            $text = '';
        }

        return $text;
    }

    // FIXME: Document me.
    public function clearVerificationImageText($wordVerifyID)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "DELETE FROM
                word_verification
             WHERE
                word_verification_id = %s",
             $db->makeQueryInteger($wordVerifyID)
        );
        $db->query($sql);
    }
}

?>
