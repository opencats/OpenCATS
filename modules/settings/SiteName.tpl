<?php /* $Id: SiteName.tpl 1930 2007-02-22 08:39:53Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" alt="Settings" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Change Site Name</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeSiteNameForm" onsubmit="return checkSiteNameForm(document.changeSiteNameForm);" method="post" autocomplete="off">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeSiteName" />
                            Current site name: <?php echo($_SESSION['CATS']->getSiteName())?><br />
                            <br />
                            <label id="siteNameLabel" for="siteName">New Site Name:</label>
                            <br />
                            <input type="text" name="siteName" id="siteName" value="<?php echo($_SESSION['CATS']->getSiteName())?>" style="width:250px;" /><br /><br />
                            <input type="submit" name="save" class = "button" value="Save" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form>
                    </td>
                </tr>
            </table>

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
