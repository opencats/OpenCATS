<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Add Site User</h1>
            </header>

            <p class="d-flex flex-wrap justify-content-between gap-2 bg-secondary-subtle rounded p-2 mb-2 fw-semibold">
                <span >Add Site User</span>
                <span class="ms-sm-auto"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
            </p>

            <form name="addUserForm" id="addUserForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=addUser" method="post" onsubmit="return checkAddUserForm(document.addUserForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <div class="mb-2">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm">
                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="firstNameLabel" for="firstName" class="form-label small mb-0">First Name:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <div class="d-flex align-items-center gap-1"><input type="text" id="firstName" name="firstName"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="lastNameLabel" for="lastName" class="form-label small mb-0">Last Name:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <div class="d-flex align-items-center gap-1"><input type="text" id="lastName" name="lastName"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="emailLabel" for="email" class="form-label small mb-0">E-Mail:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="text" id="email" name="email"  class="form-control form-control-sm" />
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="usernameLabel" for="username" class="form-label small mb-0">Username:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <div class="d-flex align-items-center gap-1"><input type="text" id="username" name="username"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="passwordLabel" for="password" class="form-label small mb-0">Password:</label>
                                    </div>
                                    <div class="col-12 col-sm">
					<?php if ($this->auth_mode == "ldap"): ?>
					LDAP Authentication is enabled, hence password not required.
                            		<input type="hidden" class="inputbox" id="password" name="password" value="password" />
                            		<?php else: ?>
                                        <div class="d-flex align-items-center gap-1"><input type="password" id="password" name="password"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
					<?php endif; ?>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="retypePasswordLabel" for="retypePassword" class="form-label small mb-0">Retype Password:</label>
                                    </div>
                                    <div class="col-12 col-sm">
					<?php if ($this->auth_mode == "ldap"): ?>
                            		<input type="hidden" class="inputbox" id="retypePassword" name="retypePassword" value="password"/>
                             		<?php else: ?>
                                        <div class="d-flex align-items-center gap-1"><input type="password" id="retypePassword" name="retypePassword"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
					<?php endif; ?>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label id="accessLevelLabel" for="accessLevel" class="form-label small mb-0">Access Level:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <span id="accessLevelsSpan">
                                            <?php foreach ($this->accessLevels as $accessLevel): ?>
                                                <?php if ($accessLevel['accessID'] > $this->getUserAccessLevel('settings.addUser')): continue; endif; ?>
                                                <?php if (!$this->license['canAdd'] && !$this->license['unlimited'] && $accessLevel['accessID'] > ACCESS_LEVEL_READ): continue; endif; ?>

                                                <?php $radioButtonID = 'access' . $accessLevel['accessID']; ?>

                                                <input type="radio" name="accessLevel" id="<?php echo($radioButtonID); ?>" value="<?php $this->_($accessLevel['accessID']); ?>" title="<?php $this->_($accessLevel['longDescription']); ?>" <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>checked<?php endif; ?> onclick="document.getElementById('userAccessStatus').innerHTML='<?php $this->_($accessLevel['longDescription']); ?>'; <?php if($accessLevel['accessID'] >= ACCESS_LEVEL_SA): ?>document.getElementById('eeoIsVisible').checked=true; document.getElementById('eeoIsVisible').disabled=true;  document.getElementById('eeoVisibleSpan').style.display='none';<?php else: ?>document.getElementById('eeoIsVisible').disabled=false;<?php endif; ?>"  class="form-check-input" />
                                                <label for="<?php echo($radioButtonID); ?>" title="<?php $this->_(str_replace('\'', '\\\'', $accessLevel['longDescription'])); ?>" class="form-label small mb-0">
                                                    <?php $this->_($accessLevel['shortDescription']); ?>
                                                    <?php if ($accessLevel['accessID'] == $this->defaultAccessLevel): ?>(Default)<?php endif; ?>
                                                </label>
                                                <br />
                                            <?php endforeach; ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">Access Description:</div>
                                    <div class="col-12 col-sm">
                                        <span id="userAccessStatus">Delete - All lower access, plus the ability to delete information on the system.</span>
                                    </div>
                                </div>

                                <?php if (count($this->categories) > 0): ?>
                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4 col-lg-3">
                                            <label id="accessLevelLabel" for="accessLevel" class="form-label small mb-0">Role:</label>
                                        </div>
                                        <div class="col-12 col-sm">
                                           <input type="radio" name="role" value="none" title="" checked onclick="document.getElementById('userRoleDesc').innerHTML='This user is a normal user.';  document.getElementById('accessLevelsSpan').style.display='';"  class="form-check-input" /> Normal User
                                           <br />
                                           <?php foreach ($this->categories as $category): ?>
                                               <?php if (isset($category[4])): ?>
                                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>" onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo(str_replace('\'', '\\\'', $category[2])); ?>'; document.getElementById('access<?php echo($category[4]); ?>').checked=true; document.getElementById('accessLevelsSpan').style.display='none';"  class="form-check-input" /> <?php $this->_($category[0]); ?>
                                               <?php else: ?>
                                                   <input type="radio" name="role" value="<?php $this->_($category[1]); ?>" onclick="document.getElementById('userRoleDesc').innerHTML='<?php echo(str_replace('\'', '\\\'', $category[2])); ?>'; document.getElementById('accessLevelsSpan').style.display='';"  class="form-check-input" /> <?php $this->_($category[0]); ?>
                                               <?php endif; ?>
                                               <br />
                                           <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4 col-lg-3">Role Description:</div>
                                        <div class="col-12 col-sm">
                                            <span id="userRoleDesc" style="font-size: smaller">This user is a normal user.</span>
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
                                                <input type="checkbox" name="eeoIsVisible" id="eeoIsVisible" onclick="if (this.checked) document.getElementById('eeoVisibleSpan').style.display='none'; else document.getElementById('eeoVisibleSpan').style.display='';" class="form-check-input">
                                                &nbsp;This user is <span id="eeoVisibleSpan">not </span>allowed to edit and view candidate's EEO information.
                                            </span>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <?php if (!$this->license['canAdd'] && !$this->license['unlimited']): ?>
                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4 col-lg-3">Notice:</div>
                                        <div class="col-12 col-sm">
                                            <b>You are currently using your full allotment of active user accounts. Disable an existing account or upgrade your license to add another active user.</b>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php
                        eval(Hooks::get('SETTINGS_USERS_FULLQUOTALICENSES'));
                        ?>
                    </div>
                </div>

                <input type="submit" name="submit" id="submit" value="Add User"  class="btn btn-sm btn-primary" />&nbsp;
                <input type="reset"  name="reset"  id="reset"  value="Reset" onclick="document.getElementById('userAccessStatus').innerHTML='Delete - All lower access, plus the ability to delete information on the system.'"  class="btn btn-sm btn-outline-secondary" />&nbsp;
                <input type="button" name="back"   id="back"   value="Cancel" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers');"  class="btn btn-sm btn-outline-secondary" />
            </form>
        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
