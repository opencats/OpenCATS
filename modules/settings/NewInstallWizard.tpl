<!DOCTYPE html>
<html lang="en">
    <head>
        <title>CATS - Initial Configuration Wizard</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>">
        <link rel="stylesheet" href="<?php echo TemplateUtility::getVersionedAssetURL('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'); ?>">
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('modules/settings/validator.js'); ?>"></script>
    </head>

    <body class="bg-body-tertiary">

    <div id="headerBlock" class="container text-center py-3">
        <span id="mainLogo" class="h3">OpenCATS</span><br />
        <span id="subMainLogo">Applicant Tracking System</span>
    </div>

    <div id="contents" class="container pb-4">
        <div id="login" class="card card-body mx-auto col-12 col-md-8 col-lg-6">
            <?php if (!empty($this->message)): ?>
                <div>
                    <?php if ($this->messageSuccess): ?>
                        <p class="alert alert-success"><?php $this->_($this->message); ?><br /></p>
                    <?php else: ?>
                        <p class="alert alert-danger"><?php $this->_($this->message); ?><br /></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="text-align: left;">
                <span class="fw-semibold"><?php echo ($this->title); ?></span>

                <p><?php echo ($this->prompt); ?></p>
            </div>

            <?php if ($this->inputType == 'password'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />
                        <?php if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn()): ?>
                            <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>" />
                        <?php endif; ?>

                        <label id="passwordLabel1" for="password1" class="form-label small mb-0">New Password</label><br />
                        <input type="password" name="password1" id="password1"  class="form-control form-control-sm" />
                        <br />

                        <label id="passwordLabel2" for="password2" class="form-label small mb-0">Confirm New Password</label><br />
                        <input type="password" name="password2" id="password2"  class="form-control form-control-sm" />
                        <br />

                        <input type="submit" id="submit" name="submit" value="Submit"  class="btn btn-sm btn-primary" />
                        <input type="reset"  id="reset" name="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />
                    </form>
                </div>
                <script type="text/javascript">
                    document.configurationForm.password1.focus();
                </script>
            <?php endif; ?>

            <?php if ($this->inputType == 'localization'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />
                        <?php if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn()): ?>
                            <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>" />
                        <?php endif; ?>

                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">Please choose your time zone.</div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm"><?php TemplateUtility::printTimeZoneSelect('timeZone', '', 'form-select form-select-sm', OFFSET_GMT); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">Please choose your preferred date format.</div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">
                                    <select id="dateFormat" name="dateFormat" class="form-select form-select-sm">
                                        <option value="mdy" selected="selected">MM-DD-YYYY (US)</option>
                                        <option value="dmy">DD-MM-YYYY (UK)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">Please choose your preferred time format.</div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">
                                    <select id="timeFormat" name="timeFormat" class="form-select form-select-sm">
                                        <option value="12" selected="selected">12-hour (1:30 PM)</option>
                                        <option value="24">24-hour (13:30)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <br />

                        <input type="submit" id="submit" name="submit" value="Submit"  class="btn btn-sm btn-primary" />
                        <input type="reset"  id="reset" name="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />
                    </form>
                </div>
            <?php endif; ?>

            <div style="clear: both;"></div>

            <?php if ($this->inputType == 'siteName'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />
                        <?php if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn()): ?>
                            <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>" />
                        <?php endif; ?>

                        <label id="siteNameLabel" for="siteName" class="form-label small mb-0"><?php echo($this->inputTypeTextParam); ?></label><br />
                        <input type="text" name="siteName" id="siteName"  class="form-control form-control-sm" />
                        <br />

                        <input type="submit" id="submit" name="submit" value="Submit"  class="btn btn-sm btn-primary" />
                        <input type="reset"  id="reset" name="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />
                    </form>
                </div>
                <script type="text/javascript">
                    document.configurationForm.siteName.focus();
                </script>
           <?php endif; ?>

           <?php if ($this->inputType == 'text'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />
                        <?php if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn()): ?>
                            <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>" />
                        <?php endif; ?>

                        <label id="text1Label" for="text1" class="form-label small mb-0"><?php echo($this->inputTypeTextParam); ?></label><br />
                        <input name="text1" id="text1"  class="form-control form-control-sm" />
                        <br />

                        <input type="submit" id="submit" name="submit" value="Submit"  class="btn btn-sm btn-primary" />
                        <input type="reset"  id="reset" name="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />
                        <br /><br />
                    </form>
                </div>
           <?php endif; ?>

           <?php if ($this->inputType == 'conclusion'): ?>
                <div id="subFormBlock" style="text-align: center;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php echo($this->home); ?>" method="post" autocomplete="off">
                        <?php if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn()): ?>
                            <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>" />
                        <?php endif; ?>

                        <input type="submit" id="submit" name="submit" value="Continue Using OpenCATS"  class="btn btn-sm btn-primary" />
                    </form>
                </div>
           <?php endif; ?>
        </div>

        <div style="clear: both;"></div>
        <br />

        <div id="footerBlock" class="text-center small text-body-secondary">
            <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span>
        </div>
    </div>
    </body>
</html>
