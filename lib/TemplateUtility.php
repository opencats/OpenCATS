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

include_once(LEGACY_ROOT . '/vendor/autoload.php');
include_once(LEGACY_ROOT . '/lib/Candidates.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php');
include_once(LEGACY_ROOT . '/lib/SystemInfo.php');

use OpenCATS\UI\QuickActionMenu;

/**
 *	Template Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class TemplateUtility
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Prints the template header HTML for a non-modal window.
     *
     * @param string page title
     * @param array JavaScript / CSS files to load
     * @return void
     */
    public static function printHeader($pageTitle, $headIncludes = array())
    {
        self::_printCommonHeader($pageTitle, $headIncludes);
        echo '<body class="bg-body">', "\n";
        self::_printQuickActionMenuHolder();
        self::printPopupContainer();
    }

    /**
     * Prints the template header HTML for a modal window.
     *
     * @param string page title
     * @param array JavaScript / CSS files to load
     * @return void
     */
    public static function printModalHeader($pageTitle, $headIncludes = array(), $title = '')
    {
        self::_printCommonHeader($pageTitle, $headIncludes);

        echo '<body class="bg-body-tertiary">', "\n";

        if ($title != '')
        {
            echo '<script>parentSetPopTitle(', Template::escapeJs($title), ');</script>', "\n";
        }

        self::_printQuickActionMenuHolder();
    }

    /**
     * Prints logo and "top-right" header HTML.
     *
     * @return void
     */
    public static function printHeaderBlock($showTopRight = true)
    {
        $siteName  = $_SESSION['CATS']->getSiteName();
        $indexName = CATSUtility::getIndexName();

        $logoURL = self::getVersionedAssetURL(
            'images/logo_and_project_name.svg'
        );

        echo '<header id="headerBlock" class="bg-white border-bottom">', "\n";
        echo '<div class="container-fluid d-flex justify-content-between align-items-center gap-3 py-1">', "\n";

        echo '<a href="', Template::escapeAttr($indexName),
        '" class="d-flex align-items-center text-decoration-none">', "\n";
        echo '<img src="', Template::escapeAttr($logoURL),
        '" alt="OpenCATS" height="40">', "\n";
        echo '</a>', "\n";

        if (!eval(Hooks::get('TEMPLATE_LIVE_CHAT'))) return;
        if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_PRE_TOP_RIGHT'))) return;

        if ($showTopRight)
        {
            if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_1'))) return;
            if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_UPGRADE'))) return;
            if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_EXTENDED_SITE_NAME'))) return;

            $siteNameEscaped = Template::escapeHtml($siteName);

            echo '<div id="topRight" class="d-flex align-items-center gap-2 ms-auto small text-body-secondary">', "\n";

            echo '<span class="d-none d-md-inline">',
            $siteNameEscaped,
            '</span>', "\n";

            if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA)
            {
                echo '<span class="badge text-bg-secondary">Administrator</span>', "\n";
            }

            $systemInfo = new SystemInfo();
            $systemInfoData = $systemInfo->getSystemInfo();

            if (isset($systemInfoData['available_version']) &&
                $systemInfoData['available_version'] > CATSUtility::getVersionAsInteger() &&
                isset($systemInfoData['disable_version_check']) &&
                !$systemInfoData['disable_version_check'] &&
                $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA)
            {
                echo '<a href="http://www.catsone.com/download.php"',
                ' target="catsdl"',
                ' class="badge text-bg-warning text-decoration-none">',
                'Update available</a>', "\n";
            }

            if (!$_SESSION['CATS']->accountActive())
            {
                echo '<span class="badge text-bg-danger">Account Inactive</span>', "\n";
            }
            else if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) == ACCESS_LEVEL_READ)
            {
                echo '<span class="badge text-bg-warning">Read Only</span>', "\n";
            }
            else
            {
                if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_2_ELSE'))) return;
            }

            echo '<form id="logoutForm" name="logoutForm" method="post" action="',
            Template::escapeAttr($indexName),
            '?m=logout" class="d-inline">', "\n";

            if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
            {
                echo '<input type="hidden" name="csrfToken" value="',
                Template::escapeAttr($_SESSION['CATS']->getCSRFToken()),
                '">', "\n";
            }

            echo '<button type="submit" class="btn btn-sm btn-outline-secondary">',
            'Logout</button>', "\n";

            echo '</form>', "\n";
            echo '</div>', "\n";
        }

        echo '</div>', "\n";
        echo '</header>', "\n";
    }

    /**
     * Prints the time zone selection dropdown list.
     *
     * @param integer ID and name attributes of the time zone select input
     * @param string style attribute of the time zone select input
     * @param string class attribute of the time zone select input
     * @param integer selected GMT offset
     * @return void
     */
    public static function printTimeZoneSelect($selectID, $selectStyle,
                                               $selectClass, $selectedTimeZone)
    {
        echo '<select id="', $selectID, '" name="', $selectID, '"';

        if (!empty($selectClass))
        {
            echo ' class="', $selectClass, '"';
        }

        if (!empty($selectStyle))
        {
            echo ' style="', $selectStyle, '"';
        }

        echo '>';

        $currentTimeZone = '';

        foreach ($GLOBALS['timeZones'] as $timeZone)
        {
            echo '<option value="', $timeZone[0], '"';

            if ($timeZone[0] !== $currentTimeZone)
            {
                $currentTimeZone = $timeZone[0];
                if ($timeZone[0] == $selectedTimeZone)
                {
                    echo ' selected="selected"';
                }
            }

            echo '>', htmlspecialchars($timeZone[1]), '</option>';
        }

        echo '</select>';
    }

    /**
     * Returns a country selection dropdown list as HTML.
     *
     * @param string ID and name attributes of the country select input
     * @param string selected country code
     * @param boolean include blank option
     * @param string CSS class name for the select element
     * @param string inline style for the select element
     * @return string
     */
    public static function getCountrySelectHTML(
        $selectID,
        $selectedCode,
        $includeBlank = true,
        $className = 'inputbox',
        $style = 'width: 150px;'
    )
    {
        $selectedCode = strtoupper(trim($selectedCode));
        if (strlen($selectedCode) != 2 || !isset($GLOBALS['countries'][$selectedCode]))
        {
            $selectedCode = '';
        }

        $selectHTML = '<select id="' . Template::escapeAttr($selectID)
        . '" name="' . Template::escapeAttr($selectID)
        . '" class="' . Template::escapeAttr($className) . '"';
        if ($style !== '')
        {
            $selectHTML .= ' style="' . Template::escapeAttr($style) . '"';
        }
        $selectHTML .= '>';

        if ($includeBlank)
        {
            $selectHTML .= '<option value=""></option>';
        }

        foreach ($GLOBALS['countries'] as $countryCode => $countryName)
        {
            $selectHTML .= '<option value="' . Template::escapeAttr($countryCode) . '"';
            if ($countryCode == $selectedCode)
            {
                $selectHTML .= ' selected="selected"';
            }
            $selectHTML .= '>' . Template::escapeHtml($countryName) . '</option>';
        }

        $selectHTML .= '</select>';

        return $selectHTML;
    }

    /**
     * Prints a country selection dropdown list.
     *
     * @param string ID and name attributes of the country select input
     * @param string selected country code
     * @param boolean include blank option
     * @return void
     */
    public static function printCountrySelect($selectID, $selectedCode, $includeBlank = true)
    {
        echo self::getCountrySelectHTML($selectID, $selectedCode, $includeBlank);
    }

    /**
     * Prints the Quick Search box and MRU list.
     *
     * @return void
     */
    public static function printQuickSearch($wildCardString = '')
    {
        $MRU = $_SESSION['CATS']->getMRU()->getFormatted();
        $indexName = CATSUtility::getIndexName();

        echo '<div id="MRUPanel" class="container-fluid py-1 border-bottom bg-body-tertiary">', "\n";
        echo '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">', "\n";

        echo '<div id="MRUBlock" class="text-body-secondary text-truncate flex-grow-1">', "\n";

        if (!empty($MRU))
        {
            echo '<span class="fw-semibold">Recent:</span> ', $MRU, "\n";
        }

        echo '</div>', "\n";

        echo '<form id="quickSearchForm" action="',
        Template::escapeAttr($indexName),
        '" method="get"',
        ' onsubmit="return checkQuickSearchForm(document.quickSearchForm);"',
        ' class="d-flex align-items-center">', "\n";

        echo '<input type="hidden" name="m" value="home">', "\n";
        echo '<input type="hidden" name="a" value="quickSearch">', "\n";

        echo '<label id="quickSearchLabel" for="quickSearchFor" class="visually-hidden">',
        'Quick Search</label>', "\n";

        echo '<div class="input-group input-group-sm">', "\n";

        echo '<input type="search"',
        ' name="quickSearchFor"',
        ' id="quickSearchFor"',
        ' class="form-control"',
        ' value="', Template::escapeAttr($wildCardString), '"',
        ' placeholder="Quick search"',
        ' aria-label="Quick search">', "\n";

        echo '<button type="submit" name="quickSearch"',
        ' class="btn btn-outline-secondary">Go</button>', "\n";

        echo '</div>', "\n";
        echo '</form>', "\n";

        echo '</div>', "\n";
        echo '</div>', "\n";
    }

    /**
     * Prints Advanced Search for search pages.
     *
     * @return void
     */
    public static function printAdvancedSearch($considerFields)
    {
        echo '<input type="button" class="button" name="advancedSearch" id="advancedSearch" value="Advanced"',
        ' onclick="document.getElementById(\'advancedSearchField\').style.display=\'block\'; ',
        'advancedSearchReset();" style="display:none;">', "\n";
        echo '<input type="hidden" id="advancedSearchParser" name="advancedSearchParser" value="">', "\n";

        if (isset($_GET['advancedSearchOn']) && isset($_GET['advancedSearchParser']) &&
            $_GET['advancedSearchOn'] != 0 && !empty($_GET['advancedSearchParser']))
        {
            /* Output an active advanced search. */
            echo '<input type="hidden" id="advancedSearchOn" name="advancedSearchOn" value="',
            Template::escapeAttr($_GET['advancedSearchOn']), '" />', "\n";
            echo '<span id="advancedSearchField" style="display:block;">', "\n";
            echo '</span>', "\n";

            echo '<script type="text/javascript">', "\n";
            echo '    data = [];', "\n";
            echo '    nodes = [];', "\n";

            $stuff = explode('{[+', $_GET['advancedSearchParser']);
            for ($i = 0; $i < sizeof($stuff); $i++)
            {
                $innerStuff = explode('[|]', $stuff[$i]);

                echo '    data[',  $i, '] = ', Template::escapeJs($innerStuff[0]), ';', "\n";
                echo '    nodes[', $i, '] = ', Template::escapeJs($innerStuff[1]), ';', "\n";
            }
            echo '    data[', sizeof($stuff), '] = "";', "\n";
            echo '    advancedSearchDraw();', "\n";
            echo '</script>', "\n";
        }
        else
        {
            /* Output basic framework to start an advanced search; no search visible. */
            echo '<input type="hidden" id="advancedSearchOn" name="advancedSearchOn" value="0">', "\n";
            echo '<span id="advancedSearchField" style="display:none;">', "\n";
            echo '</span>', "\n";
        }

        /* Tell the script what fields have access to advanced search. */
        if (!empty($considerFields))
        {
            $considerFieldsArray = explode(',', $considerFields);

            echo '<script type="text/javascript">';
            echo '    advancedValidFields = ["', implode('","', $considerFieldsArray), '"];';
            echo '    advancedSearchConsider();';
            echo '</script>';
        }
    }

    /**
     * Prints the HTML for a saved search from a response array.
     *
     * @param response array
     * @return void
     */
    public static function printSavedSearch($savedSearchRS)
    {
        $savedSearchRecent = array();
        $savedSearchSaved = array();

        foreach ($savedSearchRS as $savedSearchRow)
        {
            if ($savedSearchRow['isCustom'] == 1)
            {
                $savedSearchSaved[] = $savedSearchRow;
            }
            else
            {
                $savedSearchRecent[] = $savedSearchRow;
            }
        }

        $currentUrlGET = array();
        foreach ($_GET as $key => $value)
        {
            if ($key != 'savedSearchID')
            {
                $currentUrlGET[] = $key . '=' . urlencode($value);
            }
        }

        $currentUrlGETString = urlencode(implode('&', $currentUrlGET));
        $indexName = CATSUtility::getIndexName();

        echo '<div class="recentSearchResults">';
        echo '<table style="vertical-align: top; border-collapse: collapse;"><tr style="vertical-align: top;"><td>';

        echo 'Recent Searches&nbsp;&nbsp;';
        echo '<img title="To save a recent search, press the + button below."',
        ' src="images/information.gif" alt="" width="16" height="16" />';

        echo '<div id="searchRecent" class="recentSearchResultsHidden">';

        /* Recent Search Results */
        if (count($savedSearchRecent) == 0)
        {
            echo '(None)';
        }
        else
        {
            foreach ($savedSearchRecent as $savedSearchRow)
            {
                if (strlen($savedSearchRow['dataItemText']) > 35)
                {
                    $savedSearchRow['dataItemText'] = substr($savedSearchRow['dataItemText'], 0, 35) . '...';
                }

                if (count($savedSearchSaved) >= RECENT_SEARCH_MAX_ITEMS)
                {
                    $openTag = '<a href="javascript:void(0);" onclick="alert(\'The maximum amount of saved searches is ' .
                    RECENT_SEARCH_MAX_ITEMS . '. To save this search, delete another saved search.\');">';
                    $closeTag = '</a>';
                }
                else
                {
                    $openTag = '<form method="post" action="' . $indexName . '?m=home&amp;a=addSavedSearch" style="display:inline;">'
                    . '<input type="hidden" name="postback" value="postback" />'
                    . '<input type="hidden" name="searchID" value="' . $savedSearchRow['searchID'] . '" />'
                    . '<input type="hidden" name="currentURL" value="' . $currentUrlGETString . '" />'
                    . '<button type="submit" class="linkButton">';
                    $closeTag = '</button></form>';
                }

                echo $openTag,
                '<img src="images/actions/add_small.gif" alt="" style="border: none;" title="Save This Search" />',
                $closeTag,
                '&nbsp;', "\n";

                $escapedURL  = htmlspecialchars($savedSearchRow['URL']);

                /* Remove leading slashes. */
                while (substr($escapedURL, 0, 1) == '/')
                {
                    $escapedURL = substr($escapedURL, 1);
                }
                $escapedURL = '/'.$escapedURL;


                $escapedText = htmlspecialchars($savedSearchRow['dataItemText']);

                echo '<a href="', $escapedURL,
                '" onclick="gotoSearch(\'', $escapedText, "', '", $escapedURL, '\');"',
                ' onmouseover="this.className += \'recentSearchResultsHighlight\';" ',
                ' onmouseout="this.className = this.className.replace(\'recentSearchResultsHighlight\', \'\');">',
                $escapedText, '</a>', '<br />', "\n";
            }
        }

        echo '</div>';
        echo '</td><td>&nbsp;</td><td>';

        echo 'Saved Searches&nbsp;&nbsp;';
        echo '<img title="To delete a recent search, press the - button."',
        ' src="images/information.gif" alt="" width="16" height="16" />';

        echo '<div id="searchSaved" class="savedSearchResultsHidden">';

        /* Saved Search Results */
        if (count($savedSearchSaved) == 0)
        {
            echo '(None)';
        }
        else
        {
            foreach ($savedSearchSaved as $savedSearchRow)
            {
                if (strlen($savedSearchRow['dataItemText']) > 35)
                {
                    $savedSearchRow['dataItemText'] = substr($savedSearchRow['dataItemText'], 0, 35) . '...';
                }

                $escapedURL  = htmlspecialchars($savedSearchRow['URL']);
                $escapedText = htmlspecialchars($savedSearchRow['dataItemText']);

                /* Remove leading slashes. */
                while (substr($escapedURL, 0, 1) == '/')
                {
                    $escapedURL = substr($escapedURL, 1);
                }
                $escapedURL = '/'.$escapedURL;

                echo '<form method="post" action="', $indexName, '?m=home&amp;a=deleteSavedSearch" style="display:inline;">',
                '<input type="hidden" name="postback" value="postback" />',
                '<input type="hidden" name="searchID" value="', $savedSearchRow['searchID'], '" />',
                '<input type="hidden" name="currentURL" value="', $currentUrlGETString, '" />',
                '<button type="submit" class="linkButton">',
                '<img src="images/actions/delete_small.gif" style="border: none;" title="Delete This Search" /></button></form>&nbsp;';

                echo '<a href="', $escapedURL, '&amp;savedSearchID=', $savedSearchRow['searchID'],
                '" onclick="gotoSearch(\'', $escapedText, "', '", $escapedURL,
                '&amp;savedSearchID=', $savedSearchRow['searchID'], '\');"',
                ' onmouseover="this.className += \'recentSearchResultsHighlight\';" ',
                ' onmouseout="this.className = this.className.replace(\'recentSearchResultsHighlight\', \'\');">',
                $escapedText,'</a><br />', "\n";
            }
        }

        echo '</div>', "\n";

        echo '</td></tr></table></div>';
        echo '<br /><br />';
        echo '<script type="text/javascript">syncRowHeightsSaved();</script>';
    }

    /**
     * Outputs a tester which checks if cookies are enabled in the user's
     * browser.
     *
     * @return void
     */
    public static function printCookieTester()
    {
        $indexName = CATSUtility::getIndexName();

        echo '<script type="text/javascript">
        if (navigator.cookieEnabled)
        {
        var cookieEnabled = true;
    }
    else
    {
    var cookieEnabled = false;
    }

    if (typeof(navigator.cookieEnabled) == "undefined" && !cookieEnabled)
    {
    document.cookie = \'testcookie\';
        cookieEnabled = (document.cookie.indexOf(\'testcookie\') != -1) ? true : false;
    }

    if (!cookieEnabled)
    {
    showPopWin(\'' . $indexName . '?m=login&amp;a=noCookiesModal\', 400, 225, null);
    }
    </script>';
    }

    /**
     * Outputs a popup container for use with JavaScript based popups like
     * ListEditor.js and other subModal.js-based dialogs.
     *
     * @return void
     */
    public static function printPopupContainer()
    {
        echo '<div id="popupMask">&nbsp;</div><div id="popupContainer">',
        '<div id="popupInner"><div id="popupTitleBar">',
        '<div id="popupTitle"></div><div id="popupControls">',
        '<img src="js/submodal/close.gif" alt="X" width="16" height="16"',
        ' onclick="hidePopWin(false);" /></div></div>';

        echo '<div style="width: 100%; height: 100%; background-color:',
        ' transparent; display: none;" id="popupFrameDiv"></div>';

        echo '<iframe src="js/submodal/loading.html" style="width: 100%; height: 100%;',
        ' background-color: transparent; display: none;" scrolling="auto"',
        ' frameborder="0" allowtransparency="true" id="popupFrameIFrame"',
        ' width="100%" height="100%"></iframe>';

        echo '</div></div>';
    }

    /**
     * Prints the module tabs.
     *
     * @param UserInterface active module interface
     * @param string active subtab name
     * @param string module name to forcibly highlight
     * @return void
     */
    public static function printTabs($active, $subActive = '', $forceHighlight = '')
    {
        /*
         * Special tab behaviours:
         *
         * Tab text = 'something*al=somenumber'
         * Tab text = 'something*al=somenumber@somesecuredobject'
         *
         * Subtab URL = 'url*al=somenumber'
         * Subtab URL = 'url*al=somenumber@somesecuredobject'
         * Subtab URL = 'url*js=javascript code'
         */

        $indexName = CATSUtility::getIndexName();
        $modules = ModuleUtility::getModules();
        $renderSubTabs = false;

        echo '<nav id="header" class="border-bottom" aria-label="Application navigation">', "\n";
        echo '<div class="container-fluid px-0">', "\n";
        echo '<ul id="primary" class="nav flex-nowrap gap-1 overflow-x-auto" ',
        'aria-label="Primary navigation">', "\n";

        foreach ($modules as $moduleName => $parameters)
        {
            $tabText = $parameters[1];

            /* Don't display a module's tab if $tabText is empty. */
            if (empty($tabText))
            {
                continue;
            }

            /* If name = Companies and HR mode is on, change tab name to My Company. */
            if ($_SESSION['CATS']->isHrMode() && $tabText == 'Companies')
            {
                $tabText = 'My Company';
            }

            /* Allow a hook to prevent a module from being displayed. */
            $displayTab = true;

            if (!eval(Hooks::get('TEMPLATE_UTILITY_EVALUATE_TAB_VISIBLE'))) return;

            if (!$displayTab)
            {
                continue;
            }

            /*
             * Inactive tab.
             */
            if ($active === null || $moduleName != $active->getModuleName())
            {
                $className = ($moduleName == $forceHighlight)
                ? 'nav-link active'
                : 'nav-link';

                $alPosition = strpos($tabText, '*al=');

                if ($alPosition === false)
                {
                    echo '<li class="nav-item">', "\n";
                    echo '<a class="', $className, '" href="',
                    $indexName, '?m=', $moduleName, '">',
                    $tabText, '</a>', "\n";
                    echo '</li>', "\n";
                }
                else
                {
                    $al = substr($tabText, $alPosition + 4);
                    $soPosition = strpos($al, '@');
                    $soName = '';

                    if ($soPosition !== false)
                    {
                        $soName = substr($al, $soPosition + 1);
                        $al = substr($al, 0, $soPosition);
                    }

                    if ($_SESSION['CATS']->getAccessLevel($soName) >= $al ||
                        $_SESSION['CATS']->isDemo())
                    {
                        echo '<li class="nav-item">', "\n";
                        echo '<a class="', $className, '" href="',
                        $indexName, '?m=', $moduleName, '">',
                        substr($tabText, 0, $alPosition), '</a>', "\n";
                        echo '</li>', "\n";
                    }
                }

                continue;
            }

            /*
             * Active tab.
             */
            $alPosition = strpos($tabText, '*al=');

            if ($alPosition !== false)
            {
                $tabText = substr($tabText, 0, $alPosition);
            }

            echo '<li class="nav-item">', "\n";
            echo '<a class="nav-link active" aria-current="page" href="',
            $indexName, '?m=', $moduleName, '">',
            $tabText, '</a>', "\n";
            echo '</li>', "\n";

            $renderSubTabs = true;
        }

        echo '</ul>', "\n";

        /*
         * Render secondary navigation separately from the primary tab.
         */
        if ($renderSubTabs)
        {
            $subTabs = $active->getSubTabs($modules);

            if ($subTabs)
            {
                echo '<ul id="secondary" class="nav nav-underline flex-nowrap gap-3 overflow-x-auto" ',
                'aria-label="Secondary navigation">', "\n";

                foreach ($subTabs as $subTabText => $link)
                {
                    $className = ($subTabText == $subActive)
                    ? 'nav-link active'
                    : 'nav-link';

                    /*
                     * Check HR mode.
                     */
                    $hrmodePosition = strpos($link, '*hrmode=');

                    if ($hrmodePosition !== false)
                    {
                        $hrmode = substr($link, $hrmodePosition + 8);

                        if ((!$_SESSION['CATS']->isHrMode() && $hrmode == 0) ||
                            ($_SESSION['CATS']->isHrMode() && $hrmode == 1))
                        {
                            $link = substr($link, 0, $hrmodePosition);
                        }
                        else
                        {
                            $link = '';
                        }
                    }

                    /*
                     * Check access level.
                     */
                    $alPosition = strpos($link, '*al=');

                    if ($alPosition !== false)
                    {
                        $al = substr($link, $alPosition + 4);
                        $soPosition = strpos($al, '@');
                        $soName = '';

                        if ($soPosition !== false)
                        {
                            $soName = substr($al, $soPosition + 1);
                            $al = substr($al, 0, $soPosition);
                        }

                        if ($_SESSION['CATS']->getAccessLevel($soName) >= $al ||
                            $_SESSION['CATS']->isDemo())
                        {
                            $link = substr($link, 0, $alPosition);
                        }
                        else
                        {
                            $link = '';
                        }
                    }

                    /*
                     * JavaScript subtab.
                     */
                    $jsPosition = strpos($link, '*js=');

                    if ($jsPosition !== false)
                    {
                        echo '<li class="nav-item">', "\n";
                        echo '<a class="', $className, '" href="',
                        substr($link, 0, $jsPosition),
                        '" onclick="',
                        substr($link, $jsPosition + 4),
                        '">',
                        $subTabText,
                        '</a>', "\n";
                        echo '</li>', "\n";
                    }

                    /*
                     * Default company subtab.
                     */
                    else if (strpos($link, 'a=internalPostings') !== false)
                    {
                        include_once(LEGACY_ROOT . '/lib/Companies.php');

                        $companies = new Companies();
                        $defaultCompanyID = $companies->getDefaultCompany();

                        if ($defaultCompanyID !== false)
                        {
                            echo '<li class="nav-item">', "\n";
                            echo '<a class="', $className, '" href="',
                            $link, '">',
                            $subTabText, '</a>', "\n";
                            echo '</li>', "\n";
                        }
                    }

                    /*
                     * Administration subtab.
                     */
                    else if (strpos($link, 'a=administration') !== false)
                    {
                        if ($_SESSION['CATS']->getAccessLevel(
                            'settings.administration'
                        ) >= ACCESS_LEVEL_DEMO)
                        {
                            echo '<li class="nav-item">', "\n";
                            echo '<a class="', $className, '" href="',
                            $link, '">',
                            $subTabText, '</a>', "\n";
                            echo '</li>', "\n";
                        }
                    }

                    /*
                     * EEO Report subtab.
                     */
                    else if (strpos($link, 'a=customizeEEOReport') !== false)
                    {
                        $EEOSettings = new EEOSettings();
                        $EEOSettingsRS = $EEOSettings->getAll();

                        if ($EEOSettingsRS['enabled'] == 1)
                        {
                            echo '<li class="nav-item">', "\n";
                            echo '<a class="', $className, '" href="',
                            $link, '">',
                            $subTabText, '</a>', "\n";
                            echo '</li>', "\n";
                        }
                    }

                    /*
                     * Normal subtab.
                     */
                    else if ($link != '')
                    {
                        echo '<li class="nav-item">', "\n";
                        echo '<a class="', $className, '" href="',
                        $link, '">',
                        $subTabText, '</a>', "\n";
                        echo '</li>', "\n";
                    }
                }

                if (!eval(Hooks::get('TEMPLATE_UTILITY_DRAW_SUBTABS'))) return;

                echo '</ul>', "\n";
            }
        }

        echo '</div>', "\n";
        echo '</nav>', "\n";
    }


    /**
     * Prints footer HTML for application  pages.
     *
     * @return void
     */
    public static function printFooter()
    {
        $build    = $_SESSION['CATS']->getCachedBuild();
        $loadTime = $_SESSION['CATS']->getExecutionTime();

        $buildString = ($build > 0)
        ? ' build ' . $build
        : '';

        echo '<footer class="footerBlock container-fluid py-1 border-top text-body-secondary">', "\n";

        echo '<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">', "\n";

        echo '<span id="footerText">',
        'OpenCATS ', CATS_VERSION, $buildString,
        ' &middot; Powered by ',
        '<a href="http://www.opencats.org/" class="text-body-secondary">',
        'OpenCATS</a>',
        '</span>', "\n";

        echo '<span id="footerResponse">',
        'Response: ', $loadTime, 's',
        '</span>', "\n";

        echo '</div>', "\n";

        echo '<div id="footerCopyright">',
        COPYRIGHT_HTML,
        '</div>', "\n";

        if (!eval(Hooks::get('TEMPLATEUTILITY_SHOWPRIVACYPOLICY'))) return;

        echo '</footer>', "\n";

        eval(Hooks::get('TEMPLATE_UTILITY_PRINT_FOOTER'));

        echo '</body>', "\n";
        echo '</html>', "\n";
    }

    /**
     * Prints HTML for pipeline candidate-joborder match rating stars.
     *
     * @param integer rating (0-5)
     * @param integer candidate-joborder ID
     * @param string PHP session cookie
     * @return string HTML
     */
    public static function getRatingObject($rating, $candidateJobOrderID, $sessionCookie)
    {
        static $firstCall = true;

        /* These usually come straight from the database; make sure it's an
         * integer.
         */
        $rating = (int) $rating;

        $ratings = self::_getRatingImages();
        $indexName = CATSUtility::getIndexName();

        if ($_SESSION['CATS']->getAccessLevel('pipelines.editRating') < ACCESS_LEVEL_EDIT)
        {
            $HTML = '<img src="' . $ratings[$rating] . '" style="border: none;" alt="" id="moImage' . $candidateJobOrderID . '" />';
            return $HTML;
        }

        $HTML  = '<!--MATCHROW moImageValue' . $candidateJobOrderID . '-->';
        if ($rating >= 0)
        {
            $HTML .= '<img src="' . $ratings[$rating] . '" style="border: none;" alt="" id="moImage' . $candidateJobOrderID . '" usemap="#moImageMapPos' . $candidateJobOrderID . '" />';
        }
        else
        {
            $HTML .= '<img src="' . $ratings[$rating] . '" style="border: none;" alt="" id="moImage' . $candidateJobOrderID . '" usemap="#moImageMapNeg' . $candidateJobOrderID . '" />';
            $HTML .= '<map id ="moImageMapNeg' . $candidateJobOrderID . '" name="moImageMapNeg' . $candidateJobOrderID . '">';
            $HTML .= '<area shape="rect" coords="0,0,3,12"  href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 0);" onclick="moImageValue' . $candidateJobOrderID . ' = 0; setRating(' . $candidateJobOrderID . ', 0, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '<area shape="rect" coords="4,1,12,12"  href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 7);" onclick="moImageValue' . $candidateJobOrderID . ' = -2; setRating(' . $candidateJobOrderID . ', -2, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '<area shape="rect" coords="13,1,23,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 8);" onclick="moImageValue' . $candidateJobOrderID . ' = -3; setRating(' . $candidateJobOrderID . ', -3, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '<area shape="rect" coords="24,1,34,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 9);" onclick="moImageValue' . $candidateJobOrderID . ' = -4; setRating(' . $candidateJobOrderID . ', -4, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '<area shape="rect" coords="35,1,45,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 10);" onclick="moImageValue' . $candidateJobOrderID . ' = -5; setRating(' . $candidateJobOrderID . ', -5, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '<area shape="rect" coords="46,1,56,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 11);" onclick="moImageValue' . $candidateJobOrderID . ' = -6; setRating(' . $candidateJobOrderID . ', -6, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
            $HTML .= '</map>';
        }
        $HTML .= '<map id ="moImageMapPos' . $candidateJobOrderID . '" name="moImageMapPos' . $candidateJobOrderID . '">';
        $HTML .= '<area shape="rect" coords="0,0,3,12"  href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 0);" onclick="moImageValue' . $candidateJobOrderID . ' = 0; setRating(' . $candidateJobOrderID . ', 0, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '<area shape="rect" coords="4,1,12,12"  href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 1);" onclick="moImageValue' . $candidateJobOrderID . ' = 1; setRating(' . $candidateJobOrderID . ', 1, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '<area shape="rect" coords="13,1,23,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 2);" onclick="moImageValue' . $candidateJobOrderID . ' = 2; setRating(' . $candidateJobOrderID . ', 2, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '<area shape="rect" coords="24,1,34,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 3);" onclick="moImageValue' . $candidateJobOrderID . ' = 3; setRating(' . $candidateJobOrderID . ', 3, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '<area shape="rect" coords="35,1,45,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 4);" onclick="moImageValue' . $candidateJobOrderID . ' = 4; setRating(' . $candidateJobOrderID . ', 4, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '<area shape="rect" coords="46,1,56,12" href="javascript:void(0);" onmouseout="showImage(\'moImage' . $candidateJobOrderID . '\', moImageValue' . $candidateJobOrderID . ');" onmouseover="showImage(\'moImage' . $candidateJobOrderID . '\', 5);" onclick="moImageValue' . $candidateJobOrderID . ' = 5; setRating(' . $candidateJobOrderID . ', 5, \'moImage' . $candidateJobOrderID . '\', \'' . $sessionCookie . '\');" alt="">';
        $HTML .= '</map>';

        $HTML .= '<script type="text/javascript">';
        $HTML .= 'moImageValue' . $candidateJobOrderID . ' = ' . $rating . ';';

        $HTML .= '</script>';

        /* Only on the first call... */
        if ($firstCall)
        {
            $HTML .= self::getRatingsArrayJS();
        }

        return $HTML;
    }

    /**
     * Prints out the image array of ratings for associated JavaScript.
     *
     * @param integer table row number
     * @return void
     */
    public static function getRatingsArrayJS()
    {
        $ratings = self::_getRatingImages();

        $HTML = '<script type="text/javascript">';

        foreach ($ratings as $rating)
        {
            $ratingsQuoted[] = '"' . $rating . '"';
        }

        $ratingsQuotedString = implode(',', $ratingsQuoted);
        $HTML .= "\n" . 'defineImages(new Array(' . $ratingsQuotedString . '));';

        $HTML .= "\n" . '</script>';

        return $HTML;
    }

    // TODO: Document me.
    public static function getDataItemTypeDescription($dataItemType)
    {
        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                return 'Candidate';
                break;

            case DATA_ITEM_COMPANY:
                return 'Company';
                break;

            case DATA_ITEM_CONTACT:
                return 'Contact';
                break;

            case DATA_ITEM_JOBORDER:
                return 'Joborder';
                break;

            default:
                return '';
        }
    }

    /**
     * Prints out the class name for the current row number (for tables where
     * row color alternates). Even row numbers get the 'evenTableRow' class;
     * odd numbers get the 'oddTableRow' class.
     *
     * @param integer table row number
     * @return void
     */
    public static function printAlternatingRowClass($rowNumber)
    {
        /* Is the row number even? */
        if (($rowNumber % 2) == 0)
        {
            echo 'evenTableRow';
            return;
        }

        echo 'oddTableRow';
    }

    /**
     * Prints out the class name for the current row number (for div pairs where
     * row color alternates). Even row numbers get the 'evenTableRow' class;
     * odd numbers get the 'oddTableRow' class.
     *
     * @param integer div row number
     * @return void
     */
    public static function printAlternatingDivClass($rowNumber)
    {
        /* Is the row number even? */
        if (($rowNumber % 2) == 0)
        {
            echo 'evenDivRow';
            return;
        }

        echo 'oddDivRow';
    }

    /**
     * Returns the class name for the current row number (for tables where
     * row color alternates). Even row numbers get the 'evenTableRow' class;
     * odd numbers get the 'oddTableRow' class.
     *
     * @param integer table row number
     * @return void
     */
    public static function getAlternatingRowClass($rowNumber)
    {
        /* Is the row number even? */
        if (($rowNumber % 2) == 0)
        {
            return 'evenTableRow';
        }
        else
        {
            return 'oddTableRow';
        }
    }

    /**
     * Escapes activity notes and highlights status change text at render time.
     *
     * @param string activity note text
     * @return string escaped notes with optional status text highlight
     */
    public static function highlightStatusChangeActivityNote($notes)
    {
        $statusPrefix = 'Status change: ';
        if (strpos($notes, $statusPrefix) !== 0)
        {
            return Template::escapeHtml($notes);
        }

        $statusText = substr($notes, strlen($statusPrefix));
        if ($statusText === '')
        {
            return Template::escapeHtml($notes);
        }

        return Template::escapeHtml($statusPrefix)
        . '<span class="statusChangeHighlight">'
        . Template::escapeHtml($statusText)
        . '</span>';
    }

    /**
     * Removes from $text everything from starting block through ending block.
     * Optionally also removes a following piece of text indicated by closing
     * tag.
     *
     * For example, lets say you had the following text:
     *
     *   <a href="blah/blah.html?id=55"><b>My Link</b></a>
     *
     * If you wanted to remove the hyperlink from the text for every occurrence
     * of this format of link, you could use:
     *
     *   $HTML = filterRemoveTextBlock(
     *       $HTML, '<a href="blah/blah.html?id=', '>', '</a>'
     *   );
     *
     * and the link would be replaced with '<b>My Link</b>' in the returned
     * text / HTML.
     *
     * @param string output HTML to filter
     * @param string text at start of text to be removed
     * @param string text at end of text to be removed
     * @param string closing tag to be removed
     * @return string filtered HTML output
     */
    public static function filterRemoveTextBlock($text, $startBlock, $endBlock, $closingTag = '')
    {
        $startPos = strpos($text, $startBlock);
        if ($startPos !== false)
        {
            $endPos = strpos(substr($text, $startPos + strlen($startBlock)), $endBlock);
        }
        else
        {
            $endPos = false;
        }

        while ($startPos !== false || $endPos !== false)
        {
            if ($startPos === false)
            {
                $startPos = 0;
            }

            if ($endPos === false)
            {
                $endPos = 0;
            }
            else
            {
                $endPos += strlen($endBlock);
            }

            $text = substr_replace($text, '', $startPos, $endPos + strlen($startBlock));

            if ($closingTag != '')
            {
                $closingPos = strpos(substr($text, $startPos), $closingTag);

                if ($closingPos !== false)
                {
                    $text = substr_replace($text, '', $closingPos + $startPos, strlen($closingTag));
                }
            }

            $startPos = strpos($text, $startBlock);
            if ($startPos !== false)
            {
                $endPos = strpos(substr($text, $startPos + strlen($startBlock)), $endBlock);
            }
            else
            {
                $endPos = false;
            }
        }

        return $text;
    }

    public static function printSingleQuickActionMenu(QuickActionMenu $menu)
    {
        return $menu->getHtml();
    }

    public static function _printQuickActionMenuHolder()
    {
        echo '<div class="ajaxSearchResults" id="singleQuickActionMenu" align="left" style="width:200px;" onclick="toggleVisibility()">';

        echo '</div>';
    }

    /**
     * Returns an asset URL with a file-based version query parameter.
     *
     * @param string Relative asset path
     * @return string
     */
    public static function getVersionedAssetURL($assetPath)
    {
        $assetPath = (string) $assetPath;
        if ($assetPath == '')
        {
            return $assetPath;
        }

        $parsedURL = parse_url($assetPath);
        if ($parsedURL === false || isset($parsedURL['scheme']) || isset($parsedURL['host']))
        {
            return $assetPath;
        }

        $path = isset($parsedURL['path']) ? $parsedURL['path'] : '';
        if ($path == '')
        {
            return $assetPath;
        }

        $legacyRootPath = realpath(LEGACY_ROOT);
        if ($legacyRootPath === false)
        {
            return $assetPath;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        if ($normalizedPath == '')
        {
            return $assetPath;
        }

        $assetFilePath = realpath(
            $legacyRootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedPath)
        );
        if ($assetFilePath === false)
        {
            return $assetPath;
        }

        $legacyRootPrefix = rtrim($legacyRootPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($assetFilePath !== $legacyRootPath && strpos($assetFilePath, $legacyRootPrefix) !== 0)
        {
            return $assetPath;
        }

        $fileMTime = @filemtime($assetFilePath);
        if ($fileMTime === false)
        {
            return $assetPath;
        }

        $queryString = isset($parsedURL['query']) ? $parsedURL['query'] . '&' : '';
        $queryString .= 'v=' . (int) $fileMTime;

        $versionedAssetURL = $path . '?' . $queryString;
        if (isset($parsedURL['fragment']))
        {
            $versionedAssetURL .= '#' . $parsedURL['fragment'];
        }

        return $versionedAssetURL;
    }

    /**
     * Prints template header HTML.
     *
     * @param string page title
     * @param array JavaScript / CSS files to load
     * @return void
     */
    private static function _printCommonHeader($pageTitle, $headIncludes = array())
    {
        if (!is_array($headIncludes))
        {
            $headIncludes = array($headIncludes);
        }

        echo '<!DOCTYPE html>', "\n";
        echo '<html lang="en">', "\n";
        echo '<head>', "\n";
        echo '<meta charset="', HTML_ENCODING, '">', "\n";
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">', "\n";
        echo '<title>OpenCATS - ', Template::escapeHtml($pageTitle), '</title>', "\n";
        echo '<link rel="icon" href="images/favicon.ico" type="image/x-icon">', "\n";
        echo '<link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">', "\n";
        echo '<link rel="alternate" type="application/rss+xml" title="RSS" href="',
        CATSUtility::getIndexName(), '?m=rss">', "\n";

        /* Core JS files */
        $coreJavaScriptFiles = array(
            'js/lib.js',
            'js/quickAction.js',
            'js/calendarDateInput.js',
            'js/submodal/subModal.js',
            'js/jquery-1.3.2.min.js'
        );
        foreach ($coreJavaScriptFiles as $coreJavaScriptFile)
        {
            $versionedFilename = self::getVersionedAssetURL($coreJavaScriptFile);
            echo '<script type="text/javascript" src="', $versionedFilename, '"></script>', "\n";
        }
        echo '<script type="text/javascript">CATSIndexName = ', Template::escapeJs(CATSUtility::getIndexName()), ';</script>', "\n";
        if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
        {
            $csrfToken = $_SESSION['CATS']->getCSRFToken();
            echo '<script type="text/javascript">CATSCsrfToken = ',
            Template::escapeJs($csrfToken), ';</script>', "\n";
            echo '<script type="text/javascript">', "\n";
            echo 'function catsInjectCSRFToken()', "\n";
            echo '{', "\n";
            echo '    if (typeof CATSCsrfToken == "undefined" || CATSCsrfToken === null || CATSCsrfToken === "")', "\n";
            echo '    {', "\n";
            echo '        return;', "\n";
            echo '    }', "\n";
            echo '    var forms = document.getElementsByTagName("form");', "\n";
            echo '    for (var i = 0; i < forms.length; i++)', "\n";
            echo '    {', "\n";
            echo '        var form = forms[i];', "\n";
            echo '        var method = form.method;', "\n";
            echo '        if (!method || method.toLowerCase() != "post")', "\n";
            echo '        {', "\n";
            echo '            continue;', "\n";
            echo '        }', "\n";
            echo '        var action = form.action;', "\n";
            echo '        if (action && (action.indexOf("http://") == 0 || action.indexOf("https://") == 0))', "\n";
            echo '        {', "\n";
            echo '            var parser = document.createElement("a");', "\n";
            echo '            parser.href = action;', "\n";
            echo '            if (parser.host && parser.host.toLowerCase() != window.location.host.toLowerCase())', "\n";
            echo '            {', "\n";
            echo '                continue;', "\n";
            echo '            }', "\n";
            echo '        }', "\n";
            echo '        var hasToken = false;', "\n";
            echo '        if (form.elements)', "\n";
            echo '        {', "\n";
            echo '            for (var j = 0; j < form.elements.length; j++)', "\n";
            echo '            {', "\n";
            echo '                if (form.elements[j].name == "csrfToken")', "\n";
            echo '                {', "\n";
            echo '                    hasToken = true;', "\n";
            echo '                    break;', "\n";
            echo '                }', "\n";
            echo '            }', "\n";
            echo '        }', "\n";
            echo '        if (hasToken)', "\n";
            echo '        {', "\n";
            echo '            continue;', "\n";
            echo '        }', "\n";
            echo '        var input = document.createElement("input");', "\n";
            echo '        input.type = "hidden";', "\n";
            echo '        input.name = "csrfToken";', "\n";
            echo '        input.value = CATSCsrfToken;', "\n";
            echo '        form.appendChild(input);', "\n";
            echo '    }', "\n";
            echo '}', "\n";
            echo 'var catsOldOnload = window.onload;', "\n";
            echo 'window.onload = function()', "\n";
            echo '{', "\n";
            echo '    if (catsOldOnload)', "\n";
            echo '    {', "\n";
            echo '        catsOldOnload();', "\n";
            echo '    }', "\n";
            echo '    catsInjectCSRFToken();', "\n";
            echo '};', "\n";
            echo '</script>', "\n";
        }

        $headIncludes = array_merge(
            array(
                'vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
                'main.css'
            ),
            $headIncludes
        );

        foreach ($headIncludes as $key => $filename)
        {
            $path = parse_url($filename, PHP_URL_PATH);
            if ($path === false || $path === null)
            {
                $path = $filename;
            }
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

            $versionedFilename = self::getVersionedAssetURL($filename);

            if ($extension == 'js')
            {
                echo '<script type="text/javascript" src="', $versionedFilename, '"></script>', "\n";
            }
            else if ($extension == 'css')
            {
                echo '<style type="text/css" media="all">@import "', $versionedFilename, '";</style>', "\n";
            }
        }

        echo '<!--[if IE]><link rel="stylesheet" type="text/css" href="',
        self::getVersionedAssetURL('ie.css'),
        '" /><![endif]-->', "\n";
        echo '<![if !IE]><link rel="stylesheet" type="text/css" href="',
        self::getVersionedAssetURL('not-ie.css'),
        '" /><![endif]>', "\n";
        echo '</head>', "\n\n";
    }


    /**
     * Returns an array of "star" images for rating values.
     *
     * @return array rating values and associated image paths
     */
    private static function _getRatingImages()
    {
        return array(
            0  => 'images/stars/star0.gif',
            1  => 'images/stars/star1.gif',
            2  => 'images/stars/star2.gif',
            3  => 'images/stars/star3.gif',
            4  => 'images/stars/star4.gif',
            5  => 'images/stars/star5.gif',
            -1 => 'images/stars/starneg1.gif',
            -2 => 'images/stars/starneg2.gif',
            -3 => 'images/stars/starneg3.gif',
            -4 => 'images/stars/starneg4.gif',
            -5 => 'images/stars/starneg5.gif',
            -6 => 'images/stars/starneg6.gif'
        );
    }
}

?>
