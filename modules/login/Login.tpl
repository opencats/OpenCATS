<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php echo(HTML_ENCODING); ?>">
    <title>OpenCATS - Login</title>

    <style type="text/css" media="all">
        @import "<?php echo TemplateUtility::getVersionedAssetURL('modules/login/login.css'); ?>";
    </style>

    <script src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
    <script src="<?php echo TemplateUtility::getVersionedAssetURL('modules/login/validator.js'); ?>"></script>
    <script src="<?php echo TemplateUtility::getVersionedAssetURL('js/submodal/subModal.js'); ?>"></script>
</head>

<body>
    <!-- CATS_LOGIN -->
    <?php TemplateUtility::printPopupContainer(); ?>

    <p>&nbsp;</p>
    <p>&nbsp;</p>
    <p>&nbsp;</p>

    <div id="contents">
        <div id="login">
            <div id="loginText">
                <div class="ctr"></div>
                <br>

                <?php if (ENABLE_DEMO_MODE): ?>
                    <br><br>
                    <a href="javascript:void(0);" onclick="demoLogin(); return false;">
                        Login to Demo Account
                    </a>
                    <br>
                <?php endif; ?>
            </div>

            <div id="formBlock">
                <img src="images/CATS-sig.gif" alt="Login" hspace="10" vspace="10">
                <br>

                <form
                    name="loginForm"
                    id="loginForm"
                    action="<?php echo(CATSUtility::getIndexName()); ?>?m=login&amp;a=attemptLogin<?php if ($this->reloginVars != ''): ?>&amp;reloginVars=<?php echo($this->reloginVars); ?><?php endif; ?>"
                    method="post"
                    onsubmit="return checkLoginForm(document.loginForm);"
                    autocomplete="off"
                >
                    <div id="subFormBlock">
                        <label id="usernameLabel" for="username">Username</label>
                        <br>

                        <input
                            name="username"
                            id="username"
                            class="login-input-box"
                            value="<?php if (isset($this->username)) $this->_($this->username); ?>"
                        >
                        <br>

                        <label id="passwordLabel" for="password">Password</label>
                        <br>

                        <input
                            type="password"
                            name="password"
                            id="password"
                            class="login-input-box"
                        >
                        <br>

                        <input type="submit" class="button" value="Login">
                        <input type="reset" id="reset" name="reset" class="button" value="Reset">

                        <br><br>
                    </div>
                </form>

                <span style="line-height: 30px; font-size: 10px; padding-left: 10px;">
                    Version <?php echo(CATSUtility::getVersion()); ?>
                </span>
            </div>

            <div style="clear: both;"></div>
        </div>

        <br>

        <script>
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

            <?php if (isset($_GET['defaultlogin'])): ?>
                defaultLogin();
            <?php endif; ?>
        </script>

        <p>&nbsp;</p>
        <p>&nbsp;</p>

        <span style="font-size: 12px;">
            <a href="http://forums.opencats.org">OpenCATS Support Forum</a>
        </span>

        <div id="login">
            <?php if (!empty($this->message)): ?>
                <div>
                    <?php if ($this->messageSuccess): ?>
                        <p class="success">
                            <?php $this->_($this->message); ?><br>
                        </p>
                    <?php else: ?>
                        <p class="failure">
                            <?php $this->_($this->message); ?><br>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <div id="footerBlock">
            <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span>
            Based upon original work and Powered by
            <a href="http://www.opencats.org" target="_blank">OpenCATS</a>.
        </div>
    </div>

    <script>
        initPopUp();
    </script>

    <?php TemplateUtility::printCookieTester(); ?>
</body>
</html>
