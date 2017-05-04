<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/html4/transitional.dtd">
<?php include_once('config.php'); ?>
<html>
    <head>
        <title><?php echo __("OpenCATS");?> - <?php echo __("Installation Wizard Script");?></title>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="js/install.js"></script>
        <style type="text/css" media="all">@import "modules/install/install.css";</style>
    </head>

    <body>
        <div id="headerBlock">
            <span id="mainLogo"><?php echo __("OpenCATS");?></span><br />
            <span id="subMainLogo"><?php echo __("Applicant Tracking System");?></span>
        </div>

        <div id="contents">
            <div id="login">
            	<p><?php echo __("Activities");?></p>
                <p><?php echo __("OpenCATS has not yet been installed, or a previous installation was not completed.");?></p>
                <p><?php echo sprintf(__("Please visit the %s to continue."),"<a href=\"installwizard.php\">".__("Installation Wizard")."</a>");?></p>
            </div>
        </div>
    </body>
</html>
