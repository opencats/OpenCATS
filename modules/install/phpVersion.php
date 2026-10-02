<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CATS - Installation Wizard Script</title>
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
        <p class="alert alert-danger" role="alert">
        Your PHP version is
        <?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?>.
        OpenCATS requires PHP
        <?php echo htmlspecialchars($minimumPHPVersion, ENT_QUOTES, 'UTF-8'); ?>
        or newer.
        </p>
        <p>Please install a supported PHP version and try again.</p>
            </div>
        </main>
    </body>
</html>
