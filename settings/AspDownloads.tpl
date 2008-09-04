<?php /* $Id: AspDownloads.tpl 3367 2007-10-31 22:24:34Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/downloads.css')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>

<?php // <---------------------------------- FIXME: Move to ASP Hook? ?>
<script>
function sendNotificationEmail()
{
    var ajaxObj;
    try
    {
        // Firefox, Opera 8.0+, Safari
        ajaxObj = new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            ajaxObj = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    ajaxObj.onreadystatechange = function()
    {
        // no response necessary
    }
    ajaxObj.open("GET","<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=downloads&sendDevEmail=true",true);
    ajaxObj.send(null);
}
</script>
<?php
if (isset($_GET['sendDevEmail']) && !strcmp($_GET['sendDevEmail'], 'true') && file_exists('modules/asp') &&
    $_SESSION['CATS'] && $_SESSION['CATS']->isLoggedIn())
{
    if (isset($_COOKIE['CATS_firefoxToolbar']) && !strcmp($_COOKIE['CATS_firefoxToolbar'], 'true')) exit(0);

    include_once('./modules/asp/lib/ASPUtility.php');
    ASPUtility::sendDevEmail(
        'Firefox Download',
        sprintf(
            'User(<b>%s</b>) @ Site(<b>%s</b>) has clicked to download the Firefox toolbar.',
            ucwords($_SESSION['CATS']->getUsername()),
            ucwords($_SESSION['CATS']->getSiteName())
        )
    );
    // Set a cookie so we don't get multiple e-mails from the same computer
    setcookie('CATS_firefoxToolbar', 'true', time()+(60*60*24*7*15), null, null, null, null);
    exit(0);
}
// End Move to Asp Hook ---------------------------------->
?>


    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Downloads</h2></td>
                </tr>
            </table>

            <table width="100%">
                <tr>
                    <td width="100%">
                        <p class="noteUnsized">Additional tools to enhance your productivity.</p>

                        <table class="searchTable" width="100%">
                            <tr>
                                <td width="230" valign="top">
                                    <img src="images/i-firefox-small.gif" class="absmiddle" alt="" />
                                    <a href="javascript:void(0);" onclick="tryInstall();">
                                        Install CATS Firefox Toolbar
                                    </a>
                                </td>
                                <td>
                                    Integrate CATS with Monster, Hotjobs and more through the Firefox Web Browser.&nbsp;&nbsp;<a href="index.php?a=addons">Learn More</a>
                                    <div id="settingsDownloads">
                                        <h4>Internet Browsers</h4>
                                        <table class="downloadSupportedGrid" cellpadding="0" cellspacing="0">
                                            <tr class="headers">
                                                <td>&nbsp;</td>
                                                <td><img src="images/i-firefox.gif" /> <span>Firefox</span></td>
                                                <td class="medGrid"><img src="images/i-ie.gif" /> <span>Internet Explorer</span></td>
                                                <td><img src="images/i-opera-grayed.gif" /> <span>Opera</span></td>
                                                <td><img src="images/i-safari-grayed.gif" /> <span>Safari</span></td>
                                            </tr>
                                            <tr>
                                                <td class="category">CATS Toolbar</td>
                                                <td><img src="images/blue_check.gif" /></td>
                                                <td>coming soon</td>
                                                <td><img src="images/gray_x.gif" /></td>
                                                <td><img src="images/gray_x.gif" /></td>
                                            </tr>
                                        </table>

                                        <h4>Email Clients</h4>
                                        <table class="downloadSupportedGrid" cellpadding="0" cellspacing="0">
                                            <tr class="headers">
                                                <td>&nbsp;</td>
                                                <td colspan="2"><img src="images/i-thunderbird.gif" /> <span>Thunderbird</span></td>
                                                <td colspan="2"><img src="images/i-outlook.gif" /> <span>Outlook</span></td>
                                            </tr>
                                            <tr>
                                                <td class="category">CATS Plug-in</td>
                                                <td colspan="2">coming soon</td>
                                                <td colspan="2">coming soon</td>
                                            </tr>
                                        </table>
                                    </div>

                                    <span id="notFirefox" style="display:none;">

                                    </span>
                                    <script type="text/javascript">
                                        <?php if (false): ?>
                                            function tryInstall()
                                            {
                                                 showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=getPaidModal', 400, 270, null); return false;
                                            }
                                        <?php else: ?>
                                            function tryInstall()
                                            {
                                                var isFirefox = false;

                                                /* Browser Detection */
                                                if(navigator.userAgent.indexOf("Firefox")!=-1) {
                                                    var versionindex=navigator.userAgent.indexOf("Firefox")+8
                                                    if (parseInt(navigator.userAgent.charAt(versionindex))>=1) {
                                                       isFirefox = true;
                                                    }
                                                }

                                                if (!isFirefox) {
                                                    showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=getFirefoxModal', 400, 270, null); return false;
                                                }
                                                else {
                                                    xpi = new Object();
                                                    <?php if(ModuleUtility::moduleExists('asp') && false): ?>
														<?php /* TODO:  Toolbar generated automatically with username and password. */ ?>
                                                        xpi["CATS ToolBar"] = "http://www.catsone.com/extensions/firefox/catstoolbargenerator.php?" +
                                                            "username=<?php echo(urlencode($_SESSION['CATS']->getUsername())); ?>&" +
                                                            "password="+rot13("<?php echo(str_rot13(urlencode($_SESSION['CATS']->getPassword()))); ?>")+"&" +
                                                            "url=<?php echo(urlencode('https://'.$_SESSION['CATS']->getUnixName())); ?>.catsone.com/";
                                                    <?php else: ?>
                                                        xpi["CATS ToolBar"] = "http://www.catsone.com/extensions/firefox/catstoolbar.xpi";
                                                    <?php endif; ?>
                                                    InstallTrigger.install(xpi);
                                                    if (typeof sendNotificationEmail == 'function') sendNotificationEmail();
                                                }
                                            }
                                        <?php endif; ?>
                                    </script>
                                </td>
                            </tr>
                        </table>
                        <br />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
