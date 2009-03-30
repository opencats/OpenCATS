<?php /* $Id: SiteName.tpl 1930 2007-02-22 08:39:53Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validateme.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
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
                        <form name="SiteNameForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=administration&s=siteName'" method="post" id="SiteNameForm" onsubmit="return CheckMySiteName(document.SiteNameForm);" autocomplete="off">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="ChangeSiteName" />
                            Current site name: <h3> <?php echo($_SESSION['OSATS']->getSiteName())?></h3>
                            <br />
                            <label id="siteNameLabel" for="SiteName">New Site Name:</label>
                            <br />
                            <input type="text" name="MySiteName" id="MySiteName" value="" style="width:250px;" /><br /><br />
                            <input type="submit" name="save" id="submit" class = "button" value="Save" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
                        </form>
                    </td>
                </tr>
            </table>

        </div>
<?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>