<?php /* $Id: AddUser.tpl 3810 2007-12-05 19:13:25Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%" valign="bottom">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td valign="bottom"><h2>Settings: Add Site User</h2></td>
                </tr>
            </table>

            <p class="note">
                <span style="float: left;">Add Site User</span>
                <span style="float: right;"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="addUserForm" id="addUserForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=addUser" method="post" onsubmit="return checkAddUserForm(document.addUserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <table width="930">
                    <tr>
                        <td align="left" valign="top">
                            <table class="editTable" width="550">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="firstNameLabel" for="firstName">First Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="firstName" name="firstName" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="lastNameLabel" for="lastName">Last Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="lastName" name="lastName" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="emailLabel" for="username">E-Mail:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="email" name="email" style="width: 150px;" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="usernameLabel" for="username">Username:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="username" name="username" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="passwordLabel" for="password">Password:</label>
                                    </td>
                                    <td class="tdData">
					<?php if ($this->auth_mode == "ldap"): ?>
					LDAP Authentication is enabled, hence password not required.
                            		<input type="hidden" class="inputbox" id="password" name="password" value="password" />
                            		<?php else: ?>
                                        <input type="password" class="inputbox" id="password" name="password" style="width: 150px;" />&nbsp;*
					<?php endif; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="retypePasswordLabel" for="retypePassword">Retype Password:</label>
                                    </td>
                                    <td class="tdData">
					<?php if ($this->auth_mode == "ldap"): ?>
                            		<input type="hidden" class="inputbox" id="retypePassword" name="retypePassword" value="password"/>
                             		<?php else: ?>
                                        <input type="password" class="inputbox" id="retypePassword" name="retypePassword" style="width: 150px;" />&nbsp;*
					<?php endif; ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="accessLevelLabel" for="accessLevel">Access Level:</label>
                                    </td>
                                    <td class="tdData">
                                        <span id="accessLevelsSpan">
                                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                                <?php if ($accessLevel['accessID'] > $this->getUserAccessLevel('settings.addUser')): continue; endif; ?>
                                                <?php if (!$this->license['canAdd'] && !$this->license['unlimited'] && $accessLevel['accessID'] > ACCESS_LEVEL_READ): continue; endif; ?>

                                                <?php $radioButtonID = 'access' . $accessLevel['accessID']; ?>

                                                <input type="radio" name="accessLevel" id="<?php echo($radioButtonID); ?>" value="<?php $this->_($accessLevel['accessID']); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>" <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>checked<?php endif; ?> onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($accessLevel['longDescription']); ?>'; <?php if($accessLevel['accessID'] >= ACCESS_LEVEL_SA): ?>document.getElementById('eeoIsVisible').checked=true; document.getElementById('eeoIsVisible').disabled=true;  document.getElementById('eeoVisibleSpan').style.display='none';<?php else: ?>document.getElementById('eeoIsVisible').disabled=false;<?php endif; ?>" />
                                                <label for="<?php echo($radioButtonID); ?>" title="<?php $this->_(str_replace('\'', '\\\'', $accessLevel['longDescription'])); ?>">
                                                    <?php $this->_($accessLevel['shortDescription']); ?>
                                                    <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>(Default)<?php endif; ?>
                                                </label>
                                                <br />
                                            <?php endforeach; ?>
                                        </span>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">Access Description:</td>
                                    <td class="tdData">
                                        <span id="userAccessStatus">Delete - All lower access, plus the ability to delete information on the system.</span>
                                    </td>
                                </tr>

                                <?php if (count($this->categories) > 0): ?>
                                    <tr>
                                        <td class="tdVertical">
                                            <label id="accessLevelLabel" for="accessLevel">Role:</label>
                                        </td>
                                        <td class="tdData">
                                           <input type="radio" name="role" value="none" title="" checked onclick="document.getElementById('userRoleDesc').innerHTML='This user is a normal user.';  document.getElementById('accessLevelsSpan').style.display='';" /> Normal User
                                           <br />
                                           <?php foreach ($this->categories as $category): ?>
                                               <?php if (isset($category[4])): ?>
                                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>" onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo(str_replace('\'', '\\\'', $category[2])); ?>'; document.getElementById('access<?php echo($category[4]); ?>').checked=true; document.getElementById('accessLevelsSpan').style.display='none';" /> <?php $this->_($category[0]); ?>
                                               <?php else: ?>
                                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>" onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo(str_replace('\'', '\\\'', $category[2])); ?>'; document.getElementById('accessLevelsSpan').style.display='';" /> <?php $this->_($category[0]); ?>
                                               <?php endif; ?>
                                               <br />
                                           <?php endforeach; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="tdVertical">Role Description:</td>
                                        <td class="tdData">
                                            <span id="userRoleDesc" style="font-size: smaller">This user is a normal user.</span>
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
                                                <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';">
                                                &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php if (!$this->license['canAdd'] && !$this->license['unlimited']): ?>
                                    <tr>
                                        <td class="tdVertical">Notice:</td>
                                        <td class="tdData" style="color: #800000;">
                                            <b>You are currently using your full allotment of active user accounts. Disable an existing account or upgrade your license to add another active user.</b>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </td>
                        <?php
                        eval(Hooks::get('SETTINGS_USERS_FULLQUOTALICENSES'));
                        ?>
                    </tr>
                </table>

                <input type="submit" class="button" name="submit" id="submit" value="Add User" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='Delete - All lower access, plus the ability to delete information on the system.'" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers');" />
            </form>
        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
