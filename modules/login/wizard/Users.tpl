<style>
table.userTable { border: 1px solid #666666; }
td.userTitleColumn { background-color: #8DC4FF; padding: 5px; font-weight: bold; font-size: 12px; }
td.userColumn1 { background-color: #EFF1FF; font-weight: normal; font-size: 12px; font-weight: normal; padding: 5px; }
td.userColumn2 { background-color: #E7EAF9; font-weight: normal; font-size: 12px; font-weight: normal; padding: 5px; }
td.userIDColumn { color: #888888; font-size: 10px; }
a.userLink { font-size: 12px; }
td.informativeColumn { font-size: 14px; }
table.informativeTable { margin: 10px 0 10px 0; }
td.userAddColumn { font-size: 12px; padding: 10px; }
input.userAddField { border: 1px solid #c0c0c0; padding: 3px; }
input.userAddField:hover { background-color: #f0f0f0; border: 1px solid #666666; padding: 3px; }
input.userAddField:focus { background-color: #D7E0F5; border: 1px solid #666666; padding: 3px; }
#addUserContainer { background-color: #f0f0f0; padding: 10px; border: 1px solid #666666; width: 650px; }

#contentAddUser { position: absolute; left: 150px; top: 75px; margin: 5px 0 5px 0; padding: 20px; visibility: hidden; }
</style>
<b>Setup users who can access your OpenCATS site.</b>
<br />
<br />
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="userTable">
    <tr>
        <td class="userTitleColumn">&nbsp;</td>
        <td class="userTitleColumn">Name</td>
        <td class="userTitleColumn">Login Name</td>
        <td class="userTitleColumn">Access Level</td>
        <td class="userTitleColumn">Email Address</td>
        <td class="userTitleColumn">&nbsp;</td>
    </tr>
    <?php $shade = false; ?>
    <?php for ($i=0; $i<count($this->users) || $i < 6; $i++): ?>
        <?php if (isset($this->users[$i])) $user = $this->users[$i]; else $user = null; ?>
        <tr>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?> userIDColumn" nowrap="nowrap"><?php echo $user ? ($i + 1) : '&nbsp;'; ?></td>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?>" nowrap="nowrap"><?php echo $user ? $user['lastName'] . ', ' . $user['firstName'] : ''; ?></td>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?>" nowrap="nowrap"><?php echo $user ? substr($user['username'], 0, strpos($user['username'], '@')) : '&nbsp;'; ?></td>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?>" nowrap="nowrap">
                <?php if ($user): ?>
                    <?php foreach ($this->accessLevels as $level): ?>
                        <?php if ($level['accessID'] == $user['accessLevel']): ?><?php echo $level['shortDescription']; ?><?php endif; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </td>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?>" nowrap="nowrap"><?php echo $user ? $user['email'] : '&nbsp;'; ?></td>
            <td class="userColumn<?php echo $shade ? '1' : '2'; ?>" nowrap="nowrap"><?php echo $user && intval($user['accessLevel']) < ACCESS_LEVEL_SA ? '<a href="javascript:void(0);" onclick="deleteUser(' . $user['userID'] . ');">Delete</a>' : '&nbsp;'; ?></td>
        </tr>
        <?php $shade = !$shade; ?>
    <?php endfor; ?>
</table>

<?php if ($this->userLicenses != 0 && $this->totalUsers >= $this->userLicenses): ?>
<div style="font-size: 14px; margin-top: 10px;">
You have <b><?php echo StringUtility::cardinal($this->userLicenses); ?></b> user licenses and they have all been used.
You can get more by clicking <b>Settings</b>-><b>Administration</b>-><b>User Management</b> once you finish this wizard.
</div>
<?php else: ?>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="informativeTable">
    <tr>
        <td align="left" valign="top" class="informativeColumn">
            <?php if ($this->userLicenses != 0): ?>
            You are using <b><?php echo StringUtility::cardinal($this->totalUsers); ?></b> of your <b><?php echo StringUtility::cardinal($this->userLicenses); ?></b> user licenses.
            <?php endif; ?>
        </td>
        <td align="right" valign="top">
            <a href="javascript:void(0);" class="userLink" id="addUserLink" onclick="showAddUser();">Add User</a>
        </td>
    </tr>
</table>
<?php endif; ?>

<?php if (count($this->users) == 1): ?>
We strongly suggest adding another user to help you utilize the "team" features of CATS.
<?php endif; ?>

<div id="contentAddUser">
    <div style="background-color: #666666; padding: 5px;">
        <table cellpadding="0" cellspacing="0" border="0" width="650">
            <tr>
                <td align="left" valign="middle" style="font-size: 12px; font-weight: bold;color: #ffffff; ">Add User</td>
                <td align="right" valign="top" style="font-size: 12px; font-weight: bold;color: #ffffff; cursor: pointer;" onclick="cancelAddUser();">X</td>
            </tr>
        </table>
    </div>
    <div id="addUserContainer">
        <table cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="userAddColumn">First Name:</td>
                <td class="userAddColumn"><input type="text" name="firstName" id="firstName" class="userAddField" size="20" maxlength="20" /> &nbsp;&nbsp;&nbsp;
                Last Name: &nbsp;&nbsp;<input type="text" name="lastName" id="lastName" class="userAddField" size="20" maxlength="20" /></td>
            </tr>
            <tr>
                <td class="userAddColumn">Password: </td>
                <td class="userAddColumn" colspan="3"><input type="password" name="password1" id="password1" class="userAddField" size="15" maxlength="15" /> &nbsp;&nbsp;&nbsp;
                Re-type: &nbsp;&nbsp;<input type="password" name="password2" id="password2" class="userAddField" size="15" maxlength="15" /></td>
            </tr>
            <tr>
                <td class="userAddColumn">Login Name:</td>
                <td colspan="3" class="userAddColumn"><input type="text" name="loginName" id="loginName" class="userAddField" size="30" maxlength="15" /></td>
            </tr>
            <tr>
                <td class="userAddColumn">E-mail:</td>
                <td colspan="3" class="userAddColumn"><input type="text" name="email" id="email" class="userAddField" size="50" maxlength="255" /></td>
            </tr>
            <tr>
                <td class="userAddColumn" valign="top">Access Level:</td>
                <td colspan="3" class="userAddColumn" valign="top">
                    <?php foreach ($this->accessLevels as $level): ?>
                        <?php if (intval($level['accessID']) < ACCESS_LEVEL_SA && intval($level['accessID']) > ACCESS_LEVEL_DISABLED ): ?>
                            <input type="radio" name="accessLevel" id="accessLevel<?php echo $level['accessID']; ?>" value="<?php echo $level['accessID']; ?>"<?php if (intval($level['accessID']) == ACCESS_LEVEL_DELETE): ?> checked<?php endif; ?> /> <?php echo $level['longDescription']; ?><br />
                        <?php endif; ?>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <td align="right" colspan="4" style="padding-top: 10px;">
                    <input type="button" class="button" id="cancelAddUser" value="Cancel" onclick="cancelAddUser();" />
                    <input type="button" class="button" id="addUser" value="Add User" onclick="addUser();" />
                </td>
            </tr>
        </table>
    </div>
</div>
