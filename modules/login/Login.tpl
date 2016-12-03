<?php /* $Id: Login.tpl 3530 2016-12-02 18:28:10Z Bloafer $ */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>opencats - Login</title>
        <link href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css" media="screen" rel="stylesheet" type="text/css" />
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
        <style type="text/css" media="all">@import "modules/login/login.css";</style>
        <script type="text/javascript" src="vendor/components/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="modules/login/validator.js"></script>
        <script type="text/javascript" src="js/submodal/subModal.js"></script>
    </head>

    <body>
    <?php TemplateUtility::printPopupContainer(); ?>
        <div id="contents" class="container">
            <div id="login" class="row">
                <div id="loginText" class="col-md-4 col-md-offset-4">

                    <?php if (ENABLE_DEMO_MODE && !($this->siteName != '' && $this->siteName != 'choose') || ($this->siteName == 'demo')): ?>
                        <?php if ($this->aspMode): ?>
                            <a href="javascript:void(0);" onclick="demoLogin(); return false;" class="btn btn-default btn-block">Login to Demo Account</a><br />
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=forgotLogin&amp;p=0" class="btn btn-default btn-block">Forgot Login Information?</a>
                        <?php else: ?>
                            <a href="javascript:void(0);" onclick="demoLogin(); return false;" class="btn btn-default btn-block">Login to Demo Account</a><br />
                        <?php endif; ?>
                    <?php elseif ($this->aspMode): ?>
                        <br /><br />
                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=forgotLogin&amp;p=0" class="btn btn-default btn-block">Forgot Login Information?</a>
                    <?php endif; ?>
                </div>

                <div id="formBlock" class="col-md-4 col-md-offset-4">
                    <img src="images/CATS-sig.gif" alt="Login" hspace="10" vspace="10" />
                    <div class="panel panel-default">
                        <?php if ($this->siteName != '' && $this->siteName != 'choose'): ?>
                            <?php if ($this->siteNameFull == 'error'): ?>
                                <div class="panel-body">
                                    <div class="alert alert-warning">This site does not exist. Please check the URL and try again.</div>
                                </div>
                            <?php else: ?>
                                <div class="panel-heading"><?php $this->_($this->siteNameFull); ?></div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="panel-body">
                        <?php if (!empty($this->message)): ?>
                        <?php if ($this->messageSuccess): ?>
                            <div class="alert alert-success"><?php $this->_($this->message); ?></div>
                        <?php else: ?>
                            <div class="alert alert-danger"><?php $this->_($this->message); ?></div>
                        <?php endif; ?>
                        <?php endif; ?>
                            <form name="loginForm" id="loginForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=login&amp;a=attemptLogin<?php if ($this->reloginVars != ''): ?>&amp;reloginVars=<?php echo($this->reloginVars); ?><?php endif; ?>" method="post" onsubmit="return checkLoginForm(document.loginForm);" autocomplete="off">
                                <div id="subFormBlock">
                                    <?php if ($this->aspMode): ?>
                                        <?php if ($this->siteName == 'choose' || ($this->aspMode && $this->siteName == '')): ?>
                                            <div class="form-group">
                                                <label id="siteNameLabel" for="siteName">Company Identifier</label><br />
                                                <input name="siteName" id="siteName" class="form-control" />
                                            </div>
                                        <?php elseif($this->siteName != ''): ?>
                                            <input type="hidden" name="siteName" value="<?php $this->_($this->siteName); ?>">
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($this->siteNameFull != 'error'): ?>
                                        <div class="form-group">
                                            <label id="usernameLabel" for="username">Username</label><br />
                                            <input name="username" id="username" class="form-control" value="<?php if (isset($this->username)) $this->_($this->username); ?>" placeholder="Username" />
                                        </div>

                                        <div class="form-group">
                                            <label id="passwordLabel" for="password">Password</label><br />
                                            <input type="password" name="password" id="password" class="form-control" placeholder="Password" />
                                        </div>

                                        <input type="submit" class="btn btn-success pull-right" value="Login" />
                                        <input type="reset"  id="reset" name="reset"  class="btn btn-default" value="Reset" />
                                    <?php else: ?>
                                        <?php if ($this->aspMode): ?>
                                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=createsite&amp;p=0">Create Free Trial Site</a><br />
                                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=asp&amp;a=forgotLogin&amp;p=0">Forgot Login Information</a>
                                        <?php else: ?>
                                            <a href="javascript:void(0);" onclick="demoLogin(); return false;" class="btn btn-success btn-block">Login to Demo Account</a><br />
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <div class="panel-footer text-right"><small class="text-muted">Version <?php echo(CATSUtility::getVersion()); ?></small></div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                <?php if ($this->siteNameFull != 'error'): ?>
                    document.loginForm.username.focus();

                    function demoLogin()
                    {
                        document.getElementById('username').value = '<?php echo(DEMO_LOGIN); ?>';
                        document.getElementById('password').value = '<?php echo(DEMO_PASSWORD); ?>';
                        document.getElementById('loginForm').submit();
                    }
                    function defaultLogin()
                    {
                        document.getElementById('username').value = 'admin';
                        document.getElementById('password').value = 'cats';
                        document.getElementById('loginForm').submit();
                    }
                <?php endif; ?>
                <?php if (isset($_GET['defaultlogin'])): ?>
                    defaultLogin();
                <?php endif; ?>
            </script>
        </div>
    <footer class="footer">
      <div class="container">
        <div class="row">
            <div class="col-md-4">
                <a href="http://forums.opencats.org ">opencats support forum</a>
            </div>
            <div class="col-md-8 text-right">
                <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span> Based upon original work and Powered by <a href="http://www.catsone.com ">CATS</a>.
            </div>
        </div>
      </div>
    </footer>



        <script type="text/javascript">
            initPopUp();
        </script>
        <?php TemplateUtility::printCookieTester(); ?>
    </body>
</html>
