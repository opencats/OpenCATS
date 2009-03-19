<?php /* $Id: MyProfile.tpl 2452 2007-05-11 17:47:55Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); //why is the validator.js here? Jamin.  ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <p class="note">Adjust and Manage Your Profile</p>

            <table width="100%">
                <tr>
                    <td width="100%">
                        <table class="searchTable" width="100%">
                            <tr>
                                <td width="33%">
    <?php //I want to allow the admin to change their own settings. Jamin 
	if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA)
    {
	?><a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($this->userID); ?>&amp;privledged=true"><?php
    }
	else
	{
	?><a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($this->userID); ?>&amp;privledged=false"><?php	
	}
	?>
									
                                        <img src="images/2dot1c.gif" alt="" border="0" /> View Profile
                                    </a>
                                	<br>
                                    View your current profile to verify your information is correct.
                                </td>
                                <td width="33%">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=changePassword">
                                        <img src="images/2dot1c.gif" alt="" border="0" /> Change Password
                                    </a>
                                	<br />
                                    Change your OSATS login password.
                                    <br />
                            </tr>
							<tr>
                                <td width="33%">
    							<br />
								<a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=notificationOptions">
                                        <img src="images/2dot1c.gif" alt="" border="0" /> Turn Notification Options - On or Off.
                                    </a>
                                	<br>
                                    Change how OSATS notifies you of new events. (needs to be fixed. Jamin)
									
                                </td>
                                <td width="33%">
                                    
                            </tr>
                            </table>
                            
                            
                    </td>
                </tr>
            </table>
            <br />
            <br />
           
<?php //check to see if admin. if so... load admin portion. Jamin 
if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA)
{
	include ('./modules/settings/adminsonly.php');
}
?>
		    
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