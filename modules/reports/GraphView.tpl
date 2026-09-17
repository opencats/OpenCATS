<!doctype html>
<html lang="en">
    <head>
        <title>CATS - Reports</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
        <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo(CATSUtility::getIndexName()); ?>?m=rss" />
        <style type="text/css" media="all">@import "<?php echo TemplateUtility::getVersionedAssetURL('main.css'); ?>";</style>
        <script type="text/javascript" src="<?php echo TemplateUtility::getVersionedAssetURL('js/lib.js'); ?>"></script>
        <style type="text/css">
        body { background: #fff; }
        .outer { max-width: 1024px; margin: 2rem auto; padding: 0 1rem; text-align: center; }
        .outer h1 { font-size: 2.25rem; font-weight: normal; }
        .graph-image { overflow: auto; }
        </style>
    </head>

    <body>
        <main class="outer">
            <h1><?php echo($_SESSION['CATS']->getSiteName()); ?></h1>

            <p>Graph refreshes every 5 minutes. Press F11 to toggle fullscreen mode in most browsers.</p>

            <p class="graph-image"><img src="<?php $this->_($this->theImage); ?>" alt="Graph" /></p>

            <p><a href="#" onclick="window.close('fs'); return false;">Close Window</a></p>

            <script type="text/javascript">
            if (document.images)
            {
                setTimeout('location.reload(true)', 1000 * 60 * 5);
            }
            else
            {
                setTimeout('location.href = location.href', 1000 * 60 * 5);
            }
            </script>

            <p id="footerText">CATS Version <?php echo(CATSUtility::getVersion()); ?> build <?php echo(CATSUtility::getBuild()); ?>. Powered by <a href="http://www.opencats.org" target="_blank"><strong>OpenCATS</strong></a>.<br />
            <span id="footerCopyright">&copy;2007-2023 OpenCATS All rights reserved.</span></p>
        </main>
    </body>
</html>
