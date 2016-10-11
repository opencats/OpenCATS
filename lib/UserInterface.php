<?php
/**
 * CATS
 * User Interface Class
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
 * @version    $Id: UserInterface.php 3593 2007-11-13 17:36:57Z andrew $
 */

/**
 *	User Interface Library
 *	@package    CATS
 *	@subpackage Library
 */
class UserInterface
{
    protected $_moduleName = '';
    protected $_moduleTabText = '';
    protected $_subTabs = array();
    protected $_subTabsExternal = array();
    protected $_settingsEntries = array();
    protected $_settingsUserCategories = array();
    protected $_template;
    protected $_moduleDirectory = '';
    protected $_userID = -1;
    protected $_siteID = -1;
    protected $_authenticationRequired = true;
    protected $_hooks = array();
    protected $_schema = array();

    public function __construct()
    {
        $this->_template = new Template();

        if (isset($_SESSION['CATS']) && !empty($_SESSION['CATS']))
        {
            /* Get the current user's user ID. */
            $this->_userID = $_SESSION['CATS']->getUserID();

            /* Get the current user's site ID. */
            $this->_siteID = $_SESSION['CATS']->getSiteID();

        }
    }

    /**
     * Returns this module's name.
     *
     * @return string name of the module
     */
    public function getModuleName()
    {
        return $this->_moduleName;
    }

    /**
     * Returns this module's tab text.
     *
     * @return string tab text of the module
     */
    public function getModuleTabText()
    {
        return $this->_moduleTabText;
    }

    /**
     * Returns hooks defined by this module.
     *
     * @return array hooks
     */
    public function getHooks()
    {
        return $this->_hooks;
    }

    /**
     * Returns schema revisions defined by this module.
     *
     * @return array hooks
     */
    public function getSchema()
    {
        return $this->_schema;
    }

    /**
     * Returns subtabs for this module as an array of strings.
     *
     * @return array subtab items for this module
     */
    public function getSubTabs($modules = array())
    {
        if (empty($modules))
        {
            return $this->_subTabs;
        }

        $subTabsExternal = $this->getThisSubTabsExternal($modules);
        return array_merge($this->_subTabs, $subTabsExternal);
    }

    /**
     * Returns subtabs for this module as an array of strings.
     *
     * @return array subtab items for this module
     */
    public function getSubTabsExternal()
    {
        if (isset($this->_subTabsExternal))
        {
            return $this->_subTabsExternal;
        }

        return false;
    }

    /**
     * Get a list of settings and their values pertaining to the
     * user interface.
     *
     * @return mixed Array or false on failure
     */
    public function getSettingsEntries()
    {
        if (isset($this->_settingsEntries))
        {
            return $this->_settingsEntries;
        }

        return false;
    }

    /**
     * Get a list of settings pertaining to user categories
     * for the user interface.
     *
     * @return mixed Array or false on failure
     */
    public function getSettingsUserCategories()
    {
        if (isset($this->_settingsUserCategories))
        {
            return $this->_settingsUserCategories;
        }

        return false;
    }

    /**
     * Returns whether or not the module requires authentication.
     *
     * @return boolean requires authentication
     */
    public function requiresAuthentication()
    {
        if (isset($this->_authenticationRequired))
        {
            return $this->_authenticationRequired;
        }

        return true;
    }

    /**
     * Returns the action name that a module was called with (the a=blah part
     * of the request URI).
     *
     * @return string action name
     */
    protected function getAction()
    {
        if (isset($_GET['a']) && !empty($_GET['a']))
        {
            return $_GET['a'];
        }

        return '';
    }

