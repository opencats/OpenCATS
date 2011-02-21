<?php
/*
 * CATS
 * Tests Module
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: CATSTestReporter.php 1948 2007-02-23 09:49:27Z will $
 */

class CATSTestReporter extends SimpleReporter
{
    public $showPasses = true;
    public $showFails = true;
    private $_microTimeStart;


    public function __construct($microTimeStart)
    {
        parent::__construct();

        $this->_microTimeStart = $microTimeStart;
    }


    public function paintHeader($testName)
    {
        if (!headers_sent())
        {
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
            header('Cache-Control: no-store, no-cache, must-revalidate');
            header('Cache-Control: post-check=0, pre-check=0', false);
            header('Pragma: no-cache');
        }

        $this->printHeader();

        echo '<table cellspacing="0" cellpadding="0">', "\n";
        echo '    <tr>', "\n";
        echo '        <td nowrap="nowrap">', "\n";

        $this->printHeaderBlock();

        echo '        <br clear="all" />', "\n";
        echo '        <br />', "\n";
        flush();
    }

    public function paintPass($message)
    {
        parent::paintPass($message);

        if (!$this->showPasses)
        {
            return;
        }

        echo '        <tr class="pass">', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="pass">Pass</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="test_case">', $this->_getTestCase(), '</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top">', $this->_htmlEntities($message), '</td>', "\n";
        echo '        </tr>', "\n";
        flush();
    }

    public function paintFail($message)
    {
        parent::paintFail($message);

        if (!$this->showFails)
        {
            return;
        }

        echo '        <tr class="fail">', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="fail">Fail</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="test_case">', $this->_getTestCase(), '</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top">', $this->_htmlEntities($message), '</td>', "\n";
        echo '        </tr>', "\n";
        flush();
    }

    public function paintError($message)
    {
        parent::paintError($message);

        echo '        <tr class="fail">', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="fail">Error</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top"><span class="test_case">', $this->_getTestCase(), '</span></td>', "\n";
        echo '            <td nowrap="nowrap" valign="top">', $this->_htmlEntities($message), '</td>', "\n";
        echo '        </tr>', "\n";
        flush();
    }

    public function paintGroupStart($testName, $size)
    {
        parent::paintGroupStart($testName, $size);
    }

    public function paintGroupEnd($testName)
    {
        parent::paintGroupEnd($testName);
    }

    public function paintCaseStart($testName)
    {
        parent::paintCaseStart($testName);

        echo '<table cellspacing="0" cellpadding="0">', "\n";
        echo '    <tr>', "\n";
        echo '        <td>', "\n";
        echo '            <p class="test_heading">', $testName, '</p>', "\n";
        echo '            <table cellspacing="0" class="test_output">', "\n";
        echo '                <tr>', "\n";
        echo '                    <th>Result</td>', "\n";
        echo '                    <th nowrap="nowrap">Test Case</td>', "\n";
        echo '                    <th>Message</td>', "\n";
        echo '                </tr>', "\n";
        flush();
    }

    public function paintCaseEnd($testName)
    {
        parent::paintCaseEnd($testName);

        echo '                </table>', "\n";
        echo '            <br clear="all" />', "\n";
        echo '        </td>', "\n";
        echo '    </tr>', "\n";
        echo '</table>', "\n";
    }

    public function paintFooter($testName)
    {
        parent::paintFooter($testName);

        $passes = $this->getPassCount();
        $fails  = $this->getFailCount();
        $errors = $this->getExceptionCount();

        $testsComplete = $this->getTestCaseProgress();
        $testsTotal    = $this->getTestCaseCount();

        if (($fails + $errors) > 0)
        {
            $id = 'footer_fail';
        }
        else
        {
            $id = 'footer_pass';
        }

        $microTimeArray = explode(' ', microtime());
        $executionTime  = (($microTimeArray[1] + $microTimeArray[0]) - $this->_microTimeStart);

        echo '<div id="', $id, '">';
        echo '    <span class="bold">', $testsComplete, '/', $testsTotal, '</span> test cases complete:', "\n";
        echo '    <span class="bold">', $passes, '</span> passes, ';
        echo '    <span class="bold">', $fails, '</span> fails and ';
        echo '    <span class="bold">', $errors, '</span> errors.';
        echo '    <br />';
        echo '    All tests completed in <span class="bold">', round($executionTime, 4), '</span> seconds.';
        echo '</div>', "\n";

        echo '                </td>', "\n";
        echo '            </tr>', "\n";
        echo '        </table>', "\n";
        echo '    </body>', "\n";
        echo '</html>', "\n";
    }

    public function paintFormattedMessage($message)
    {
        echo '<pre>' . $this->_htmlEntities($message) . '</pre>';
    }


    private function _htmlEntities($message)
    {
        return htmlentities($message, ENT_COMPAT, HTML_ENCODING);
    }


    private function _getTestCase()
    {
        $breadcrumb = $this->getTestList();

        array_shift($breadcrumb);
        array_shift($breadcrumb);

        $testCaseMethod = implode('::', $breadcrumb);

        return preg_replace('/^test/', '', $testCaseMethod);
    }

    public function printHeader($headIncludes = array())
    {
        echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"', "\n";
        echo '"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">', "\n";
        echo '<html>', "\n";
        echo '    <head>', "\n";
        echo '        <title>CATS - Tests</title>', "\n";
        echo '        <meta http-equiv="Content-Type" content="text/html; charset=', HTML_ENCODING, '">', "\n";
        echo '        <style type="text/css" media="all">@import "modules/tests/tests.css";</style>', "\n";

        foreach ($headIncludes as $key => $value)
        {
            echo '        <script type="text/javascript" src="', $value, '"></script>', "\n";
        }

        echo '    </head>', "\n";
        echo '    <body>', "\n";
    }

    public function printHeaderBlock()
    {
        TemplateUtility::printHeaderBlock(false);
    }
}

?>
