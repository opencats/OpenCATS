<?php /* $Id: AddUser.tpl 3810 2007-12-05 19:13:25Z brian $ */ ?>
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
                    <td valign="bottom"><h2>Create a User</h2></td>
                </tr>
            </table>

            <form name="UserForm" id="UserForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=addUser" method="post" onsubmit="return CheckMyForm(document.UserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
				<input type="hidden" name="SetPass" id="SetPass" value="1" />
                <table width="930">
                    <tr>
                        <td align="left" valign="top">
                            <table class="editTable" width="550">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="firstNameLabel" for="firstName">First Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="First" name="First" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="lastNameLabel" for="lastName">Last Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="Last" name="Last" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="emailLabel" for="username">E-Mail:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="EmailAddress" name="EmailAddress" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="usernameLabel" for="username">Username:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" class="inputbox" id="UserName" name="UserName" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="passwordLabel" for="password">Password:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="password" class="inputbox" id="Password" name="Password" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="retypePasswordLabel" for="retypePassword">Retype Password:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="password" class="inputbox" id="Password2" name="Password2" style="width: 150px;" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="accessLevelLabel" for="accessLevel">Access Level:</label>
                                    </td>
                                    <td class="tdData">
                                        <span id="accessLevelsSpan">
                                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                                <?php if ($accessLevel['accessID'] > $this->accessLevel): continue; endif; ?>
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
                                        <td class="tdVertical">Can view EEO Information:</td>
                                        <td class="tdData">
                                            <span id="eeoIsVisibleCheckSpan">
                                                <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible">
                                                <span id="eeoVisibleSpan"> </span>Check for YES, uncheck for NO.
                                            </span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                
                            </table>
                        </td>
                        
                    </tr>
                </table>

                <input type="submit" class="button" name="submit" id="submit" value="Add User" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='Delete - All lower access, plus the ability to delete information on the system.'" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=manageUsers');" />
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