    /**
     * Returns true if the module/action was called with postback=postback
     * in the POST data.
     *
     * @return boolean is postback
     */
    protected function isPostBack()
    {
        if (isset($_POST['postback']))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if the module/action was called with getback=getback
     * in the GET string.
     *
     * @return boolean is getback
     */
    protected function isGetBack()
    {
        if (isset($_GET['getback']))
        {
            return true;
        }

        return false;
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @param string module directory from which to load templates (optional)
     * @return void
     */
    protected function fatal($error, $directoryOverride = '')
    {
        if ($directoryOverride != '')
        {
            $moduleDirectory = $directoryOverride;
        }
        else
        {
            $moduleDirectory = $this->_moduleDirectory;
        }

        $this->_template->assign('active', $this);
        $this->_template->assign('errorMessage', $error);
        $this->_template->display(
            './modules/' . $moduleDirectory . '/Error.tpl'
        );

        $getArray = array();
        foreach ($_REQUEST as $index => $data)
        {
            $getArray[] = urlencode($index) . '=' . urlencode($data);
        }

        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Error raised: ' . $error)
         );
        echo ' REQUEST: '.implode('&', $getArray).'-->';

        die();
    }

    /**
     * Print a fatal error and die (used in a modal dialog).
     *
     * @param string error message
     * @param string module directory from which to load templates (optional)
     * @return void
     */
    protected function fatalModal($error, $directoryOverride = '')
    {
        if ($directoryOverride != '')
        {
            $moduleDirectory = $directoryOverride;
        }
        else
        {
            $moduleDirectory = $this->_moduleDirectory;
        }

        $this->_template->assign('errorMessage', $error);
        $this->_template->display(
            './modules/' . $moduleDirectory . '/ErrorModal.tpl'
        );

        /*
        echo '<!--';
         trigger_error(
             str_replace("\n", " ", 'Fatal Modal Error raised: ' . $error)
         );
        echo '-->';
        */

        die();
    }

    /**
     * Returns true if a required numeric ID ($key) is a) present in $request,
     * b) not empty, and c) a digit / whole number. ID cannot be '0' unless
     * $allowZero is true.
     *
     * @param string request key name of ID
     * @param array $_GET, $_POST, or $_REQUEST
     * @param boolean allow ID to be 0
     * @return void
     */
    protected function isRequiredIDValid($key, $request, $allowZero = false)
    {
        if (isset($request[$key]) && (!empty($request[$key]) ||
            ($allowZero && $request[$key] == '0')) &&
            ctype_digit((string) trim($request[$key])))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if an optional numeric ID ($key) is a) present in $request,
     * b) not empty, and c) a digit / whole number, or -1.
     *
     * @param string request key name of ID
     * @param array $_GET, $_POST, or $_REQUEST
     * @return void
     */
    protected function isOptionalIDValid($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            ($request[$key] == '-1' || ctype_digit((string) $request[$key])))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if a checkbox by the name of $key is checked in $request.
     *
     * @param string Request variable name / key.
     * @param array $_GET, $_POST, or $_REQUEST
     * @return boolean Is checkbox checked?
     */
    protected function isChecked($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            $request[$key] != 'false' && $request[$key] != 'off')
        {
            return true;
        }

        return false;
    }

    /**
     * Returns trim()'d form input if $key is in $request; otherwise ''.
     *
     * @param string Request variable name / key.
     * @param array $_GET, $_POST, or $_REQUEST
     * @return string Trimmed value or ''.
     */
    protected function getTrimmedInput($key, $request)
    {
        if (isset($request[$key]))
        {
            return trim($request[$key]);
        }

        return '';
    }

    /**
     * Returns valid subtabs for this module.
     *
     * @return array subtab items for this module
     */
    public function getThisSubTabsExternal($modules)
    {
        $ret = array();

        foreach ($modules as $moduleName => $parameters)
        {
            $subTabsExternal = $parameters[2];

            if ($subTabsExternal != false)
            {
                foreach ($subTabsExternal as $moduleNameTab => $theSubTab)
                {
                    if ($moduleNameTab === $this->_moduleName)
                    {
                        $ret = array_merge($ret, $theSubTab);
                    }
                }
            }
        }

        return $ret;
    }
    
     /**
     * Returns access level of logged in user for securedObject
     * Intended to be used in UI classes (deriving from UserInterface) to check if user has acces to particular module and it's action.
     */
    protected function getUserAccessLevel($securedObjectName)
    {
        return $_SESSION['CATS']->getAccessLevel($securedObjectName);
    }
}

?>
