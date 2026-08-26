<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/html4/transitional.dtd">
<?php include_once('config.php'); ?>
<?php include_once('constants.php'); ?>
<?php include_once(LEGACY_ROOT . '/lib/TemplateUtility.php'); ?>
<html>
    <head>
        <title>CATS - Installation Wizard Script</title>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/install.js'); ?>"></script>
        <style type="text/css" media="all">@import "<?php echo TemplateUtility::getVersionedAssetURL('modules/install/install.css'); ?>";</style>
    </head>

    <body>
        <div id="headerBlock">
            <span id="mainLogo">OpenCATS</span><br />
            <span id="subMainLogo">Applicant Tracking System</span>
        </div>

        <div id="contents">
        <div id="login">
        <p>
        Your PHP version is
        <?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?>.
        OpenCATS requires PHP
        <?php echo htmlspecialchars($minimumPHPVersion, ENT_QUOTES, 'UTF-8'); ?>
        or newer.
        </p>
        <p>Please install a supported PHP version and try again.</p>
            </div>
        </div>
    </body>
</html>
