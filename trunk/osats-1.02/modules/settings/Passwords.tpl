<?php /* $Id: Passwords.tpl 1606 2007-02-04 23:27:58Z will $ */ ?>
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
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Passwords</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <table class="editTable" width="700">
                            <tr>
                                <td class="tdVertical" style="width:320px;">
                                    Allow retrieval of forgotten passwords through email:
                                </td>
                                <td class="tdData">
                                    <input type="checkbox" name="ForgottenPasswords" enabled>
                                </td>
                            </tr>
                        </table>
                        <input type="button" name="back" class="button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
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