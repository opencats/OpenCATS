<?php /* $Id: ChangePassword.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validateme.js', 'js/sorttable.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
<?php 
// get current password into the mycurpassword variable
/* not finished and only for testing. Jamin
include ('./dbconfig.php');
$myServer = mysql_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS);
$myDB = mysql_select_db(DATABASE_NAME, $myServer);
$sql = "select * from user where user_id = '" . $this->_($this->data['userID']) . "'";
$result = mysql_query($sql);
$mycurpassword = mysql_fetch_row($result);
$mycurpassword = $mycurpassword[4];
mysql_close();
*/
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: My Profile</h2></td>
                </tr>
            </table>

            <p class="note">Change Password</p>

            <form name="PwdUserForm" id="PwdUserForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=changePassword" method="post" onsubmit="return ChangePass(document.PwdUserForm);">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <table class="searchTable">
                    <tr>
                        <td colspan="2">
                            <span class="bold">Change Password</span>
                            <br />
                            <br />
                            <span id='passwordErrorMessage' style="font:smaller; color: red">
                                <?php if (isset($this->errorMessage)): ?>
                                        <?php $this->_($this->errorMessage); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label id="newPasswordLabel" for="newPassword">New Password:</label>&nbsp;
                        </td>
                        <td>
                            <input type="password" class="inputbox" id="Password" name="Password" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label id="retypeNewPasswordLabel" for="Password2">Retype New Password:</label>&nbsp;
                        </td>
                        <td>
                            <input type="password" class="inputbox" id="Password2" name="Password2" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <br />
                            <input type="submit" class="button" id="changePassword" name="changePassword" value="Change Password" />
                            <input type="reset"  class="button" id="reset"          name="reset"          value="Reset" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
                       </td>
                    </tr>
                </table>
            </form>
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
