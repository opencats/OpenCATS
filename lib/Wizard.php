<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

// FIXME: This is globally included...
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');

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
