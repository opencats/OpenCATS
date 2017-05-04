<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php
    include_once('constants.php');
    include_once('config.php');

    /* We aren't using any TemplateUtility methods that require us to pull in
     * any of its dependencies.
     */
    /* Version check before we include this. */
   
    $phpVersion = phpversion();
    $phpVersionParts = explode('.', $phpVersion);
    if ($phpVersionParts[0] >= 5)
    {
        include_once('lib/TemplateUtility.php');
    }
    else
    {
        $php4 = true;
    }
?>
<html>
    <head>
        <title><?php echo __("OpenCATS");?> - <?php echo __("Installation Wizard Script");?></title>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="js/install.js"></script>
        <script type="text/javascript" src="js/submodal/subModal.js"></script>
        <style type="text/css" media="all">@import "modules/install/install.css";</style>
    </head>

    <body>
        <div id="headerBlock">
            <span id="mainLogo"><?php echo __("OpenCATS");?></span><br />
            <span id="subMainLogo"><?php echo __("Applicant Tracking System");?></span>
        </div>

        <div id="contents">
            <div id="login">
                <table>
                    <tr>
                        <td style="vertical-align: top; width: 200px;">
                            <table style="vertical-align: top; width: 200px; border: 1px solid #ccc;">
                                <tr>
                                    <td>
                                        <script type="text/javascript">
                                            maxSteps = 7;
                                        </script>

                                        <div id="step1" style="text-align: left;">
                                            <?php echo __("Step");?> 1: <?php echo __("System Check");?><br /><br />
                                        </div>
                                        <div id="step2" style="text-align: left;">
                                            <?php echo __("Step");?> 2: <?php echo __("Database Connectivity");?><br /><br />
                                        </div>
                                        <div id="step3" style="text-align: left;">
                                            <?php echo __("Step");?> 3: <?php echo __("Loading Data");?><br /><br />
                                        </div>
                                        <div id="step4" style="text-align: left;">
                                            <?php echo __("Step");?> 4: <nobr><?php echo __("Setup Resume Indexing");?></nobr><br /><br />
                                        </div>
                                        <div id="step5" style="text-align: left;">
                                            <?php echo __("Step");?> 5: <nobr><?php echo __("Mail Settings");?></nobr><br /><br />
                                        </div>
                                        <div id="step6" style="text-align: left;">
                                            <?php echo __("Step");?> 6: <?php echo __("Loading Extras");?><br /><br />
                                        </div>
                                        <div id="step7" style="text-align: left;">
                                            <?php echo __("Step");?> 7: <?php echo __("Finishing Installation");?>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <br />

                            <input type="button" class="button" value="<?php echo __("Restart Install");?>" onclick="Installpage_populate('a=startInstall');" style="width:200px;">
                        </td>

                        <td style="vertical-align: top; width: 550px; padding-left: 15px;">
                            <table width="100%">
                                <tr>
                                    <td style="vertical-align: top; text-align: left;" id="allSpans">
                                        <div id="installLocked" style="display: none;">
                                            <span style="font-weight: bold;"><?php echo __("OpenCATS is already installed!");?></span><br />
                                            <p>
                                            	<?php echo __("To run the installer again, you must first delete the file named INSTALL_BLOCK in the OpenCATS directory.");?> 
                                            	<?php echo __("After removing the file, you can click &quot;Retry Installation&quot; below.");?>
                                            </p><br />
                                            <input type="button" class="button" value="<?php echo __("Retry Installation");?>" onclick="Installpage_populate('a=startInstall');">
                                        </div>
                                        <div id="startInstall" style="display: none;">
                                            <span style="font: normal normal bold 18px Arial, Tahoma, sans-serif"><?php echo __("Welcome to OpenCATS!");?></span><br />

                                            <p style="text-align: justify; margin-top: 15px;">
                                            <?php echo __("This process will help you set up the OpenCATS environment for the first time.");?> 
                                            <?php echo __("Before we begin, OpenCATS needs to run some tests on your system to make sure that your web environment can support OpenCATS and is configured properly.");?>
                                            </p>
                                        </div>
                                        <div id="phpVersion" style="display: none;">
                                            <span style="font: normal normal bold 18px Arial, Tahoma, sans-serif"><?php echo __("Welcome to OpenCATS!");?></span><br />

                                            <p style="text-align: justify; margin-top: 15px;">
                                            <?php echo __("This process will help you set up the OpenCATS environment for the first time.");?> 
                                            <?php echo __("Before we begin, OpenCATS needs to run some tests on your system to make sure that your web environment can support OpenCATS and is configured properly.");?>
                                            </p>
                                            
                                            <br />
                                            <span style="font-weight: bold;"><?php echo __("Test Results");?></span>
                                            <table class="test_output">
                                            <tr class="fail"><td><?php echo sprintf(__("PHP %s or greater is required to run OpenCATS. Found version: %s."),"5.0.0",phpversion());?></td></tr>
                                            </table>
                                        </div>
                                        <div id="databaseConnectivity" style="display: none;">
                                            <span style="font-weight: bold;"><?php echo __("Database Configuration");?></span><br />
                                            <br />
                                            <?php echo __("The OpenCATS installer needs some information about your MySQL database to continue the installation.");?>
                                            <?php echo __("If you do not know this information, then please contact your website host or administrator.");?>
                                            <?php echo __("Please note that this is probably NOT the same as your FTP login information!");?><br />
                                            <br />
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><?php echo __("Database Name");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="dbname" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo __("Database User");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="dbuser" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap="nowrap"><?php echo __("Database Password");?>:</td>
                                                    <td valign="top"><input type="text" size="20" id="dbpass" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td><?php echo __("Database Host");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type="text" size="20" id="dbhost" value="localhost" /> (<?php echo __("usually");?> <i>localhost</i>)</td>
                                                </tr>
                                            </table>
                                            <br />

                                            <input type="button" class="button" id="testDatabaseConnectivity" value="<?php echo __("Test Database Connectivity");?>" onclick="Installpage_populate('a=databaseConnectivity&amp;user='+escape(document.getElementById('dbuser').value)+'&amp;pass='+escape(document.getElementById('dbpass').value)+'&amp;host='+escape(document.getElementById('dbhost').value)+'&amp;name='+escape(document.getElementById('dbname').value));" />
                                            <img src="images/indicator.gif" id="testDatabaseConnectivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
                                        </div>
                                        <div id="resumeParsing" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Resume Indexing Configuration");?></span><br />
                                            <br />
                                            <?php echo __("OpenCATS can index resumes for advanced searching with the assistance of external document processing software.");?> 
                                            <?php echo __("You need to configure the software below to enable resume indexing.");?>
                                            <br />
                                            <br />
                                            <a href="<?php echo ATS_CV_INDEXING_INFO_URL;?>os=<?php echo(urlencode(PHP_OS)); ?>&amp;server_software=<?php echo(urlencode($_SERVER['SERVER_SOFTWARE'])); ?>" target="resumeParsingSoftwareDownload">
                                                <?php echo __("Where can I get this software?");?>
                                            </a><br />
                                            <br />
                                            <input type="checkbox" id="docEnabled" checked onclick="if (this.checked) { document.getElementById('docExecutable').disabled = false; document.getElementById('docExecutable').value = document.getElementById('docExecutableOrg').value; } else { document.getElementById('docExecutable').disabled = true; document.getElementById('docExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/doc.gif" alt="" />&nbsp;&nbsp;<?php echo __(".doc file");?> (<?php echo __("Microsoft Word Document");?>)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;"><?php echo __("Path to Antiword Executable");?>:</td>
                                                    <td>
                                                        <input type="text" name="docExecutable" id="docExecutable" style="width:250px;" />
                                                        <input type="hidden" name="docExecutableOrg" id="docExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="pdfEnabled" checked onclick="if (this.checked) { document.getElementById('pdfExecutable').disabled = false; document.getElementById('pdfExecutable').value = document.getElementById('pdfExecutableOrg').value; } else { document.getElementById('pdfExecutable').disabled = true; document.getElementById('pdfExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/pdf.gif" alt="" />&nbsp;&nbsp;<?php echo __(".pdf file");?> (<?php echo __("Adobe Acrobat Document");?>)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;"><?php echo __("Path to PDFToText Executable");?>:</td>
                                                    <td>
                                                        <input type="text" name="pdfExecutable" id="pdfExecutable" style="width:250px;" />
                                                        <input type="hidden" name="pdfExecutableOrg" id="pdfExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="htmlEnabled" checked onclick="if (this.checked) { document.getElementById('htmlExecutable').disabled = false; document.getElementById('htmlExecutable').value = document.getElementById('htmlExecutableOrg').value; } else { document.getElementById('htmlExecutable').disabled = true; document.getElementById('htmlExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/txt.gif" alt="" />&nbsp;&nbsp;<?php echo __(".html file");?> (<?php echo __("Hypertext Markup Document");?>)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;"><?php echo __("Path to Html2Text Executable");?>:</td>
                                                    <td>
                                                        <input type="text" name="htmlExecutable" id="htmlExecutable" style="width:250px;" />
                                                        <input type="hidden" name="htmlExecutableOrg" id="htmlExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="rtfEnabled" checked onclick="if (this.checked) { document.getElementById('rtfExecutable').disabled = false; document.getElementById('rtfExecutable').value = document.getElementById('rtfExecutableOrg').value; } else { document.getElementById('rtfExecutable').disabled = true; document.getElementById('rtfExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/rtf.gif" alt="" />&nbsp;&nbsp;<?php echo __(".rtf file");?> (<?php echo __("Rich Text Document");?>)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;"><?php echo __("Path to UnRTF Executable");?>:</td>
                                                    <td>
                                                        <input type="text" name="rtfExecutable" id="rtfExecutable" style="width:250px;" />
                                                        <input type="hidden" name="rtfExecutableOrg" id="rtfExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Test Configuration");?>" onclick="Installpage_populate('a=testResumeParsing&amp;docExecutable='+escape(document.getElementById('docExecutable').value)+'&amp;pdfExecutable='+escape(document.getElementById('pdfExecutable').value)+'&amp;htmlExecutable='+escape(document.getElementById('htmlExecutable').value)+'&amp;rtfExecutable='+escape(document.getElementById('rtfExecutable').value));" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="<?php echo __("Skip this Step");?>" onclick="document.getElementById('resumeParsing').style.display='none';showTextBlock('mailSettings');Installpage_populate('a=mailSettings');">
                                        </div>
                                        <div id="mailSettings" style="display: none;">
                                            <span style="font-weight: bold;"><?php echo __("Mail Settings");?></span><br />

                                            <br />
                                            <?php echo __("Please enter your e-mail address (for where OpenCATS e-mails should be replied to, etc.).");?>
                                            <br />
                                            <br />
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><span id="mailFromAddressLabel">E-mail:</span> <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=35 id="mailFromAddress" value="" /></td>
                                                </tr>
                                            </table>

                                            <br />
                                            <br />
                                            <?php echo __("OpenCATS sends automatic E-Mails on different events.");?>  
                                            <?php echo __("Please choose the mechanism for E-Mail delivery via OpenCATS.");?>
                                            <br />
                                            <br />
                                            <form name="mailForm">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><?php echo __("Mail Support");?>: <span style="color: #ff0000">*</span></td>
                                                    <td>
                                                    <select id="mailSupport" name="mailSupport" onChange="changeMailForm();" class="selectBox">
                                                    <option value="opt0"><?php echo __("None");?></option>
                                                    <option value="opt1"><?php echo __("PHP Built-In Mail Support (recommended)");?></option>
                                                    <option value="opt2"><?php echo __("Sendmail");?></option>
                                                    <option value="opt3"><?php echo __("SMTP");?></option>
                                                    <option value="opt4"><?php echo __("SMTP w/Authorization");?></option>
                                                    </select>
                                                    </td>
                                                </tr>
                                            </table>

                                            <div id="mailSendmailBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><?php echo __("Sendmail Location");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSendmail" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            <div id="mailSmtpBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><?php echo __("SMTP Host");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpHost" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td width="30%"><?php echo __("SMTP Port");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpPort" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            <div id="mailSmtpAuthorizationBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%"><?php echo __("SMTP Username");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpUsername" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td width="30%"><?php echo __("SMTP Password");?>: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpPassword" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            </form>

                                            <br />

                                            <input style="float: right;" type="button" class="button" id="setMailSettings" value="Next -->" onclick="if (document.getElementById('mailFromAddress').value=='') alert('Please enter a reply E-Mail Address.'); else {Installpage_populate('a=setMailSettings&amp;mailSupport='+escape(document.getElementById('mailSupport').value)+'&amp;mailSendmail='+escape(document.getElementById('mailSendmail').value)+'&amp;mailSmtpHost='+escape(document.getElementById('mailSmtpHost').value)+'&amp;mailSmtpPort='+escape(document.getElementById('mailSmtpPort').value)+'&amp;mailSmtpUsername='+escape(document.getElementById('mailSmtpUsername').value)+'&amp;mailSmtpPassword='+escape(document.getElementById('mailSmtpPassword').value)+'&amp;mailFromAddress='+escape(document.getElementById('mailFromAddress').value));}" />
                                        </div>
                                        <div id="detectingOptional" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Extras - Detecting Installed Components");?></span><br />
                                            <br />
                                            <?php echo __("Please wait while the installer checks what components you have installed...");?><br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponents" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Installing");?></span><br />
                                            <br />
                                            <?php echo __("Please wait while the selected components are installed...");?><br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsExtra" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Extras - Installing");?></span><br />
                                            <br />
                                            <?php echo __("Please wait while the selected components are installed...");?><br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsMaint" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Performing Maintenance");?></span><br />
                                            <br />
                                            <?php echo __("Please wait whilst the OpenCATS database is brought up to date...");?><br />
											<br />
											<span id="upToDateModuleName">
											</span><br /><br />
	                                        <div id="d3" style="background-color:#eeeeee;border:1px solid black;height:20px;width:300px;padding:0px;" align="left">
	                                            <div id="d2" style="position:relative;top:0px;left:0px;background-color:#2244ff;height:20px;width:0px;padding-top:5px;padding:0px;">
	                                                <div id="d1" style="position:relative;top:0px;left:0px;color:#ffffff;height:20px;text-align:center;font:bold;padding:0px;padding-top:1px;">
	                                                </div>
	                                            </div>
	                                        </div>
											<br /><br />
											<span id="upToDateSqlQueryLabel" style="display:none;"><?php echo __("SQL Query Being Executed");?>:</span><br />
											<div id="upToDateSqlQuery" style="overflow:hidden; width: 350px; height:100px; padding: 5px; border: 1px solid #000; background-color: #fff;">
											</div>
                                            <br /><br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsMaintResume" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Performing Maintenance");?></span><br />
                                            <br />
                                            <?php echo __("Please wait while your unindexed resumes are reindexed...");?><br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="emptyDatabase" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Empty Database");?></span><br />
                                            <br />
                                            <?php echo __("The installer is ready to set up your OpenCATS data. Please pick the way you want the installer to set up OpenCATS");?>:<br />
                                            <br />
                                            <input type="radio" name="installgroup" id="emptyCheckBox" value="empty" checked>&nbsp;<?php echo __("New Installation (Recommended)");?><br />
                                            <input type="radio" name="installgroup" value="demo">&nbsp;<?php echo __("Demonstration Installation");?><br />
                                            <input type="radio" name="installgroup" value="restore">&nbsp;<?php echo __("Restore Installation from Backup");?><br />
                                            <br />
                                            <?php echo __("You can always run the installer again to clear the database and choose a different option.");?><br />
                                            <br />

                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="Installpage_populate('a=selectDBType&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroup'))));" />
                                        </div>
                                        <div id="unknownDataInDatabase" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Unknown Data in the Database");?></span><br />
                                            <br />
                                            <?php echo __("The installer has scanned the database and found unknown tables in the database. This could indicate that another application has been installed in the database beforehand.");?>
                                            <br />
                                            <br />
                                            <a href="javascript:void(0);" onclick="document.getElementById('tableNamesUnknown').style.display = ''" style="font-weight: bold;">View a list of tables in the database</a>
                                            <br />
                                            <span id="tableNamesUnknown" style="font-weight: bold; display: none;"></span>
                                            <br />
                                            <?php echo __("Please remove all of the-non OpenCATS tables to continue.");?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Remove Non-OpenCATS Tables");?>" onclick="Installpage_populate('a=resetDatabase');">&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="<?php echo __("Do Nothing and Retry Installation");?>" onclick="Installpage_populate('a=startInstall');">
                                        </div>
                                        <div id="databaseUpgrade" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Upgrade");?></span><br />
                                            <br />
                                            <?php echo sprintf(__("The installer has scanned the database and found an older version of OpenCATS in the database (%s)."),'<span id="upgradeVersion"></span>');?> 
                                            <?php echo __("The installer will upgrade your database version to the latest version automatically.");?>
                                            <br />
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="document.getElementById('databaseUpgrade').style.display='none';showTextBlock('installingComponentsMaint');Installpage_populate('a=upgradeCats');" />
                                        </div>
                                        <div id="catsUpToDate" style="display: none;">
                                            <span style="font-weight: bold;"><?php echo __("Loading Data - Existing Database");?></span><br />
                                            <br />
                                            <?php echo __("The installer has detected an existing installation of OpenCATS.");?><br />
                                            <?php echo __("How would you like to proceed?");?><br />
                                            <br />
                                            <input type="radio" name="installgroupexists" id="currentCheckBox" value="current" checked />&nbsp;<?php echo __("Use existing OpenCATS installation and automatically preform any necessary upgrade (recommended).");?><br />
                                            <br />
                                            <input type="radio" name="installgroupexists" value="empty" />&nbsp;<?php echo __("Delete existing data and create a new installation.");?><br />
                                            <input type="radio" name="installgroupexists" value="demo" />&nbsp;<?php echo __("Delete existing data and install the OpenCATS demonstration database.");?><br />
                                            <input type="radio" name="installgroupexists" value="restore" />&nbsp;<?php echo __("Delete existing data and restore a previous OpenCATS installation from backup.");?><br />
                                            <br />
                                            <?php echo __("If you choose to use the existing OpenCATS installation, you can always run\n the installer again later and choose a different option.");?><br />
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="<?php echo __("Next -->");?>" onclick="if (getCheckedValue(document.getElementsByName('installgroupexists')) == 'current') Installpage_populate('a=resumeParsing'); else {document.getElementById('catsUpToDate').style.display='none';showTextBlock('queryResetDatabase');}" />
                                        </div>
                                        <div id="queryInstallBackup" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Restore from Backup");?></span><br />
                                            <br />
                                            <?php echo __("The installer is ready to restore your backup.");?><br />
                                            <br />
                                            <?php echo sprintf(__("Please upload the file %s into the %s directory."),ATS_DB_BACKUP_FILENAME,'<b>restore</b>');?>   
                                            <?php echo sprintf(__("The installer will load the data out of the %s and set up your site accordingly, and then it will delete the file (preventing unauthorized access to the backup file)."),ATS_DB_BACKUP_FILENAME);?>
                                            <br />
                                            <br />
                                            <input type="checkbox" id="continueRestoreCheck" onclick="if (this.checked) document.getElementById('continueRestoreButton').style.display = ''; else document.getElementById('continueRestoreButton').style.display = 'none';"> <?php echo sprintf(__("I have uploaded the file %s into the restore directory."),ATS_DB_BACKUP_FILENAME);?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Continue");?>" id="continueRestoreButton" style="display: none;" onclick="document.getElementById('queryInstallBackup').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=restoreFromBackup');" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="<?php echo __("Cancel");?>" onclick="Installpage_populate('a=detectRevision');" /><br /><br />
                                        </div>
                                        <div id="queryInstallDemo" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Demo");?></span><br />
                                            <br />
                                            <?php echo sprintf(__("The installer is about to install a demonstration database for the fictitious company '%s'."),ATS_DEMO_FICT_COMPANY);?><br /> 
                                            <br />
                                            <?php echo __("The database will be pre-populated with test data that you can use to train new users on using OpenCATS.");?> 
                                            <?php echo sprintf(__("You can login using username: %s, password %s (or by clicking %s on the login screen)."),ATS_DEMO_FICT_USER,ATS_DEMO_FICT_PASS,__("Login to demo"));?><br /><br />  
                                            <?php echo __("To clear the demo data and start a production system, run the installer again.");?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Continue");?>" onclick="document.getElementById('queryInstallDemo').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=onLoadDemoData');" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="<?php echo __("Cancel");?>" onclick="Installpage_populate('a=detectRevision');" /><br /><br />
                                        </div>
                                        <div id="installCompleteProd" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Finishing Installation");?></span><br />
                                            <br />
                                            <?php echo __("The installer has finished installing OpenCATS!");?> 
                                            <?php echo __("The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.");?><br /><br />
                                            <br />
                                            <?php echo __("You may now login to OpenCATS. If it is a new installation, use the following logon information");?>:<br /><br />
                                            <?php echo __("Username: admin");?><br />
                                            <?php echo __("Password: admin");?><br />
                                            <br />
                                            <br />
                                            <?php echo sprintf(__("OpenCATS will periodically check for new versions of the software from %s, and will send non confidential information about your installation including operating system version and web browser configuration back to %s in order for us to improve OpenCATS."),ATS_CT_DOMAIN,ATS_CT_DOMAIN);?>   
                                            <?php echo __("To see what information is sent, view lib/NewVersionCheck.php.");?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Start OpenCATS");?>" onclick="window.location.href='index.php';" />
                                        </div>
                                        <div id="installCompleteDemo" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Finishing Installation - Demo");?></span><br />
                                            <br />
                                            <?php echo __("The installer has finished installing OpenCATS!");?> 
                                            <?php echo __("The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.");?>
                                            <br /><br />
                                            <br />
                                            <?php echo __("You may now login to OpenCATS.");?> 
                                            <?php echo sprintf(__("To login, either click %s on the logon screen or use the following logon information"),__("Login to demo"));?>:
                                            <br /><br />
                                            <?php echo sprintf(__("Username: %s"),ATS_DEMO_FICT_USER);?><br />
                                            <?php echo sprintf(__("Password: %s"),ATS_DEMO_FICT_PASS);?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Start OpenCATS");?>" onclick="window.location.href='index.php';" />
                                        </div>
                                        <div id="queryResetDatabase" style="display: none;">
                                            <span style="font-weight:bold;"><?php echo __("Loading Data - Existing Database");?></span><br />
                                            <br />
                                            <?php echo __("Warning: This option will delete ALL of the data in your current database. Are you sure you want to proceed?");?><br />
                                            <br />
                                            <?php echo __("THIS ACTION CANNOT BE UNDONE! Make sure you have backed up and saved all your data before you do this!");?><br />
                                            <br />
                                            <input type="button" class="button" value="<?php echo __("Yes - Delete All Data");?>" onclick="document.getElementById('queryResetDatabase').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=resetDatabase&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroupexists'))));" style="width:240px;" />&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="<?php echo __("No - Do Not Delete Data");?>" onclick="Installpage_populate('a=detectRevision');" style="width:240px;" />
                                        </div>
                                        <div id="pickOptionalComponents" style="display:none;">
                                            <span style="font-weight: bold;"><?php echo __("Loading Extras - Localization");?></span><br />
                                            <br />
                                            <table>
                                                <tr>
                                                    <td><?php echo __("Please choose your time zone.");?></td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px;"><?php if (!isset($php4)) TemplateUtility::printTimeZoneSelect('timeZone', 'width: 420px;', '', OFFSET_GMT); ?></td>
                                                </tr>

                                                <tr>
                                                    <td><?php echo __("Please choose your preferred date format.");?></td>
                                                </tr>
                                                <tr>
                                                    <td>
                                                        <select id="dateFormat" name="dateFormat" style="width: 150px;" class="selectBox">
                                                            <option value="mdy" selected="selected">MM-DD-YYYY (US)</option>
                                                            <option value="dmy">DD-MM-YYYY (UK)</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <br />
                                            <span style="font-weight: bold;"><?php echo __("Loading Extras - Choose Extras");?></span><br />
                                            <br />
                                            <?php echo __("OpenCATS comes with some optional features. You can enable them below.");?><br />
                                            <br />
                                            <?php echo __("To add or remove features in the future, run the installer again.");?><br />
                                            <br />

                                            <span id="extrasList"></span>
                                        </div>

                                        <span id="subFormBlock" style="font: normal normal normal 9pt Arial, Tahoma, sans-serif;"></span>

                                        <div id="testPassed" style="display: none;">
                                            <table class="footer_pass"><tr><td><?php echo __("All tests passed successfully!");?></td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="<?php echo __("Next -->");?>" onclick="Installpage_populate('a=databaseConnectivity');" />
                                        </div>
                                        <div id="testPassedParsing" style="display: none;">
                                            <table class="footer_pass"><tr><td><?php echo __("All tests passed successfully!");?></td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="<?php echo __("Next -->");?>" onclick="document.getElementById('resumeParsing').style.display='none';document.getElementById('subFormBlock').style.display='none';document.getElementById('testPassedParsing').style.display='none';showTextBlock('mailSettings'); Installpage_populate('a=mailSettings');" />
                                        </div>
                                        <div id="testWarning" style="display: none;">
                                            <table class="footer_warning"><tr><td><?php echo __("One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.");?><br />
                                            <br />
                                            <?php echo sprintf(__("If you have any questions, visit the OpenCATS forums at %s."),'<a href="'.ATS_FORUM_URL.'">'.ATS_FORUM_URL.'</a>');?>
                                            </td></tr></table><br />
                                            <input style="float: right;" type="button" class="button" value="<?php echo __("Next -->");?>" onclick="Installpage_populate('a=databaseConnectivity');" />
                                        </div>
                                        <div id="testFailed" style="display: none;">
                                            <table class="footer_fail"><tr><td><?php echo __("One or more tests failed. Please correct the errors and try again.");?><br />
                                            <br />
                                            <?php echo sprintf(__("If you have any questions, visit the OpenCATS forums at %s."),'<a href="'.ATS_FORUM_URL.'">'.ATS_FORUM_URL.'</a>');?>
                                            
                                            </td></tr></table><br />
                                            <input type="button" class="button" value="<?php echo __("Retry Installation");?>" onclick="Installpage_populate('a=startInstall');" />
                                        </div>
                                        <div id="testFailedWarning" style="display: none;"><table class="footer_warning"><tr><td><?php echo __("One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.");?><br />
                                            <br />
                                            <?php echo sprintf(__("If you have any questions, visit the OpenCATS forums at %s."),'<a href="'.ATS_FORUM_URL.'">'.ATS_FORUM_URL.'</a>');?>
                                            </td></tr></table>

                                            <table class="footer_fail"><tr><td><?php echo __("One or more tests failed. Please correct the errors and try again.");?><br />
                                            <br />
                                            <?php echo sprintf(__("If you have any questions, visit the OpenCATS forums at %s."),'<a href="'.ATS_FORUM_URL.'">'.ATS_FORUM_URL.'</a>');?>                                            
                                            </td></tr></table><br />
                                            <input type="button" class="button" value="<?php echo __("Retry Installation");?>" onclick="Installpage_populate('a=startInstall');" />
                                        </div>


                                        <div id="MySQLTestPassed" style="display: none;">
                                            <table class="footer_pass"><tr><td><?php echo __("All tests passed successfully!");?></td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="<?php echo __("Next -->");?>" onclick="Installpage_populate('a=detectRevision');" />
                                        </div>
                                        <div id="MySQLTestFailed" style="display: none;">
                                            <table class="footer_fail"><tr><td><?php echo __("One or more tests failed. Please correct the errors and try again.");?><br />
                                            <br />
                                            <?php echo sprintf(__("If you have any questions, visit the OpenCATS forums at %s."),'<a href="'.ATS_FORUM_URL.'">'.ATS_FORUM_URL.'</a>');?>                                           
                                            </td></tr></table>
                                        </div>

                                        <div style="clear: both;"></div>
                                        <div id="execute" style="display: none;"><!--This is so we can execute incoming JS.--></div>
                                    </td>
                                </tr>
                            </table>

                            <script type="text/javascript">
                                <?php if (!isset($php4)): ?>
                                    Installpage_populate('a=startInstall');
                                <?php else: ?>
                                    setActiveStep(1);
                                    showTextBlock("phpVersion");
                                    showTextBlock("testFailed");
                                <?php endif; ?>
                            </script>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </body>
</html>
