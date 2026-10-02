<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Edit Site User</h1>
            </header>

            <p class="d-flex flex-wrap justify-content-between gap-2 bg-secondary-subtle rounded p-2 mb-2 fw-semibold">
                <span >Edit Site User</span>
                <span class="ms-sm-auto"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="editUserForm" id="editUserForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=editUser" method="post" onsubmit="return checkEditUserForm(document.editUserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="userID" name="userID" value="<?php $this->_($this->data['userID']); ?>" />

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label id="firstNameLabel" for="firstName" class="form-label small mb-0">First Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" id="firstName" name="firstName" value="<?php $this->_($this->data['firstName']); ?>"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label id="lastNameLabel" for="lastName" class="form-label small mb-0">Last Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" id="lastName" name="lastName" value="<?php $this->_($this->data['lastName']); ?>"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label id="emailLabel" for="email" class="form-label small mb-0">E-Mail:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" id="email" name="email" value="<?php $this->_($this->data['email']); ?>"  class="form-control form-control-sm" />
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label id="usernameLabel" for="username" class="form-label small mb-0">Username:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" id="username" name="username" value="<?php $this->_($this->data['username']); ?>"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label id="notesLabel" for="notes" class="form-label small mb-0">Access Level:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                <?php if ($accessLevel['accessID'] > $this->getUserAccessLevel('')): continue; endif; ?>

                                <?php $radioButtonID = 'access' . $accessLevel['accessID']; ?>

                                <input type="radio" name="accessLevel" id="<?php echo($radioButtonID); ?>" value="<?php $this->_($accessLevel['accessID']); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>"<?php if ($this->data['accessLevel'] == $accessLevel['accessID']): ?> checked<?php endif; ?><?php if (($this->disableAccessChange && $accessLevel['accessID'] > ACCESS_LEVEL_READ) || ($this->currentUser == $this->data['userID'])): ?> disabled<?php endif; ?> onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($accessLevel['longDescription']); ?>'"  class="form-check-input" />
                                <label for="<?php echo($radioButtonID); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>" class="form-label small mb-0">
                                    <?php $this->_($accessLevel['shortDescription']); ?>
                                    <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>(Default)<?php endif; ?>
                                </label>
                                <br />
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-sm-4 col-lg-3">Access Description:</div>
                        <div class="col-12 col-sm">
                            <span id="userAccessStatus" style='font-size: smaller'>
                                <?php if ($this->cannotEnableMessage): ?>
                                    You cannot make this account active  without disabling another account or upgrading your license, as doing so would cause you to exceed your allowable number of active accounts.
                                <?php elseif ($this->currentUser == $this->data['userID']): ?>
                                    You are a <?php $this->_($this->data['accessLevelLongDescription']); ?> You cannot edit your own access level.
                                <?php else: ?>
                                    <?php $this->_($this->data['accessLevelLongDescription']); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>

                    <?php if (count($this->categories) > 0): ?>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-4 col-lg-3">
                                <label id="accessLevelLabel" for="accessLevel" class="form-label small mb-0">Role:</label>
                            </div>
                            <div class="col-12 col-sm">
                               <input type="radio" name="role" value="none" title="" <?php if ($this->data['categories'] == ''): ?>checked<?php endif; ?> onclick="document.getElementById('userRoleDesc').innerHTML='This user is a normal user.';" class="form-check-input" /> Normal User
                               <?php $roleDesc = "This user is a normal user."; ?>
                               <br />
                               <?php foreach ($this->categories as $category): ?>
                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>"  <?php if ($this->data['categories'] == $category[1]): ?>checked<?php $roleDesc = $category[2]; ?><?php endif; ?> onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo($category[2]); ?>';"  class="form-check-input" /> <?php $this->_($category[0]); ?>
                                   <br />
                               <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-4 col-lg-3">Role Description:</div>
                            <div class="col-12 col-sm">
                                <span id="userRoleDesc" style='font-size: smaller'><?php $this->_($roleDesc); ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <span style="display:none;">
                            <input type="radio" name="role" value="none" title="" checked  class="form-check-input" /> Normal User
                        </span>
                    <?php endif; ?>
                    
                    <?php if($this->EEOSettingsRS['enabled'] == 1): ?>                    
                         <div class="row g-2 mb-2">
                            <div class="col-sm-4 col-lg-3">Allowed to view EEO Information:</div>
                            <div class="col-12 col-sm">
                                <span id="eeoIsVisibleCheckSpan">
                                    <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" <?php if ($this->data['canSeeEEOInfo'] == 1): ?>checked <?php endif; ?>onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';" class="form-check-input">
                                    &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
                                </span>
                            </div>
                        </div>
                    <?php endif; ?>
                    
		    <?php if ($this->auth_mode != "ldap"): ?>
                    <div class="row g-2 mb-2" id="passwordResetElement1">
                        <div class="col-sm-4 col-lg-3">
                            <label id="PasswordResetLabel" for="username" class="form-label small mb-0">Password Reset:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="button" name="passwordreset" id="passwordreset" value="Reset Password" onclick="javascript:document.getElementById('passwordResetElement1').style.display = 'none'; document.getElementById('passwordResetElement2').style.display = ''; document.getElementById('passwordResetElement3').style.display = ''; document.getElementById('password1').value=''; document.getElementById('passwordIsReset').value='1';"  class="btn btn-sm btn-outline-secondary" />
                            <input type="hidden" id="passwordIsReset" name="passwordIsReset" value="0" />
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row g-2 mb-2" id="passwordResetElement2" style="display:none;">
                        <div class="col-sm-4 col-lg-3">
                            <label id="password1Label" for="password1" class="form-label small mb-0">New Password:</label>
                        </div>
                        <div class="col-12 col-sm">
                                <div class="d-flex align-items-center gap-1"><input type="password" id="password1" name="password1"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2" id="passwordResetElement3" style="display:none;">
                        <div class="col-sm-4 col-lg-3">
                            <label id="password2Label" for="password2" class="form-label small mb-0">Retype Password:</label>
                        </div>
                        <div class="col-12 col-sm">
                                <div class="d-flex align-items-center gap-1"><input type="password" id="password2" name="password2"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                </div>
                <input type="submit" name="submit" id="submit" value="Save"  class="btn btn-sm btn-primary" />&nbsp;
                <input type="reset"  name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($this->data['accessLevelLongDescription']); ?>'"  class="btn btn-sm btn-outline-secondary" />&nbsp;
                <input type="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($this->data['userID']); ?>');"  class="btn btn-sm btn-outline-secondary" />
            </form>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
