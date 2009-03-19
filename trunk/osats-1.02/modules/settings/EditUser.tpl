<?php /* $Id: EditUser.tpl 2881 2007-08-14 07:47:26Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validateme.js', 'js/sorttable.js')); ?>
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
                    <td><h2>Settings: Edit Site User</h2></td>
                </tr>
            </table>

            <p class="note">
                <span style="float: left;">Edit Site User</span>
                <span style="float: right;"><a href='<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="UserForm" id="UserForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=editUser" method="post" onsubmit="return CheckMyForm(document.UserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="userID" name="userID" value="<?php $this->_($this->data['userID']); ?>" />

                <table class="editTable" width="600">
                    <tr>
                        <td class="tdVertical">
                            <label id="firstNameLabel" for="firstName">First Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="First" name="First" value="<?php $this->_($this->data['firstName']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="lastNameLabel" for="lastName">Last Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="Last" name="Last" value="<?php $this->_($this->data['lastName']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="emailLabel" for="email">E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="EmailAddress" name="EmailAddress" value="<?php $this->_($this->data['email']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="usernameLabel" for="username">Username:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="UserName" name="UserName" value="<?php $this->_($this->data['username']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Access Level:</label>
                        </td>
                        <td class="tdData">
                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                <?php if ($accessLevel['accessID'] > $this->accessLevel): continue; endif; ?>

                                <?php $radioButtonID = 'access' . $accessLevel['accessID']; ?>

                                <input type="radio" name="accessLevel" id="<?php echo($radioButtonID); ?>" value="<?php $this->_($accessLevel['accessID']); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>"<?php if ($this->data['accessLevel'] == $accessLevel['accessID']): ?> checked<?php endif; ?><?php if (($this->disableAccessChange && $accessLevel['accessID'] > ACCESS_LEVEL_READ) || ($this->currentUser == $this->data['userID'])): ?> disabled<?php endif; ?> onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($accessLevel['longDescription']); ?>'" />
                                <label for="<?php echo($radioButtonID); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>">
                                    <?php $this->_($accessLevel['shortDescription']); ?>
                                    <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>(Default)<?php endif; ?>
                                </label>
                                <br />
                            <?php endforeach; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">Access Description:</td>
                        <td class="tdData">
                            <span id="userAccessStatus" style='font-size: smaller'>
                                <?php if ($this->currentUser == $this->data['userID']): ?>
                                    You are a <?php $this->_($this->data['accessLevelLongDescription']); ?> You cannot edit your own access level.
                                <?php else: ?>
                                    <?php $this->_($this->data['accessLevelLongDescription']); ?>
                                <?php endif; ?>
                            </span>
                        </td>
                    </tr>

                    <?php if (count($this->categories) > 0): ?>
                        <tr>
                            <td class="tdVertical">
                                <label id="accessLevelLabel" for="accessLevel">Role:</label>
                            </td>
                            <td class="tdData">
                               <input type="radio" name="role" value="none" title="" <?php if ($this->data['categories'] == ''): ?>checked<?php endif; ?> onclick="document.getElementById('userRoleDesc').innerHTML='This user is a normal user.';"/> Normal User
                               <?php $roleDesc = "This user is a normal user."; ?>
                               <br />
                            </td>
                        </tr>
                        <tr>
                            <td class="tdVertical">Role Description:</td>
                            <td class="tdData">
                                <span id="userRoleDesc" style='font-size: smaller'><?php $this->_($roleDesc); ?></span>
                            </td>
                        </tr>
                    <?php else: ?>
                        <span style="display:none;">
                            <input type="radio" name="role" value="none" title="" checked /> Normal User
                        </span>
                    <?php endif; ?>
                    
                    <?php if($this->EEOSettingsRS['enabled'] == 1): ?>                    
                         <tr>
                            <td class="tdVertical">Can view EEO Information:</td>
                            <td class="tdData">
                                <span id="eeoIsVisibleCheckSpan">
                                                <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible">
                                                <span id="eeoVisibleSpan"> </span>Check for YES, uncheck for NO.
                                            </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
                    <tr id="passwordResetElement1">
                        <td class="tdVertical">
                            <label id="PasswordResetLabel" for="username">Password Reset:</label>
                        </td>
                        <td class="tdData">
                            <input type="button" class="button" name="passwordreset" id="passwordreset" value="Reset Password" onclick="javascript:document.getElementById('passwordResetElement1').style.display = 'none'; document.getElementById('passwordResetElement2').style.display = ''; document.getElementById('passwordResetElement3').style.display = ''; document.getElementById('Password').value=''; document.getElementById('SetPass').value='1';" />
                            <input type="hidden" id="SetPass" name="SetPass" value="0" />
                        </td>
                    </tr>

                    <tr id="passwordResetElement2" style="display:none;">
                        <td class="tdVertical">
                            <label id="password1Label" for="Password">New Password:</label>
                        </td>
                        <td class="tdData">
                                <input type="password" class="inputbox" id="Password" name="Password" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr id="passwordResetElement3" style="display:none;">
                        <td class="tdVertical">
                            <label id="password2Label" for="password2">Retype Password:</label>
                        </td>
                        <td class="tdData">
                                <input type="password" class="inputbox" id="Password2" name="Password2" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                </table>
				<input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($this->data['accessLevelLongDescription']); ?>'" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($this->data['userID']); ?>');" />
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
