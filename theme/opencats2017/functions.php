<?php
function opencats2017_nav(){
    $active = oc_get_active();
    $subActive = '';
    $forceHighlight = '';
    echo '<aside id="menu">';
    echo '    <div id="navigation">';
    echo '        <ul class="nav" id="side-menu">';

    $indexName = CATSUtility::getIndexName();

    $modules = ModuleUtility::getModules();
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

        /* Inactive Tab? */
        if ($active === null || $moduleName != $active->getModuleName())
        {
            if ($moduleName == $forceHighlight)
            {
                $className = 'active';
            }
            else
            {
                $className = 'inactive';
            }

            $alPosition = strpos($tabText, "*al=");
            if ($alPosition === false)
            {
                echo '<li class="', $className, '"><a href="', $indexName,
                     '?m=', $moduleName, '">', $tabText, '</a></li>', "\n";
            }
            else
            {
                 $al = substr($tabText, $alPosition + 4);
                 $soPosition = strpos($al, "@");
                 $soName = '';
                 if( $soPosition !== false )
                 {
                     $soName = substr($al, $soPosition + 1);
                     $al = substr($al, 0, $soPosition);
                 }
                 if ($_SESSION['CATS']->getAccessLevel($soName) >= $al ||
                     $_SESSION['CATS']->isDemo())
                 {
                    echo '<li class="', $className, '"><a href="', $indexName, '?m=', $moduleName, '">',
                         substr($tabText, 0, $alPosition), '</a></li>', "\n";
                }
            }

            continue;
        }

        $alPosition = strpos($tabText, "*al=");
        if ($alPosition !== false)
        {
            $tabText = substr($tabText, 0, $alPosition);
        }

        /* Start the <li> block for the active tab. The secondary <ul>
         * for subtabs MUST be contained within this block. It is
         * closed after subtabs are printed. */
        echo '<li class="active">';

        echo '<a href="', $indexName, '?m=', $moduleName,
             '">', $tabText, '</a>', "\n";

        $subTabs = $active->getSubTabs($modules);
        if ($subTabs)
        {
            echo '<ul class="nav nav-second-level collapse in">';

            foreach ($subTabs as $subTabText => $link)
            {
                if ($subTabText == $subActive)
                {
                    $subClass = "active";
                }
                else
                {
                    $subClass = "";
                }

                /* Check HR mode for displaying tab. */
                $hrmodePosition = strpos($link, "*hrmode=");
                if ($hrmodePosition !== false)
                {
                    /* Access level restricted subtab. */
                    $hrmode = substr($link, $hrmodePosition + 8);
                    if ((!$_SESSION['CATS']->isHrMode() && $hrmode == 0) ||
                        ($_SESSION['CATS']->isHrMode() && $hrmode == 1))
                    {
                        $link =  substr($link, 0, $hrmodePosition);
                    }
                    else
                    {
                        $link = '';
                    }
                }

                /* Check access level for displaying tab. */
                $alPosition = strpos($link, "*al=");
                if ($alPosition !== false)
                {
                    /* Access level restricted subtab. */
                    $al = substr($link, $alPosition + 4);
                    $soPosition = strpos($al, "@");
                    $soName = '';
                    if( $soPosition !== false )
                    {
                        $soName = substr($al, $soPosition + 1);
                        $al = substr($al, 0, $soPosition);
                    }
                    if ($_SESSION['CATS']->getAccessLevel($soName) >= $al ||
                        $_SESSION['CATS']->isDemo())
                    {
                        $link =  substr($link, 0, $alPosition);
                    }
                    else
                    {
                        $link = '';
                    }
                }

                $jsPosition = strpos($link, "*js=");
                if ($jsPosition !== false)
                {
                    /* Javascript subtab. */
                    echo '<li class="'.$subClass.'"><a href="', substr($link, 0, $jsPosition), '" onclick="',
                         substr($link, $jsPosition + 4), '">', $subTabText, '</a></li>', "\n";
                }

                /* A few subtabs have special logic to decide if they display or not. */
                /* FIXME:  Put the logic for these somewhere else.  Perhaps the definitions of the subtabs
                           themselves should have an eval()uatable rule?
                           Brian 6-14-07:  Second.  */
                else if (strpos($link, 'a=internalPostings') !== false)
                {
                    /* Default company subtab. */
                    include_once('./lib/Companies.php');

                    $companies = new Companies($_SESSION['CATS']->getSiteID());
                    $defaultCompanyID = $companies->getDefaultCompany();
                    if ($defaultCompanyID !== false)
                    {
                        echo '<li class="'.$subClass.'"><a href="', $link, '">', $subTabText, '</a></li>', "\n";
                    }
                }
                else if (strpos($link, 'a=administration') !== false)
                {
                    /* Administration subtab. */
                    if ($_SESSION['CATS']->getAccessLevel('settings.administration') >= ACCESS_LEVEL_DEMO)
                    {
                        echo '<li class="'.$subClass.'"><a href="', $link, '">', $subTabText, '</a></li>', "\n";
                    }
                }
                else if (strpos($link, 'a=customizeEEOReport') !== false)
                {
                    /* EEO Report subtab.  Shouldn't be visible if EEO tracking is disabled. */
                    $EEOSettings = new EEOSettings($_SESSION['CATS']->getSiteID());
                    $EEOSettingsRS = $EEOSettings->getAll();

                    if ($EEOSettingsRS['enabled'] == 1)
                    {
                        echo '<li class="'.$subClass.'"><a href="', $link, '">', $subTabText, '</a></li>', "\n";
                    }
                }


                /* Tab is ok to draw. */
                else if ($link != '')
                {
                    /* Normal subtab. */
                    echo '<li class="'.$subClass.'"><a href="', $link, '">', $subTabText, '</a></li>', "\n";
                }
            }

            if (!eval(Hooks::get('TEMPLATE_UTILITY_DRAW_SUBTABS'))) return;

            echo '</ul>';
        }

        echo '</li>';
    }
    echo '        </ul>';
    echo '    </div>';
    echo '</aside>';
}

function opencats2017_scripts(){
    oc_enqueue_script('opencats2017-sweettitles', '/js/sweetTitles.js');
    oc_enqueue_script('opencats2017-datagrid', '/js/dataGrid.js');
    oc_enqueue_script('opencats2017-datagridfilters', '/js/dataGridFilters.js');
    oc_enqueue_script('opencats2017-bootstrap', get_template_directory_uri() . '/js/bootstrap.min.js');

    oc_enqueue_style( 'opencats2017-bootstrap', get_template_directory_uri() . '/css/bootstrap.min.css');
    oc_enqueue_style( 'opencats2017-fontawesome', get_template_directory_uri() . '/css/font-awesome.min.css');
    oc_enqueue_style( 'opencats2017-pe', get_template_directory_uri() . '/css/pe-icon-7-stroke.css');
    oc_enqueue_style( 'opencats2017-pehelper', get_template_directory_uri() . '/css/helper.css');
    oc_enqueue_style( 'opencats2017-homer', get_template_directory_uri() . '/css/style.css');
}
add_filter('oc_enqueue_scripts', 'opencats2017_scripts');