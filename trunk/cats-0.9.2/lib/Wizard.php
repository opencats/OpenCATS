<?php
/**
 * CATS
 * Wizard Library
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
 * @version    $Id: Wizard.php 3591 2007-11-13 17:20:07Z andrew $
 */

// FIXME: This is globally included...
include_once('./lib/CATSUtility.php');

/**
 *	Form Wizard Library
 *	@package    CATS
 *	@subpackage Library
 */
class Wizard
{
    /**
     * Initializes the session that the wizard module will use to
     * track where the user is, what the user has entered and
     * what they need to see.
     *
     * @param string $finishURL The URL the user is taken too upon completion
     * @param string $jsInclude A javascript file to include
     */
    public function __construct($finishURL = '', $jsInclude = '')
    {
        if (isset($_SESSION['CATS_WIZARD'])) unset($_SESSION['CATS_WIZARD']);

        // Initialize the session that will store information regarding the wizard
        $_SESSION['CATS_WIZARD'] = array(
            'pages' => array(),
            'curPage' => 1,
            'js' => $jsInclude,
            'finishURL' => $finishURL
        );
    }

    /**
     * Add a page to the wizard. Pages are displayed in the order they were
     * added.
     *
     * @param string $pageTitle Title of the wizard page
     * @param string $templateFile Template (tpl) file to display
     * @param string $phpEval PHP code to evaluate on page load
     * @param boolean $disableNext Disable the next button on load
     * @param boolean $disableSkip Disable the skip button on load
     * @return boolean true on success
     */
    public function addPage($pageTitle, $templateFile, $phpEval = '', $disableNext = false, $disableSkip = false)
    {
        $_SESSION['CATS_WIZARD']['pages'][] = array(
            'title' => $pageTitle,
            'php' => $phpEval,
            'template' => $templateFile,
            'disableNext' => $disableNext,
            'disableSkip' => $disableSkip
        );
        return true;
    }

    /**
     * Redirect the user to the wizard module that will display the interactive
     * wizard, eventually taking the user to the finishURL defined on
     * initialization.
     *
     * @return boolean true on success
     */
    public function doModal()
    {
        if (!isset($_SESSION['CATS_WIZARD']) || !count($_SESSION['CATS_WIZARD']['pages'])) return;
        CATSUtility::transferRelativeURI('m=wizard');
        return true;
    }
}

?>
