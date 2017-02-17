<?php /* $Id: EditUser.tpl 2881 2007-08-14 07:47:26Z brian $ */ ?>
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
                    <td><h2>Settings: Edit Site User</h2></td>
                </tr>
            </table>

            <p class="note">
                <span style="float: left;">Edit Site User</span>
                <span style="float: right;"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="editUserForm" id="editUserForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=editUser" method="post" onsubmit="return checkEditUserForm(document.editUserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="userID" name="userID" value="<?php $this->_($this->data['userID']); ?>" />

                <table class="editTable" width="600">
                    <tr>
                        <td class="tdVertical">
                            <label id="firstNameLabel" for="firstName">First Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="firstName" name="firstName" value="<?php $this->_($this->data['firstName']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="lastNameLabel" for="lastName">Last Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="lastName" name="lastName" value="<?php $this->_($this->data['lastName']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="emailLabel" for="email">E-Mail:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="email" name="email" value="<?php $this->_($this->data['email']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="usernameLabel" for="username">Username:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" id="username" name="username" value="<?php $this->_($this->data['username']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Access Level:</label>
                        </td>
                        <td class="tdData">
                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                <?php if ($accessLevel['accessID'] > $this->getUserAccessLevel('')): continue; endif; ?>

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
                                <?php if ($this->cannotEnableMessage): ?>
                                    You cannot make this account active  without disabling another account or upgrading your license, as doing so would cause you to exceed your allowable number of active accounts.
                                <?php elseif ($this->currentUser == $this->data['userID']): ?>
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
                               <?php foreach ($this->categories as $category): ?>
                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>"  <?php if ($this->data['categories'] == $category[1]): ?>checked<?php $roleDesc = $category[2]; ?><?php endif; ?> onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo($category[2]); ?>';" /> <?php $this->_($category[0]); ?>
                                   <br />
                               <?php endforeach; ?>
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
                            <td class="tdVertical">Allowed to view EEO Information:</td>
                            <td class="tdData">
                                <span id="eeoIsVisibleCheckSpan">
                                    <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" <?php if ($this->data['canSeeEEOInfo'] == 1): ?>checked <?php endif; ?>onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';">
                                    &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
                                </span>
                            </td>
                        </tr>
                    <?php endif; ?>
                    
		    <?php if ($this->auth_mode != "ldap"): ?>
                    <tr id="passwordResetElement1">
                        <td class="tdVertical">
                            <label id="PasswordResetLabel" for="username">Password Reset:</label>
                        </td>
                        <td class="tdData">
                            <input type="button" class="button" name="passwordreset" id="passwordreset" value="Reset Password" onclick="javascript:document.getElementById('passwordResetElement1').style.display = 'none'; document.getElementById('passwordResetElement2').style.display = ''; document.getElementById('passwordResetElement3').style.display = ''; document.getElementById('password1').value=''; document.getElementById('passwordIsReset').value='1';" />
                            <input type="hidden" id="passwordIsReset" name="passwordIsReset" value="0" />
                        </td>
                    </tr>
                    <?php endif; ?>

                    <tr id="passwordResetElement2" style="display:none;">
                        <td class="tdVertical">
                            <label id="password1Label" for="password1">New Password:</label>
                        </td>
                        <td class="tdData">
                                <input type="password" class="inputbox" id="password1" name="password1" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr id="passwordResetElement3" style="display:none;">
                        <td class="tdVertical">
                            <label id="password2Label" for="password2">Retype Password:</label>
                        </td>
                        <td class="tdData">
                                <input type="password" class="inputbox" id="password2" name="password2" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                </table>
                <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($this->data['accessLevelLongDescription']); ?>'" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($this->data['userID']); ?>');" />
            </form>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
