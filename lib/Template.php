<?php
/**
 * CATS
 * Template Library
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
 * @version    $Id: Template.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Template Library
 *	@package    CATS
 *	@subpackage Library
 */
class Template
{
    private $_templateFile;
    private $_filters = array();

    /**
     * Prints $string with all html special characters converted to &codes;.
     *
     * Ex: 'If x < 2 & x > 0, x = 1.' -> 'If x &lt; 2 &amp; x &gt; 0, x = 1.'.
     *
     * @param string input
     * @return void
     */
    public function _($string)
    {
        echo(htmlspecialchars($string));
    }

    /**
     * Assigns the specified property value to the specified property name
     * for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assign($propertyName, $propertyValue)
    {
        $this->$propertyName = $propertyValue;
    }

    /**
     * Assigns the specified property value to the specified property name,
     * by reference, for access within the template.
     *
     * @param string property name
     * @param mixed property value
     * @return void
     */
    public function assignByReference($propertyName, &$propertyValue)
    {
        $this->$propertyName =& $propertyValue;
    }

    /**
     *  TODO: Document me.
     */
    public function addFilter($code)
    {
        $this->_filters[] = $code;
    }

    /**
     * Evaluates a template file. All assignments (see the Template::assign()
     * and Template::assignByReference() methods) must be made before calling
     * this method. The template filename is relative to index.php.
     *
     * @param string template filename
     * @return void
     */
    public function display($template)
    {
        /* File existence checking. */
        $file = realpath('./' . $template);
        if (!$file)
        {
            echo 'Template error: File \'', $template, '\' not found.', "\n\n";
            return;
        }

        $this->_templateFile = $file;

        /* We don't want any variable name conflicts here. */
        unset($file, $template);

        /* Include the template, with output buffering on, and echo it. */
        ob_start();
        include($this->_templateFile);
        $html = ob_get_clean();

        if (strpos($html, '<!-- NOSPACEFILTER -->') === false && strpos($html, 'textarea') === false)
        {
            $html = preg_replace('/^\s+/m', '', $html);
        }

        foreach ($this->_filters as $filter)
        {
            eval($filter);
        }

        echo($html);
    }

    /**
     * Returns access level of logged in user for securedObject
     * Intended to be used in tpl classes to check if user has acces to particular part of page and if shall be generated or not
     */
    protected function getUserAccessLevel($securedObjectName)
    {
        return $_SESSION['CATS']->getAccessLevel($securedObjectName);
    }
}

?>
