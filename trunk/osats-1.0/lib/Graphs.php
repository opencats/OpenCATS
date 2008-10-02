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
include_once('./lib/Statistics.php');

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
        return array(
            'Black'           => array(0, 0, 0),
            'AlmostBlack'     => array(48, 48, 48),
            'VeryDarkGray'    => array(88, 88, 88),
            'DarkGray'        => array(128, 128, 128),
            'MidGray'         => array(160, 160, 160),
            'LightGray'       => array(195, 195, 195),
            'VeryLightGray'   => array(220, 220, 220),
            'White'           => array(255, 255, 255),
            'VeryDarkRed'     => array(64, 0, 0),
            'DarkRed'         => array(128, 0, 0),
            'MidRed'          => array(192, 0, 0),
            'Red'             => array(255, 0, 0),
            'LightRed'        => array(255, 192, 192),
            'VeryDarkGreen'   => array(0, 64, 0),
            'DarkGreen'       => array(0, 128, 0),
            'MidGreen'        => array(0, 192, 0),
            'Green'           => array(0, 255, 0),
            'LightGreen'      => array(192, 255, 192),
            'VeryDarkBlue'    => array(0, 0, 64),
            'DarkBlue'        => array(0, 0, 128),
            'MidBlue'         => array(0, 0, 192),
            'Blue'            => array(0, 0, 255),
            'LightBlue'       => array(192, 192, 255),
            'VeryDarkYellow'  => array(64, 64, 0),
            'DarkYellow'      => array(128, 128, 0),
            'MidYellow'       => array(192, 192, 0),
            'Yellow'          => array(255, 255, 2),
            'LightYellow'     => array(255, 255, 192),
            'VeryDarkCyan'    => array(0, 64, 64),
            'DarkCyan'        => array(0, 128, 128),
            'MidCyan'         => array(0, 192, 192),
            'Cyan'            => array(0, 255, 255),
            'LightCyan'       => array(192, 255, 255),
            'VeryDarkMagenta' => array(64, 0, 64),
            'DarkMagenta'     => array(128, 0, 128),
            'MidMagenta'      => array(192, 0, 192),
            'Magenta'         => array(255, 0, 255),
            'LightMagenta'    => array(255, 192, 255),
            'DarkOrange'      => array(192, 88, 0),
            'Orange'          => array(255, 128, 0),
            'LightOrange'     => array(255, 168, 88),
            'VeryLightOrange' => array(255, 220, 168),
            'DarkPink'        => array(192, 0, 88),
            'Pink'            => array(255, 0, 128),
            'LightPink'       => array(255, 88, 168),
            'VeryLightPink'   => array(255, 168, 220),
            'DarkPurple'      => array(88, 0, 192),
            'Purple'          => array(128, 0, 255),
            'LightPurple'     => array(168, 88, 255),
            'VeryLightPurple' => array(220, 168, 255)
        );
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
    private function _getGraphHTML($graphName, $width, $height, $params = array(), $borderStyle = "none")
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
        srand((double) microtime() * 10000);
        $string = strtoupper(md5(rand(0, 10000)));
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
