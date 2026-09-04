<?php /* Installed-system database maintenance. */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>OpenCATS - Database Maintenance</title>
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo(HTML_ENCODING); ?>" />
        <script type="text/javascript">CATSCsrfToken = <?php echo Template::escapeJs($this->csrfToken); ?>;</script>
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
            <div id="login" style="width: 500px;">
                <div style="text-align: left;">
                    <span style="font-weight: bold;">Database Maintenance</span>
                    <p>Database migrations are pending. OpenCATS should be updated before normal use continues.</p>
                    <p><input type="button" class="button" id="startMaintenance" value="Start Maintenance" onclick="this.disabled = true; document.getElementById('maintenanceError').style.display = 'none'; document.getElementById('maintenanceProgress').style.display = ''; maintenanceOnly = true; Installpage_maint();" /></p>
                    <p id="maintenanceError" style="display: none; color: #b00000;">Maintenance could not be completed. Please reload the page and try again.</p>

                    <div id="maintenanceProgress" style="display: none;">
                        <span id="upToDateModuleName"></span><br /><br />
                        <div id="d3" style="background-color:#eeeeee;border:1px solid black;height:20px;width:300px;padding:0px;" align="left">
                            <div id="d2" style="position:relative;top:0px;left:0px;background-color:#2244ff;height:20px;width:0px;padding-top:5px;padding:0px;">
                                <div id="d1" style="position:relative;top:0px;left:0px;color:#ffffff;height:20px;text-align:center;font:bold;padding:0px;padding-top:1px;"></div>
                            </div>
                        </div>
                        <br />
                        <span id="upToDateSqlQueryLabel" style="display:none;">SQL Query Being Executed:</span><br />
                        <div id="upToDateSqlQuery" style="overflow:hidden; width: 350px; height:100px; padding: 5px; border: 1px solid #000; background-color: #fff;"></div>
                    </div>

                    <span id="subFormBlock"></span>
                </div>
            </div>
        </div>
    </body>
</html>
