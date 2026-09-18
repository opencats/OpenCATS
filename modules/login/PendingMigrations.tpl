<?php /* Pending install schema migration notice. */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>OpenCATS - Maintenance Required</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
        <style type="text/css" media="all">@import "<?php echo TemplateUtility::getVersionedAssetURL('modules/install/install.css'); ?>";</style>
    </head>

    <body>
        <div id="headerBlock">
            <span id="mainLogo">OpenCATS</span><br />
            <span id="subMainLogo">Applicant Tracking System</span>
        </div>

        <div id="contents">
            <div id="login" style="width: 500px;">
                <div style="text-align: left;">
                    <span style="font-weight: bold;">Maintenance Required</span>

                    <?php if ($this->isAdministrator): ?>
                        <p>Database migrations are pending. OpenCATS should be updated before normal use continues.</p>
                        <p><a href="index.php?m=install&amp;a=maint">Start Maintenance</a></p>
                    <?php else: ?>
                        <p>Database maintenance is required before OpenCATS can be used normally. Please contact your administrator.</p>
                    <?php endif; ?>
                </div>

                <div style="clear: both;"></div>
            </div>

            <div style="clear: both;"></div>
            <br />

            <div id="footerBlock">
                <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span>
            </div>
        </div>
    </body>
</html>
