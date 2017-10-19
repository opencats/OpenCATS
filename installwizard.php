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
        <title>OpenCATS - Installation Wizard Script</title>
        <script type="text/javascript" src="js/lib.js"></script>
        <script type="text/javascript" src="js/install.js"></script>
        <script type="text/javascript" src="js/submodal/subModal.js"></script>
        <style type="text/css" media="all">@import "modules/install/install.css";</style>
    </head>

    <body>
        <div id="headerBlock">
            <span id="mainLogo">OpenCATS</span><br />
            <span id="subMainLogo">Applicant Tracking System</span>
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
                                            Step 1: System Check<br /><br />
                                        </div>
                                        <div id="step2" style="text-align: left;">
                                            Step 2: Database Connectivity<br /><br />
                                        </div>
                                        <div id="step3" style="text-align: left;">
                                            Step 3: Loading Data<br /><br />
                                        </div>
                                        <div id="step4" style="text-align: left;">
                                            Step 4: <nobr>Setup Resume Indexing</nobr><br /><br />
                                        </div>
                                        <div id="step5" style="text-align: left;">
                                            Step 5: <nobr>Mail Settings</nobr><br /><br />
                                        </div>
                                        <div id="step6" style="text-align: left;">
                                            Step 6: Loading Extras<br /><br />
                                        </div>
                                        <div id="step7" style="text-align: left;">
                                            Step 7: Finishing Installation
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <br />

                            <input type="button" class="button" value="Restart Install" onclick="Installpage_populate('a=startInstall');" style="width:200px;">
                        </td>

                        <td style="vertical-align: top; width: 550px; padding-left: 15px;">
                            <table width="100%">
                                <tr>
                                    <td style="vertical-align: top; text-align: left;" id="allSpans">
                                        <div id="installLocked" style="display: none;">
                                            <span style="font-weight: bold;">OpenCATS is already installed!</span><br />
                                            <p>To run the installer again, you must first delete the file named INSTALL_BLOCK in the OpenCATS directory. After removing the file, you can click &quot;Retry Installation&quot; below.</p><br />
                                            <input type="button" class="button" value="Retry Installation" onclick="Installpage_populate('a=startInstall');">
                                        </div>
                                        <div id="startInstall" style="display: none;">
                                            <span style="font: normal normal bold 18px Arial, Tahoma, sans-serif">Welcome to OpenCATS!</span><br />

                                            <p style="text-align: justify; margin-top: 15px;">This process will help you set up the OpenCATS environment
                                            for the first time. Before we begin, OpenCATS needs to run some tests on your system to make sure that your
                                            web environment can support OpenCATS and is configured properly.</p>
                                        </div>
                                        <div id="phpVersion" style="display: none;">
                                            <span style="font: normal normal bold 18px Arial, Tahoma, sans-serif">Welcome to OpenCATS!</span><br />

                                            <p style="text-align: justify; margin-top: 15px;">This process will help you set up the OpenCATS environment
                                            for the first time. Before we begin, OpenCATS needs to run some tests on your system to make sure that your
                                            web environment can support OpenCATS and is configured properly.</p>
                                            
                                            <br />
                                            <span style="font-weight: bold;">Test Results</span>
                                            <table class="test_output">
                                            <tr class="fail"><td>PHP 5.0.0 or greater is required to run OpenCATS. Found version: <?php echo(phpversion()); ?>.</td></tr>
                                            </table>
                                        </div>
                                        <div id="databaseConnectivity" style="display: none;">
                                            <span style="font-weight: bold;">Database Configuration</span><br />
                                            <br />
                                            The OpenCATS installer needs some information about your MySQL database to continue the installation.
                                            If you do not know this information, then please contact your website host or administrator.
                                            Please note that this is probably NOT the same as your FTP login information!<br />
                                            <br />
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%">Database Name: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="dbname" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td>Database User: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="dbuser" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td nowrap="nowrap">Database Password:</td>
                                                    <td valign="top"><input type="text" size="20" id="dbpass" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td>Database Host: <span style="color: #ff0000">*</span></td>
                                                    <td><input type="text" size="20" id="dbhost" value="localhost" /> (usually <i>localhost</i>)</td>
                                                </tr>
                                            </table>
                                            <br />

                                            <input type="button" class="button" id="testDatabaseConnectivity" value="Test Database Connectivity" onclick="Installpage_populate('a=databaseConnectivity&amp;user='+escape(document.getElementById('dbuser').value)+'&amp;pass='+escape(document.getElementById('dbpass').value)+'&amp;host='+escape(document.getElementById('dbhost').value)+'&amp;name='+escape(document.getElementById('dbname').value));" />
                                            <img src="images/indicator.gif" id="testDatabaseConnectivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
                                        </div>
                                        <div id="resumeParsing" style="display: none;">
                                            <span style="font-weight:bold;">Resume Indexing Configuration</span><br />
                                            <br />
                                            OpenCATS can index resumes for advanced searching with the assistance of external
                                            document processing software. You need to configure the software below
                                            to enable resume indexing.<br />
                                            <br />
                                            <a href="http://www.catsone.com/resumeIndexingSoftware.php?os=<?php echo(urlencode(PHP_OS)); ?>&amp;server_software=<?php echo(urlencode($_SERVER['SERVER_SOFTWARE'])); ?>" target="resumeParsingSoftwareDownload">
                                                Where can I get this software?
                                            </a><br />
                                            <br />
                                            <input type="checkbox" id="docEnabled" checked onclick="if (this.checked) { document.getElementById('docExecutable').disabled = false; document.getElementById('docExecutable').value = document.getElementById('docExecutableOrg').value; } else { document.getElementById('docExecutable').disabled = true; document.getElementById('docExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/doc.gif" alt="" />&nbsp;&nbsp;.doc file (Microsoft Word Document)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;">Path to Antiword Executable:</td>
                                                    <td>
                                                        <input type="text" name="docExecutable" id="docExecutable" style="width:250px;" />
                                                        <input type="hidden" name="docExecutableOrg" id="docExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="pdfEnabled" checked onclick="if (this.checked) { document.getElementById('pdfExecutable').disabled = false; document.getElementById('pdfExecutable').value = document.getElementById('pdfExecutableOrg').value; } else { document.getElementById('pdfExecutable').disabled = true; document.getElementById('pdfExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/pdf.gif" alt="" />&nbsp;&nbsp;.pdf file (Adobe Acrobat Document)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;">Path to PDFToText Executable:</td>
                                                    <td>
                                                        <input type="text" name="pdfExecutable" id="pdfExecutable" style="width:250px;" />
                                                        <input type="hidden" name="pdfExecutableOrg" id="pdfExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="htmlEnabled" checked onclick="if (this.checked) { document.getElementById('htmlExecutable').disabled = false; document.getElementById('htmlExecutable').value = document.getElementById('htmlExecutableOrg').value; } else { document.getElementById('htmlExecutable').disabled = true; document.getElementById('htmlExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/txt.gif" alt="" />&nbsp;&nbsp;.html file (Hypertext Markup Document)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;">Path to Html2Text Executable:</td>
                                                    <td>
                                                        <input type="text" name="htmlExecutable" id="htmlExecutable" style="width:250px;" />
                                                        <input type="hidden" name="htmlExecutableOrg" id="htmlExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="checkbox" id="rtfEnabled" checked onclick="if (this.checked) { document.getElementById('rtfExecutable').disabled = false; document.getElementById('rtfExecutable').value = document.getElementById('rtfExecutableOrg').value; } else { document.getElementById('rtfExecutable').disabled = true; document.getElementById('rtfExecutable').value = ''; }">
                                            &nbsp;
                                            <img src="images/file/rtf.gif" alt="" />&nbsp;&nbsp;.rtf file (Rich Text Document)
                                            <table>
                                                <tr>
                                                    <td width="10px;">&nbsp;</td>
                                                    <td width="170px;">Path to UnRTF Executable:</td>
                                                    <td>
                                                        <input type="text" name="rtfExecutable" id="rtfExecutable" style="width:250px;" />
                                                        <input type="hidden" name="rtfExecutableOrg" id="rtfExecutableOrg" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <br />
                                            <input type="button" class="button" value="Test Configuration" onclick="Installpage_populate('a=testResumeParsing&amp;docExecutable='+escape(document.getElementById('docExecutable').value)+'&amp;pdfExecutable='+escape(document.getElementById('pdfExecutable').value)+'&amp;htmlExecutable='+escape(document.getElementById('htmlExecutable').value)+'&amp;rtfExecutable='+escape(document.getElementById('rtfExecutable').value));" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="Skip this Step" onclick="document.getElementById('resumeParsing').style.display='none';showTextBlock('mailSettings');Installpage_populate('a=mailSettings');">
                                        </div>
                                        <div id="mailSettings" style="display: none;">
                                            <span style="font-weight: bold;">Mail Settings</span><br />

                                            <br />
                                            Please enter your e-mail address (for where OpenCATS e-mails should be replied to, etc.).
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
                                            OpenCATS sends automatic E-Mails on different events.  Please choose the mechanism for E-Mail delivery via OpenCATS.<br />
                                            <br />
                                            <form name="mailForm">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%">Mail Support: <span style="color: #ff0000">*</span></td>
                                                    <td>
                                                    <select id="mailSupport" name="mailSupport" onChange="changeMailForm();" class="selectBox">
                                                    <option value="opt0">None</option>
                                                    <option value="opt1">PHP Built-In Mail Support (recommended)</option>
                                                    <option value="opt2">Sendmail</option>
                                                    <option value="opt3">SMTP</option>
                                                    <option value="opt4">SMTP w/Authorization</option>
                                                    </select>
                                                    </td>
                                                </tr>
                                            </table>

                                            <div id="mailSendmailBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%">Sendmail Location: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSendmail" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            <div id="mailSmtpBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%">SMTP Host: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpHost" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">SMTP Port: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpPort" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            <div id="mailSmtpAuthorizationBox" style="display: none;">
                                            <table width="100%">
                                                <tr>
                                                    <td width="30%">SMTP Username: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpUsername" value="" /></td>
                                                </tr>
                                                <tr>
                                                    <td width="30%">SMTP Password: <span style="color: #ff0000">*</span></td>
                                                    <td><input type=text size=20 id="mailSmtpPassword" value="" /></td>
                                                </tr>
                                            </table>
                                            </div>

                                            </form>

                                            <br />

                                            <input style="float: right;" type="button" class="button" id="setMailSettings" value="Next -->" onclick="if (document.getElementById('mailFromAddress').value=='') alert('Please enter a reply E-Mail Address.'); else {Installpage_populate('a=setMailSettings&amp;mailSupport='+escape(document.getElementById('mailSupport').value)+'&amp;mailSendmail='+escape(document.getElementById('mailSendmail').value)+'&amp;mailSmtpHost='+escape(document.getElementById('mailSmtpHost').value)+'&amp;mailSmtpPort='+escape(document.getElementById('mailSmtpPort').value)+'&amp;mailSmtpUsername='+escape(document.getElementById('mailSmtpUsername').value)+'&amp;mailSmtpPassword='+escape(document.getElementById('mailSmtpPassword').value)+'&amp;mailFromAddress='+escape(document.getElementById('mailFromAddress').value));}" />
                                        </div>
                                        <div id="detectingOptional" style="display: none;">
                                            <span style="font-weight:bold;">Loading Extras - Detecting Installed Components</span><br />
                                            <br />
                                            Please wait while the installer checks what components you have installed...<br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponents" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Installing</span><br />
                                            <br />
                                            Please wait while the selected components are installed...<br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsExtra" style="display: none;">
                                            <span style="font-weight:bold;">Loading Extras - Installing</span><br />
                                            <br />
                                            Please wait while the selected components are installed...<br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsMaint" style="display: none;">
                                            <span style="font-weight:bold;">Performing Maintenance</span><br />
                                            <br />
                                            Please wait whilst the OpenCATS database is brought up to date...<br />
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
											<span id="upToDateSqlQueryLabel" style="display:none;">SQL Query Being Executed:</span><br />
											<div id="upToDateSqlQuery" style="overflow:hidden; width: 350px; height:100px; padding: 5px; border: 1px solid #000; background-color: #fff;">
											</div>
                                            <br /><br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="installingComponentsMaintResume" style="display: none;">
                                            <span style="font-weight:bold;">Performing Maintenance</span><br />
                                            <br />
                                            Please wait while your unindexed resumes are reindexed...<br />
                                            <br />
                                            <img src="images/indicator.gif" alt="" />
                                        </div>
                                        <div id="emptyDatabase" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Empty Database</span><br />
                                            <br />
                                            The installer is ready to set up your OpenCATS data. Please pick the way you want the installer to set up OpenCATS:<br />
                                            <br />
                                            <input type="radio" name="installgroup" id="emptyCheckBox" value="empty" checked>&nbsp;New Installation (Recommended)<br />
                                            <input type="radio" name="installgroup" value="demo">&nbsp;Demonstration Installation<br />
                                            <input type="radio" name="installgroup" value="restore">&nbsp;Restore Installation from Backup<br />
                                            <br />
                                            You can always run the installer again to clear the database and choose a different option.<br />
                                            <br />

                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="Installpage_populate('a=selectDBType&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroup'))));" />
                                        </div>
                                        <div id="unknownDataInDatabase" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Unknown Data in the Database</span><br />
                                            <br />
                                            The installer has scanned the database and found unknown tables in the database. This could indicate that another application has been
                                            installed in the database beforehand.<br />
                                            <br />
                                            <a href="javascript:void(0);" onclick="document.getElementById('tableNamesUnknown').style.display = ''" style="font-weight: bold;">View a list of tables in the database</a>
                                            <br />
                                            <span id="tableNamesUnknown" style="font-weight: bold; display: none;"></span>
                                            <br />
                                            Please remove all of the-non OpenCATS tables to continue.<br />
                                            <br />
                                            <input type="button" class="button" value="Remove Non-OpenCATS Tables" onclick="Installpage_populate('a=resetDatabase');">&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="Do Nothing and Retry Installation" onclick="Installpage_populate('a=startInstall');">
                                        </div>
                                        <div id="databaseUpgrade" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Upgrade</span><br />
                                            <br />
                                            The installer has scanned the database and found an older version of OpenCATS in the database (<span id="upgradeVersion"></span>). The installer
                                            will upgrade your database version to the latest version automatically.<br />
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="document.getElementById('databaseUpgrade').style.display='none';showTextBlock('installingComponentsMaint');Installpage_populate('a=upgradeCats');" />
                                        </div>
                                        <div id="catsUpToDate" style="display: none;">
                                            <span style="font-weight: bold;">Loading Data - Existing Database</span><br />
                                            <br />
                                            The installer has detected an existing installation of OpenCATS.<br />
                                            How would you like to proceed?<br />
                                            <br />
                                            <input type="radio" name="installgroupexists" id="currentCheckBox" value="current" checked />&nbsp;Use existing OpenCATS installation and automatically perform any necessary upgrade (recommended).<br />
                                            <br />
                                            <input type="radio" name="installgroupexists" value="empty" />&nbsp;Delete existing data and create a new installation.<br />
                                            <input type="radio" name="installgroupexists" value="demo" />&nbsp;Delete existing data and install the OpenCATS demonstration database.<br />
                                            <input type="radio" name="installgroupexists" value="restore" />&nbsp;Delete existing data and restore a previous OpenCATS installation from backup.<br />
                                            <br />
                                            If you choose to use the existing OpenCATS installation, you can always run<br />
                                            the installer again later and choose a different option.<br />
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="if (getCheckedValue(document.getElementsByName('installgroupexists')) == 'current') Installpage_populate('a=resumeParsing'); else {document.getElementById('catsUpToDate').style.display='none';showTextBlock('queryResetDatabase');}" />
                                        </div>
                                        <div id="queryInstallBackup" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Restore from Backup</span><br />
                                            <br />
                                            The installer is ready to restore your backup.<br />
                                            <br />
                                            Please upload the file catsbackup.bak into the <b>restore</b> directory. The installer will load the data
                                            out of the catsbackup.bak and set up your site accordingly, and then it will delete the file (preventing
                                            unauthorized access to the backup file).<br />
                                            <br />
                                            <input type="checkbox" id="continueRestoreCheck" onclick="if (this.checked) document.getElementById('continueRestoreButton').style.display = ''; else document.getElementById('continueRestoreButton').style.display = 'none';"> I have uploaded the file catsbackup.bak into the restore directory.<br />
                                            <br />
                                            <input type="button" class="button" value="Continue" id="continueRestoreButton" style="display: none;" onclick="document.getElementById('queryInstallBackup').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=restoreFromBackup');" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="Cancel" onclick="Installpage_populate('a=detectRevision');" /><br /><br />
                                        </div>
                                        <div id="queryInstallDemo" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Demo</span><br />
                                            <br />
                                            The installer is about to install a demonstration database for the fictitious company 'MyCompany.NET'.<br />
                                            <br />
                                            The database will be pre-populated with test data that you can use to train new users on using OpenCATS. You can login using
                                            username: john@mycompany.net, password john99 (or by clicking Login to demo on the login screen).<br /><br />
                                            To clear the demo data and start a production system, run the installer again.<br />
                                            <br />
                                            <input type="button" class="button" value="Continue" onclick="document.getElementById('queryInstallDemo').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=onLoadDemoData');" />&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="Cancel" onclick="Installpage_populate('a=detectRevision');" /><br /><br />
                                        </div>
                                        <div id="installCompleteProd" style="display: none;">
                                            <span style="font-weight:bold;">Finishing Installation</span><br />
                                            <br />
                                            The installer has finished installing OpenCATS! The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.<br /><br />
                                            <br />
                                            You may now login to OpenCATS. If it is a new installation, use the following logon information:<br /><br />
                                            Username: admin<br />
                                            Password: admin<br />
                                            <br />
                                            <br />
                                            OpenCATS will periodically check for new versions of the software from catsone.com, and will send non confidential information about your
                                            installation including operating system version and web browser configuration back to catsone.com in order for us to improve OpenCATS.  To see what information is sent, view
                                            lib/NewVersionCheck.php.<br />
                                            <br />
                                            <input type="button" class="button" value="Start OpenCATS" onclick="window.location.href='index.php';" />
                                        </div>
                                        <div id="installCompleteDemo" style="display: none;">
                                            <span style="font-weight:bold;">Finishing Installation - Demo</span><br />
                                            <br />
                                            The installer has finished installing OpenCATS! The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.<br /><br />
                                            <br />
                                            You may now login to OpenCATS. To login, either click "Login to Demo Account" on the logon screen or use the following logon information:<br /><br />
                                            Username: john@mycompany.net<br />
                                            Password: john99<br />
                                            <br />
                                            <input type="button" class="button" value="Start OpenCATS" onclick="window.location.href='index.php';" />
                                        </div>
                                        <div id="queryResetDatabase" style="display: none;">
                                            <span style="font-weight:bold;">Loading Data - Existing Database</span><br />
                                            <br />
                                            Warning: This option will delete ALL of the data in your current database. Are you sure you want to proceed?<br />
                                            <br />
                                            THIS ACTION CANNOT BE UNDONE! Make sure you have backed up and saved all your data before you do this!<br />
                                            <br />
                                            <input type="button" class="button" value="Yes - Delete All Data" onclick="document.getElementById('queryResetDatabase').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=resetDatabase&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroupexists'))));" style="width:240px;" />&nbsp;&nbsp;&nbsp;&nbsp;
                                            <input type="button" class="button" value="No - Do Not Delete Data" onclick="Installpage_populate('a=detectRevision');" style="width:240px;" />
                                        </div>
                                        <div id="pickOptionalComponents" style="display:none;">
                                            <span style="font-weight: bold;">Loading Extras - Localization</span><br />
                                            <br />
                                            <table>
                                                <tr>
                                                    <td>Please choose your time zone.</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding-bottom: 10px;"><?php if (!isset($php4)) TemplateUtility::printTimeZoneSelect('timeZone', 'width: 420px;', '', OFFSET_GMT); ?></td>
                                                </tr>

                                                <tr>
                                                    <td>Please choose your preferred date format.</td>
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
                                            <span style="font-weight: bold;">Loading Extras - Choose Extras</span><br />
                                            <br />
                                            OpenCATS comes with some optional features. You can enable them below.<br />
                                            <br />
                                            To add or remove features in the future, run the installer again.<br />
                                            <br />

                                            <span id="extrasList"></span>
                                        </div>

                                        <span id="subFormBlock" style="font: normal normal normal 9pt Arial, Tahoma, sans-serif;"></span>

                                        <div id="testPassed" style="display: none;">
                                            <table class="footer_pass"><tr><td>All tests passed successfully!</td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="Installpage_populate('a=databaseConnectivity');" />
                                        </div>
                                        <div id="testPassedParsing" style="display: none;">
                                            <table class="footer_pass"><tr><td>All tests passed successfully!</td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="document.getElementById('resumeParsing').style.display='none';document.getElementById('subFormBlock').style.display='none';document.getElementById('testPassedParsing').style.display='none';showTextBlock('mailSettings'); Installpage_populate('a=mailSettings');" />
                                        </div>
                                        <div id="testWarning" style="display: none;">
                                            <table class="footer_warning"><tr><td>One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.<br />
                                            <br />
                                            If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</td></tr></table><br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="Installpage_populate('a=databaseConnectivity');" />
                                        </div>
                                        <div id="testFailed" style="display: none;">
                                            <table class="footer_fail"><tr><td>One or more tests failed. Please correct the errors and try again.<br />
                                            <br />
                                            If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</td></tr></table><br />
                                            <input type="button" class="button" value="Retry Installation" onclick="Installpage_populate('a=startInstall');" />
                                        </div>
                                        <div id="testFailedWarning" style="display: none;"><table class="footer_warning"><tr><td>One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.<br />
                                            <br />
                                            If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</td></tr></table>

                                            <table class="footer_fail"><tr><td>One or more tests failed. Please correct the errors and try again.<br />
                                            <br />
                                            If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</td></tr></table><br />
                                            <input type="button" class="button" value="Retry Installation" onclick="Installpage_populate('a=startInstall');" />
                                        </div>


                                        <div id="MySQLTestPassed" style="display: none;">
                                            <table class="footer_pass"><tr><td>All tests passed successfully!</td></tr></table>
                                            <br />
                                            <input style="float: right;" type="button" class="button" value="Next -->" onclick="Installpage_populate('a=detectRevision');" />
                                        </div>
                                        <div id="MySQLTestFailed" style="display: none;">
                                            <table class="footer_fail"><tr><td>One or more tests failed. Please correct the errors and try again.<br />
                                            <br />
                                            If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</td></tr></table>
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
