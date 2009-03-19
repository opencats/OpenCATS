<?php /* $Id: CustomizeReports.tpl 1535 2007-01-22 17:55:29Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td align="left"><h2>Settings: Reports</h2></td>
                </tr>
            </table>

            <p class="note">Report Settings</p>
            <table>
                <tr>
                    <td>
                        <form name="editCalendarForm" id="editCalendarForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=customizeCalendar" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <table class="editTable" width="700">
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        URL to logo image for report:
                                    </td>
                                    <td class="tdData">
                                        <input type="textbox" class="textbox" name="reportImageURL" style="width:400px;">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Text caption for logo:
                                    </td>
                                    <td class="tdData">
                                        <input type="textbox" class="textbox" name="reportImageURL" style="width:400px;">
                                    </td>
                                </tr>
                            </table>
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
                            <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
                            <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" />&nbsp;
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
