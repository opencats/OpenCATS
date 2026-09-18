<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: My Profile</h1>
            </header>

	    <?php if ($this->auth_mode == "ldap"): ?>
		<p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">LDAP Enabled. Password cannot be changed from OpenCATS</p>
	    <?php endif; ?>
            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Change Password</p>

            <form name="changePasswordForm" id="changePasswordForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=changePassword" method="post" onsubmit="return checkChangePasswordForm(document.changePasswordForm);">
                <input type="hidden" name="postback" id="postback" value="postback" />


                <?php if ($this->isDemoUser): ?>
                    Note that as a demo user, you do not have privileges to modify any settings.
                    <br /><br />
                <?php endif; ?>

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <span class="bold">Change Password</span>
                            <br />
                            <br />
                            <span id='passwordErrorMessage' class="small text-danger">
                                <?php if (isset($this->errorMessage)): ?>
                                        <?php $this->_($this->errorMessage); ?>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>


                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm">
                            <label id="currentPasswordLabel" for="currentPassword" class="form-label small mb-0">Current Password:</label>&nbsp;
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="password" id="currentPassword" name="currentPassword"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm">
                            <label id="newPasswordLabel" for="newPassword" class="form-label small mb-0">New Password:</label>&nbsp;
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="password" id="newPassword" name="newPassword"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm">
                            <label id="retypeNewPasswordLabel" for="retypeNewPassword" class="form-label small mb-0">Retype New Password:</label>&nbsp;
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="password" id="retypeNewPassword" name="retypeNewPassword"  class="form-control form-control-sm" /><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <br />
                            <input type="submit" id="changePassword" name="changePassword" value="Change Password"  class="btn btn-sm btn-primary" />
                            <input type="reset"  id="reset"          name="reset"          value="Reset"  class="btn btn-sm btn-outline-secondary" />
                            <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';"  class="btn btn-sm btn-outline-secondary" />
                       </div>
                    </div>
                </div>
            </form>

            <script type="text/javascript">
                document.changePasswordForm.currentPassword.focus();
            </script>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
