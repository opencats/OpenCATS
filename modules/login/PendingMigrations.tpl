<?php /* Pending install schema migration notice. */ ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OpenCATS - Maintenance Required</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
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
                <div>
                    <h1 class="h5">Maintenance Required</h1>

                    <?php if ($this->isAdministrator): ?>
                        <p>Database migrations are pending. OpenCATS should be updated before normal use continues.</p>
                        <p>Open the <a href="installwizard.php">Installation Wizard</a> to complete the upgrade.</p>
                    <?php else: ?>
                        <p>Database maintenance is required before OpenCATS can be used normally. Please contact your administrator.</p>
                    <?php endif; ?>
                </div>
            </div>

            <div id="footerBlock" class="text-center small text-body-secondary mt-3">
                <span class="footerCopyright"><?php echo(COPYRIGHT_HTML); ?></span>
            </div>
        </main>
    </body>
</html>
