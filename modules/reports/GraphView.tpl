<?php /* $Id: GraphView.tpl 3430 2007-11-06 20:44:51Z will $ */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
    <head>
        <title>CATS - Reports</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="icon" href="images/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" />
        <link rel="alternate" type="application/rss+xml" title="RSS" href="<?php echo(CATSUtility::getIndexName()); ?>?m=rss&amp;siteID=<?php echo($_SESSION['CATS']->getSiteID()); ?>" />
        <style type="text/css" media="all">@import "main.css";</style>
        <script type="text/javascript" src="js/lib.js"></script>
        <style type="text/css">
        div.outer
        {
            position: absolute;
            left: 50%;
            top: 50%;
            width: 1024px;
            height: 768px;
            margin-left: -512px; /* half of width */
            margin-top: -384px;  /* half of height */
        }
        </style>
    </head>

    <body style="background: #fff;">
        <div class="outer">
            <p align="center" style="font-size:36px;"><?php echo($_SESSION['CATS']->getSiteName()); ?></p>

            <p align="center">Graph refreshes every 5 minutes. Press F11 to toggle fullscreen mode in most browsers.</p>

            <p align="center"><img src="<?php $this->_($this->theImage); ?>" alt="Graph" /></p>

            <p align="center"><a href="#" onclick="window.close('fs'); return false;">Close Window</a></p>

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

            <p id="footerText" align="center">CATS Version <?php echo(CATSUtility::getVersion()); ?> build <?php echo(CATSUtility::getBuild()); ?>. Powered by <a href="http://www.opencats.org" target="_blank"><strong>OpenCATS</strong></a>.<br />
            <span id="footerCopyright">&copy;2007-2020 OpenCATS All rights reserved.</span></p>
        </div>
    </body>
</html>
