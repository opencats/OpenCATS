<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?php echo(HTML_ENCODING); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>OpenCATS - Login</title>

    <link
        rel="stylesheet"
        href="<?php echo TemplateUtility::getVersionedAssetURL('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'); ?>"
    >
    <link
        rel="stylesheet"
        href="<?php echo TemplateUtility::getVersionedAssetURL('modules/login/login.css'); ?>"
    >

    <script src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
    <script src="<?php echo TemplateUtility::getVersionedAssetURL('modules/login/validator.js'); ?>"></script>
    <script src="<?php echo TemplateUtility::getVersionedAssetURL('js/submodal/subModal.js'); ?>"></script>
</head>

<body class="bg-body-tertiary">
    <!-- CATS_LOGIN -->
    <?php TemplateUtility::printPopupContainer(); ?>

    <main class="container">
        <div class="row justify-content-center min-vh-100 align-items-center py-5">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="text-center mb-4">
                    <img
                        src="images/CATS-sig.gif"
                        alt="OpenCATS"
                        class="img-fluid"
                    >
                </div>

                <div class="card shadow-sm">
                    <div class="card-header bg-body">
                        <h1 class="h4 mb-0">Sign in</h1>
                    </div>

                    <div class="card-body">

                        <?php if (!empty($this->message)): ?>
                            <?php if ($this->messageSuccess): ?>
                                <div class="alert alert-success" role="alert">
                                    <?php $this->_($this->message); ?>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-danger" role="alert">
                                    <?php $this->_($this->message); ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <form
                            name="loginForm"
                            id="loginForm"
                            action="<?php echo(CATSUtility::getIndexName()); ?>?m=login&amp;a=attemptLogin<?php if ($this->reloginVars != ''): ?>&amp;reloginVars=<?php echo($this->reloginVars); ?><?php endif; ?>"
                            method="post"
                            onsubmit="return checkLoginForm(document.loginForm);"
                            autocomplete="off"
                        >
                            <div class="mb-3">
                                <label
                                    id="usernameLabel"
                                    for="username"
                                    class="form-label"
                                >
                                    Username
                                </label>

                                <input
                                    name="username"
                                    id="username"
                                    class="form-control"
                                    value="<?php if (isset($this->username)) $this->_($this->username); ?>"
                                    autocomplete="username"
                                >
                            </div>

                            <div class="mb-3">
                                <label
                                    id="passwordLabel"
                                    for="password"
                                    class="form-label"
                                >
                                    Password
                                </label>

                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control"
                                    autocomplete="current-password"
                                >
                            </div>

                            <div class="d-flex gap-2">
                                <input
                                    type="submit"
                                    class="btn btn-primary"
                                    value="Login"
                                >

                                <input
                                    type="reset"
                                    id="reset"
                                    name="reset"
                                    class="btn btn-outline-secondary"
                                    value="Reset"
                                >
                            </div>
                        </form>

                        <?php if (ENABLE_DEMO_MODE): ?>
                            <hr>

                            <a
                                href="javascript:void(0);"
                                onclick="demoLogin(); return false;"
                                class="btn btn-outline-primary w-100"
                            >
                                Login to Demo Account
                            </a>
                        <?php endif; ?>

                    </div>

                    <div class="card-footer text-center text-body-secondary small">
                        Version <?php echo(CATSUtility::getVersion()); ?>
                    </div>
                </div>

                <div class="text-center mt-4 small">
                    <a href="http://forums.opencats.org">
                        OpenCATS Support Forum
                    </a>
                </div>

                <footer class="text-center text-body-secondary small mt-3">
                    <div>
                        <span class="footerCopyright">
                            <?php echo(COPYRIGHT_HTML); ?>
                        </span>
                    </div>

                    <div>
                        Based upon original work and Powered by
                        <a
                            href="http://www.opencats.org"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            OpenCATS
                        </a>.
                    </div>
                </footer>

            </div>
        </div>
    </main>

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

        initPopUp();
    </script>

    <?php TemplateUtility::printCookieTester(); ?>
</body>
</html>
