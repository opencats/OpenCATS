<?php /* $Id: NewInstallWizard.tpl 2035 2007-02-28 17:32:27Z will $ */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>CATS - Initial Configuration Wizard</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>">
        <style type="text/css" media="all">@import "modules/install/install.css";</style>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="modules/settings/validator.js"></script>
    </head>

    <body>

    <div id="headerBlock">
        <span id="mainLogo">OpenCATS</span><br />
        <span id="subMainLogo">Applicant Tracking System</span>
    </div>

    <div id="contents">
        <div id="login" style="width: 500px;">
            <?php if (!empty($this->message)): ?>
                <div>
                    <?php if ($this->messageSuccess): ?>
                        <p class="success"><?php $this->_($this->message); ?><br /></p>
                    <?php else: ?>
                        <p class="failure"><?php $this->_($this->message); ?><br /></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div style="text-align: left;">
                <span style="font-weight: bold;"><?php echo ($this->title); ?></span>

                <p><?php echo ($this->prompt); ?></p>
            </div>

            <?php if ($this->inputType == 'password'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />
                        <label id="passwordLabel1" for="password1">New Password</label><br />
                        <input type="password" name="password1" id="password1" class="input-box" />
                        <br />

                        <label id="passwordLabel2" for="password2">Confirm New Password</label><br />
                        <input type="password" name="password2" id="password2" class="input-box" />
                        <br />

                        <input type="submit" id="submit" name="submit" class="button" value="Submit" />
                        <input type="reset"  id="reset" name="reset"  class="button" value="Reset" />
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

                        <table>
                            <tr>
                                <td>Please choose your time zone.</td>
                            </tr>
                            <tr>
                                <td style="padding-bottom: 10px;"><?php TemplateUtility::printTimeZoneSelect('timeZone', 'width: 420px;', '', OFFSET_GMT); ?></td>
                            </tr>

                            <tr>
                                <td>Please choose your preferred date format.</td>
                            </tr>
                            <tr>
                                <td>
                                    <select id="dateFormat" name="dateFormat" style="width: 150px;">
                                        <option value="mdy" selected="selected">MM-DD-YYYY (US)</option>
                                        <option value="dmy">DD-MM-YYYY (UK)</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <br />

                        <input type="submit" id="submit" name="submit" class="button" value="Submit" />
                        <input type="reset"  id="reset" name="reset"  class="button" value="Reset" />
                    </form>
                </div>
            <?php endif; ?>

            <div style="clear: both;"></div>

            <?php if ($this->inputType == 'siteName'): ?>
                <div id="subFormBlock" style="text-align: left;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo($this->action); ?>" method="post" autocomplete="off">
                        <input type="hidden" name="postback" value="postback" />

                        <label id="siteNameLabel" for="siteName"><?php echo($this->inputTypeTextParam); ?></label><br />
                        <input type="text" name="siteName" id="siteName" class="input-box" style="width: 200px;" />
                        <br />

                        <input type="submit" id="submit" name="submit" class="button" value="Submit" />
                        <input type="reset"  id="reset" name="reset"  class="button" value="Reset" />
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
                        <label id="text1Label" for="text1"><?php echo($this->inputTypeTextParam); ?></label><br />
                        <input name="text1" id="text1" class="input-box" />
                        <br />

                        <input type="submit" id="submit" name="submit" class="button" value="Submit" />
                        <input type="reset"  id="reset" name="reset"  class="button" value="Reset" />
                        <br /><br />
                    </form>
                </div>
           <?php endif; ?>

           <?php if ($this->inputType == 'conclusion'): ?>
                <div id="subFormBlock" style="text-align: center;">
                    <form name="configurationForm" id="configurationForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php echo($this->home); ?>" method="post" autocomplete="off">
                        <input type="submit" id="submit" name="submit" class="button" value="Continue Using OpenCATS" />
                    </form>
                </div>
           <?php endif; ?>
        </div>

        <div style="clear: both;"></div>
        <br />

        <div id="footerBlock">
            <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span>
        </div>
    </div>
    </body>
</html>
