<?php
        $username     = $_SESSION['CATS']->getUsername();
        $siteName     = $_SESSION['CATS']->getSiteName();
        $fullName     = $_SESSION['CATS']->getFullName();
        $indexName    = CATSUtility::getIndexName();
        $showTopRight = true;
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset='<?php print HTML_ENCODING ?>" />
  <link rel="icon" href="/images/favicon.ico" type="image/x-icon" />
  <link rel="shortcut icon" href="/images/favicon.ico" type="image/x-icon" />
  <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php print CATSUtility::getIndexName() ?>?m=rss" />
  <!--[if IE]><link rel="stylesheet" type="text/css" href="ie.css" /><![endif]-->
  <![if !IE]><link rel="stylesheet" type="text/css" href="not-ie.css" /><![endif]>
  <?php oc_head() ?>
</head>
<body>
<div id="header">
    <div class="color-line">
    </div>
    <div id="logo" class="light-version">
            <img src="images/applicationLogo.jpg" border="0" alt="CATS Applicant Tracking System" class="img-responsive" />
    </div>
    <nav role="navigation">
        <div class="small-logo">
            <span class="text-primary">OpenCATS</span>
        </div>
        <div class="navbar-right">
            <ul class="nav navbar-nav no-borders">
                <li class="dropdown"><a href="#"><?php print $fullName ?>&nbsp;&lt;<?php print $username ?>&gt;&nbsp;(<?php print $siteName ?>)></a></li>
                <li>
                    <a href="<?php print $indexName ?>?m=logout">
                        <i class="pe-7s-upload pe-rotate-90"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>
<!--

        <div id="headerBlock">

        <table cellspacing="0" cellpadding="0" style="margin: 0px; padding: 0px; float: left;">
        <tr>
        <td rowspan="2"></td>
        </tr>
        </table>

        <?php if (!eval(Hooks::get('TEMPLATE_LIVE_CHAT'))) return; ?>

        <?php if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_PRE_TOP_RIGHT'))) return; ?>
        <?php
        if ($showTopRight)
        {
            // FIXME: Use common functions.
            // FIXME: Isn't the UNIX-name stuff ASP specific? Hook?
            if (strpos($username, '@'.$_SESSION['CATS']->getSiteID()) !== false &&
                substr($username, strpos($username, '@'.$_SESSION['CATS']->getSiteID())) ==
                '@'.$_SESSION['CATS']->getSiteID() )
            {
               $username = str_replace('@'.$_SESSION['CATS']->getSiteID(), '', $username);
            }
        ?>
            <?php if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_1'))) return; ?>

            <div id="topRight">

            <div style="padding-bottom: 8px;">
            <?php if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_UPGRADE'))) return; ?>

            <?php if ((!file_exists('modules/asp') || (defined('CATS_TEST_MODE') && CATS_TEST_MODE)) && LicenseUtility::isProfessional() &&
                $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA)
            {
                if (abs(LicenseUtility::getExpirationDate() - time()) < 60*60*24*30)
                {
                    $daysLeft = abs(LicenseUtility::getExpirationDate() - time())/60/60/24;
            ?>
                    <a href="http://www.catsone.com/professional" target="_blank">
                    <img src="images/tabs/small_upgrade.jpg" border="0" />
                    License expires in <?php print number_format($daysLeft, 0) ?> days, Renew?</a>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php }
                else
                { ?>
                    <a href="http://www.catsone.com/professional" target="_blank">
                    <img src="images/tabs/small_upgrade.jpg" border="0" />
                    CATS Professional Account Login</a>&nbsp;&nbsp;&nbsp;&nbsp;
                    <?php
                }
            }


            if (!file_exists('modules/asp') && !LicenseUtility::isProfessional())
            {
                ?>
                <a href="http://www.catsone.com/professional" target="_blank">
                <img src="images/tabs/small_upgrade.jpg" border="0" />
                <b>For more features, upgrade to CATS Professional</b></a>&nbsp;&nbsp;&nbsp;&nbsp;
                <?php
            }
            ?>

            <a href="<?php print $indexName ?>?m=logout">
            <img src="images/tabs/small_logout.jpg" border="0" />
            Logout</a>
            </div>

            <?php if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_EXTENDED_SITE_NAME'))) return; ?>

            <span></span>

            <?php if ($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA) { ?>
                &nbsp;<span style="font-weight:bold;">Administrator</span>
            <?php } ?>

            <br />
            <?php
            $systemInfo = new SystemInfo();
            $systemInfoData = $systemInfo->getSystemInfo();

            if (isset($systemInfoData['available_version']) &&
                $systemInfoData['available_version'] > CATSUtility::getVersionAsInteger() &&
                isset($systemInfoData['disable_version_check']) &&
                !$systemInfoData['disable_version_check'] &&
                $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) >= ACCESS_LEVEL_SA)
            { ?>
                <a href="http://www.catsone.com/download.php" target="catsdl">A new CATS version is available!</a><br />
            <?php }

            /* Disabled notice */
            if (!$_SESSION['CATS']->accountActive())
            { ?>
                <span style="font-weight:bold;">Account Inactive</span><br />
            <?php }elseif($_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT) == ACCESS_LEVEL_READ){ ?>
                <span>Read Only Access</span><br />
            <?php }else{
                if (!eval(Hooks::get('TEMPLATE_LOGIN_INFO_TOP_RIGHT_2_ELSE'))) return;
            } ?>

            </div>
        <?php } ?>

        </div>
-->
<?php print opencats2017_nav() ?>
<div id="wrapper">
<?php TemplateUtility::printQuickSearch(); ?>