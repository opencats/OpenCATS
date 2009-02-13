<?php /* $Id: Login.tpl 3530 2007-11-09 18:28:10Z brian $ */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>** Authorized Logins Only ** OSATS</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
        <style type="text/css" media="all">@import "modules/login/login.css";</style>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="modules/login/validator.js"></script>
        <script type="text/javascript" src="js/submodal/subModal.js"></script>
    </head>

    <body>
    <!-- The Login Page -->
    <?php TemplateUtility::printPopupContainer(); ?>
        <div id="headerBlock">
            <br />
            <span id="subMainLogo"></span>
        </div>

        <div id="contents">
            <div id="login">
                <?php if (!empty($this->message)): ?>
                    <div>
                        <?php if ($this->messageSuccess): ?>
                            <p class="success"><?php $this->_($this->message); ?><br /></p>
                        <?php else: ?>
                            <p class="failure"><?php $this->_($this->message); ?><br /></p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div id="loginText">
                    <div class="ctr">
						<img src="images/login2.jpg" alt="Login Now" />
                        <center><img src="images/folder1_locked.jpg" width="64" height="64" alt="You Must Login with a Valid Username and Password!" /></center>
                    </div>
                    <br />
                    <form name="loginForm" id="loginForm" action="<?php echo(osatutil::getIndexName()); ?>?m=login&amp;a=attemptLogin<?php if ($this->reloginVars != ''): ?>&amp;reloginVars=<?php echo($this->reloginVars); ?><?php endif; ?>" method="post" onsubmit="return checkLoginForm(document.loginForm);" autocomplete="off">
                        <div id="subFormBlock">
                            <?php if ($this->siteName != '' && $this->siteName != 'choose'): ?>
                                <?php if ($this->siteNameFull == 'error'): ?>
                                    <label>OOPS! This site does not exist. Please check the URL and try again.</label>
                                    <br />
                                    <br />
                                <?php else: ?>
                                    <label><?php $this->_($this->siteNameFull); ?></label>
                                    <br />
                                    <br />
                                <?php endif; ?>
                            <?php endif; ?>

							<?php if ($this->siteNameFull != 'error'): ?>
                                <label id="usernameLabel" for="username">Login Name:</label><br />
                                <input name="username" id="username" class="login-input-box" value="<?php if (isset($this->username)) $this->_($this->username); ?>" />
                                <br />

                                <label id="passwordLabel" for="password">Password:</label><br />
                                <input type="password" name="password" id="password" class="login-input-box" />
                                <br />

                                <input type="submit" class="button" value="Login" />
                                <!-- Do we really need a Clear button? Its wasted code... -Jamin
								<input type="reset"  id="reset" name="reset"  class="button" value="Clear" />
								-->

							<!-- I want to make this work - Jamin. Remmed out for now
							<a href="<?php echo(osatutil::getIndexName()); ?>?m=asp&amp;a=forgotLogin&amp;p=0">Forgot Login Information</a>
							-->
                            <?php endif; ?>
                            <br /><br />
                        </div>
                    </form>
                    <span style="font-size: 10px;"></span>


                </div>


                <div style="clear: both;"></div>
            </div>
            <br />

            <script type="text/javascript">
                <?php if ($this->siteNameFull != 'error'): ?>
                    document.loginForm.username.focus();


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

            <div id="footerBlock">
                <?php
                    /* This is where you can put your own footer info on the main login page.
                   */
                ?>
                <span class="footerCopyright">
				<font color="#FFFFFF">Put your own message here by modifying the modules/login.tpl file and look for this message and change it!</font>

				</span>

            </div>
        </div>
        <script type="text/javascript">
            initPopUp();
        </script>
        <?php TemplateUtility::printCookieTester(); ?>
    </body>
</html>