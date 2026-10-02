<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
global $careerPage;
$assetPrefix = !empty($careerPage) ? '../' : '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="<?php echo HTML_ENCODING; ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php $this->_($this->siteName); ?> - Careers</title>
    <link rel="stylesheet" href="<?php echo $assetPrefix . TemplateUtility::getVersionedAssetURL('vendor/twbs/bootstrap/dist/css/bootstrap.min.css'); ?>">
    <?php foreach (array('lib', 'sorttable', 'calendarDateInput', 'careerPortalApply') as $script): ?>
    <script src="<?php echo $assetPrefix . TemplateUtility::getVersionedAssetURL('js/' . $script . '.js'); ?>"></script>
    <?php endforeach; ?>
    <style><?php echo $this->template['CSS']; ?></style>
</head>
<body class="bg-body-tertiary">
    <?php echo $this->template['Header']; ?>
    <?php echo $this->template['Content']; ?>
    <?php echo $this->template['Footer']; ?>
    <footer class="text-center my-4" id="poweredCATS">
        <?php /* WARNING: It is against the terms of the CPL to remove or alter the following line.  The 'Powered by OpenCATS' line must stay visible on every page. */ ?>
        <a href="http://www.opencats.org" target="_blank"><img src="<?php echo $assetPrefix; ?>images/CATS-powered.gif" alt="Powered by: OpenCATS - Applicant Tracking System" title="Powered by: OpenCATS - Applicant Tracking System" /></a>
    </footer>
    <script>st_init();</script>
</body>
</html>
