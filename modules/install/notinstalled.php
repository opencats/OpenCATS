<!DOCTYPE html>
<?php include_once('config.php'); ?>
<?php include_once('constants.php'); ?>
<?php include_once(LEGACY_ROOT . '/lib/TemplateUtility.php'); ?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OpenCATS - Installation Wizard Script</title>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/install.js'); ?>"></script>
        <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="modules/install/install.css">
    </head>

    <body class="installer bg-body-tertiary">
        <div id="headerBlock" class="container text-center py-4">
            <span id="mainLogo" class="h2 fw-bold text-primary">OpenCATS</span><br />
            <span id="subMainLogo" class="text-body-secondary">Applicant Tracking System</span>
        </div>

        <main id="contents" class="container pb-4">
            <div id="login" class="card card-body mx-auto col-12 col-md-8 col-lg-6">
                <p>OpenCATS has not yet been installed, or a previous installation was not completed.</p>
                <p>Please visit the <a href="installwizard.php">Installation Wizard</a> to continue.</p>
            </div>
        </main>
    </body>
</html>
