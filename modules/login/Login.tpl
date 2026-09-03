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
        <div class="row justify-content-center align-items-center min-vh-100 py-5">
            <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

                <div class="card login-card shadow">
                    <div class="card-body p-4 p-md-5">

                        <div class="text-center mb-4">
                            <img
                                src="<?php echo TemplateUtility::getVersionedAssetURL('images/logo_and_project_name.svg'); ?>"
                                alt="OpenCATS"
                                class="login-logo"
                            >
                        </div>

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

                        <div class="login-form-panel">
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
                                        class="form-label fw-semibold"
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
                                        class="form-label fw-semibold"
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

                                <button
                                    type="submit"
                                    class="btn btn-success w-100"
                                >
                                    Login
                                </button>
                            </form>

                            <?php if (ENABLE_DEMO_MODE): ?>
                                <div class="mt-3">
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary w-100"
                                        onclick="demoLogin();"
                                    >
                                        Login to Demo Account
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="small mt-4">
                            Version <?php echo(CATSUtility::getVersion()); ?>
                        </div>

                    </div>
                </div>

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
