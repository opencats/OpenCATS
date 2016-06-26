<?php /* $Id: ChangePassword.tpl 1927 2007-02-22 06:03:24Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
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

	    <?php if ($this->auth_mode == "ldap"): ?>
		<p class="note">LDAP Enabled. Password cannot be changed from OpenCATS</p>
	    <?php endif; ?>
            <p class="note">Change Password</p>

            <form name="changePasswordForm" id="changePasswordForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=changePassword" method="post" onsubmit="return checkChangePasswordForm(document.changePasswordForm);">
                <input type="hidden" name="postback" id="postback" value="postback" />


                <?php if ($this->isDemoUser): ?>
                    Note that as a demo user, you do not have privileges to modify any settings.
                    <br /><br />
                <?php endif; ?>

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
                            <label id="currentPasswordLabel" for="currentPassword">Current Password:</label>&nbsp;
                        </td>
                        <td>
                            <input type="password" class="inputbox" id="currentPassword" name="currentPassword" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label id="newPasswordLabel" for="newPassword">New Password:</label>&nbsp;
                        </td>
                        <td>
                            <input type="password" class="inputbox" id="newPassword" name="newPassword" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <label id="retypeNewPasswordLabel" for="retypeNewPassword">Retype New Password:</label>&nbsp;
                        </td>
                        <td>
                            <input type="password" class="inputbox" id="retypeNewPassword" name="retypeNewPassword" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <br />
                            <input type="submit" class="button" id="changePassword" name="changePassword" value="Change Password" />
                            <input type="reset"  class="button" id="reset"          name="reset"          value="Reset" />
                            <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
                       </td>
                    </tr>
                </table>
            </form>

            <script type="text/javascript">
                document.changePasswordForm.currentPassword.focus();
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
