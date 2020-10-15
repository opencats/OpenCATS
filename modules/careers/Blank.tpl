<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
        <title><?php $this->_($this->siteName); ?> - Careers</title>
            <script type="text/javascript" src="../js/careerPortalApply.js"></script>
        <?php global $careerPage; if (isset($careerPage) && $careerPage == true): ?>
            <script type="text/javascript" src="../js/lib.js"></script>
            <script type="text/javascript" src="../js/sorttable.js"></script>
            <script type="text/javascript" src="../js/calendarDateInput.js"></script>
        <?php else: ?>
            <script type="text/javascript" src="js/lib.js"></script>
            <script type="text/javascript" src="js/sorttable.js"></script>
            <script type="text/javascript" src="js/calendarDateInput.js"></script>
			<script type="text/javascript" src="js/careersPage.js"></script>
        <?php endif; ?>
        <style type="text/css" media="all">
            <?php echo($this->template['CSS']); ?>
			#poweredCATS { clear: both; margin: 30px auto; clear: both; width: 140px; height: 40px; border: none;}
			#poweredCATS img { border: none; }
        </style>
    </head>
    <body>
    <!-- TOP -->
    <?php echo($this->template['Header']); ?>

    <!-- CONTENT -->
    <?php echo($this->template['Content']); ?>

    <!-- FOOTER -->
    <?php echo($this->template['Footer']); ?>
    <div style="font-size:9px;">
        <br /><br /><br /><br />
    </div>
    <div style="text-align:center;">

        <?php /* WARNING: It is against the terms of the CPL to remove or alter the following line.  The 'Powered by OpenCATS' line must stay visible on every page. */ ?>
        <div id="poweredCATS">
		<a href="http://www.opencats.org" target="_blank"><img src="../images/CATS-powered.gif" alt="Powered by: OpenCATS - Applicant Tracking System" title="Powered by: OpenCATS - Applicant Tracking System" /></a>
		</div>
    </div>
    <script type="text/javascript">st_init();</script>
    </body>
</html>
