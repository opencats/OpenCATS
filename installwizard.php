<?php
    include_once('constants.php');
    include_once('config.php');

    $minimumPHPVersion = '8.4.1';
    $isSupportedPHPVersion = version_compare(
        PHP_VERSION,
        $minimumPHPVersion,
        '>='
    );

    if ($isSupportedPHPVersion)
    {
        include_once(LEGACY_ROOT . '/lib/Template.php');
        include_once(LEGACY_ROOT . '/lib/Session.php');
        include_once(LEGACY_ROOT . '/lib/TemplateUtility.php');

        session_name(CATS_SESSION_NAME);
        session_start();
    }


    $installLibURL = 'js/lib.js';
    $installScriptURL = 'js/install.js';
    $subModalScriptURL = 'js/submodal/subModal.js';
    $bootstrapCSSURL = 'vendor/twbs/bootstrap/dist/css/bootstrap.min.css';
    $installCSSURL = 'modules/install/install.css';
    if ($isSupportedPHPVersion)
    {
        $bootstrapCSSURL = call_user_func(array('TemplateUtility', 'getVersionedAssetURL'), $bootstrapCSSURL);
        $installLibURL = call_user_func(array('TemplateUtility', 'getVersionedAssetURL'), $installLibURL);
        $installScriptURL = call_user_func(array('TemplateUtility', 'getVersionedAssetURL'), $installScriptURL);
        $subModalScriptURL = call_user_func(array('TemplateUtility', 'getVersionedAssetURL'), $subModalScriptURL);
        $installCSSURL = call_user_func(array('TemplateUtility', 'getVersionedAssetURL'), $installCSSURL);
    }
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>OpenCATS - Installation Wizard Script</title>
        <?php
            if ($isSupportedPHPVersion && isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
            {
                echo '<script type="text/javascript">CATSCsrfToken = ',
                     Template::escapeJs($_SESSION['CATS']->getCSRFToken()), ';</script>', "\n";
            }
        ?>
        <script type="text/javascript" src="<?php echo $installLibURL; ?>"></script>
        <script type="text/javascript" src="<?php echo $installScriptURL; ?>"></script>
        <script type="text/javascript" src="<?php echo $subModalScriptURL; ?>"></script>
        <link rel="stylesheet" href="<?php echo $bootstrapCSSURL; ?>">
        <link rel="stylesheet" href="<?php echo $installCSSURL; ?>">
    </head>

    <body class="installer bg-body-tertiary">
        <header id="headerBlock" class="container text-center py-4">
            <span id="mainLogo" class="h2 fw-bold text-primary">OpenCATS</span><br />
            <span id="subMainLogo" class="text-body-secondary">Applicant Tracking System</span>
        </header>

        <main id="contents" class="container pb-4">
            <div id="login" class="row g-4">
                <nav class="col-12 col-lg-3" aria-label="Installation progress">
                    <script>maxSteps = 7;</script>
                    <ol class="list-group mb-3">
                        <li id="step1" class="list-group-item">Step 1: System Check</li>
                        <li id="step2" class="list-group-item">Step 2: Database Connectivity</li>
                        <li id="step3" class="list-group-item">Step 3: Loading Data</li>
                        <li id="step4" class="list-group-item">Step 4: Setup Resume Indexing</li>
                        <li id="step5" class="list-group-item">Step 5: Mail Settings</li>
                        <li id="step6" class="list-group-item">Step 6: Loading Extras</li>
                        <li id="step7" class="list-group-item">Step 7: Finishing Installation</li>
                    </ol>
                    <input type="button" class="btn btn-outline-secondary w-100" value="Restart Install" onclick="Installpage_populate('a=startInstall');">
                </nav>
                <section class="col-12 col-lg-9" aria-label="Installation">
                    <section id="allSpans" class="card card-body p-3 p-md-4">
                        <div class="alert alert-warning" id="installLocked" style="display: none;">
                            <h1 class="h5 mb-3">OpenCATS is already installed!</h1>
                            <p>To run the installer again, you must first delete the file named INSTALL_BLOCK in the OpenCATS directory. After removing the file, you can click &quot;Retry Installation&quot; below.</p>
                            <input type="button" class="btn btn-primary" value="Retry Installation" onclick="Installpage_populate('a=startInstall');">
                        </div>
                        <div id="startInstall" style="display: none;">
                            <h1 class="h5 mb-3">Welcome to OpenCATS!</h1>
                            <p>This process will help you set up the OpenCATS environment
                            for the first time. Before we begin, OpenCATS needs to run some tests on your system to make sure that your
                            web environment can support OpenCATS and is configured properly.</p>
                        </div>
                        <div id="phpVersion" style="display: none;">
                            <h1 class="h5 mb-3">Welcome to OpenCATS!</h1>
                            <p>This process will help you set up the OpenCATS environment
                            for the first time. Before we begin, OpenCATS needs to run some tests on your system to make sure that your
                            web environment can support OpenCATS and is configured properly.</p>
                            <h1 class="h5 mb-3">Test Results</h1>
                            <table class="table test_output">
                                <tr class="fail">
                                    <td>
                                        OpenCATS requires PHP
                                        <?php echo htmlspecialchars($minimumPHPVersion, ENT_QUOTES, 'UTF-8'); ?>
                                        or newer. Found PHP
                                        <?php echo htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8'); ?>.
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div id="databaseConnectivity" style="display: none;">
                            <h1 class="h5 mb-3">Database Configuration</h1>
                            <p>The OpenCATS installer needs some information about your MySQL database to continue the installation.
                            If you do not know this information, then please contact your website host or administrator.
                            Please note that this is probably NOT the same as your FTP login information!</p>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="dbname">Database Name: <span class="text-danger">*</span></label>
                                <section class="col-sm-8">
                                    <input class="form-control" type=text size=20 id="dbname" value="" />
                                </section>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="dbuser">Database User: <span class="text-danger">*</span></label>
                                <section class="col-sm-8">
                                    <input class="form-control" type=text size=20 id="dbuser" value="" />
                                </section>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="dbpass">Database Password:</label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="password" size="20" id="dbpass" value="" />
                                </section>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="dbhost">Database Host: <span class="text-danger">*</span></label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="text" size="20" id="dbhost" value="localhost" /> (usually <i>localhost</i>)
                                </section>
                            </section>
                            <input type="button" class="btn btn-primary" id="testDatabaseConnectivity" value="Test Database Connectivity" onclick="Installpage_populate('a=databaseConnectivity&amp;user='+escape(document.getElementById('dbuser').value)+'&amp;pass='+escape(document.getElementById('dbpass').value)+'&amp;host='+escape(document.getElementById('dbhost').value)+'&amp;name='+escape(document.getElementById('dbname').value));" />
                            <img src="images/indicator.gif" id="testDatabaseConnectivityIndicator" alt="Testing database connection" class="ms-1" style="visibility: hidden;" height="16" width="16" />
                        </div>
                        <div id="resumeParsing" style="display: none;">
                            <h1 class="h5 mb-3">Resume Indexing Configuration</h1>
                            <p>OpenCATS can index resumes for advanced searching with the assistance of external
                            document processing software. You need to configure the software below
                            to enable resume indexing.</p>
                            <a href="http://www.catsone.com/resumeIndexingSoftware.php?os=<?php echo(urlencode(PHP_OS)); ?>&amp;server_software=<?php echo(urlencode($_SERVER['SERVER_SOFTWARE'])); ?>" class="d-block mb-3" target="resumeParsingSoftwareDownload">
                            Where can I get this software?
                            </a>
                            <section class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="docEnabled" checked onclick="if (this.checked) { document.getElementById('docExecutable').disabled = false; document.getElementById('docExecutable').value = document.getElementById('docExecutableOrg').value; } else { document.getElementById('docExecutable').disabled = true; document.getElementById('docExecutable').value = ''; }">
                                <label class="form-check-label" for="docEnabled"><img src="images/file/doc.gif" alt="" />&nbsp;&nbsp;.doc file (Microsoft Word Document)</label>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="docExecutable">Path to Antiword Executable:</label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="text" name="docExecutable" id="docExecutable" />
                                    <input type="hidden" name="docExecutableOrg" id="docExecutableOrg" />
                                </section>
                            </section>
                            <section class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="pdfEnabled" checked onclick="if (this.checked) { document.getElementById('pdfExecutable').disabled = false; document.getElementById('pdfExecutable').value = document.getElementById('pdfExecutableOrg').value; } else { document.getElementById('pdfExecutable').disabled = true; document.getElementById('pdfExecutable').value = ''; }">
                                <label class="form-check-label" for="pdfEnabled"><img src="images/file/pdf.gif" alt="" />&nbsp;&nbsp;.pdf file (Adobe Acrobat Document)</label>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="pdfExecutable">Path to PDFToText Executable:</label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="text" name="pdfExecutable" id="pdfExecutable" />
                                    <input type="hidden" name="pdfExecutableOrg" id="pdfExecutableOrg" />
                                </section>
                            </section>
                            <section class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="htmlEnabled" checked onclick="if (this.checked) { document.getElementById('htmlExecutable').disabled = false; document.getElementById('htmlExecutable').value = document.getElementById('htmlExecutableOrg').value; } else { document.getElementById('htmlExecutable').disabled = true; document.getElementById('htmlExecutable').value = ''; }">
                                <label class="form-check-label" for="htmlEnabled"><img src="images/file/txt.gif" alt="" />&nbsp;&nbsp;.html file (Hypertext Markup Document)</label>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="htmlExecutable">Path to Html2Text Executable:</label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="text" name="htmlExecutable" id="htmlExecutable" />
                                    <input type="hidden" name="htmlExecutableOrg" id="htmlExecutableOrg" />
                                </section>
                            </section>
                            <section class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="rtfEnabled" checked onclick="if (this.checked) { document.getElementById('rtfExecutable').disabled = false; document.getElementById('rtfExecutable').value = document.getElementById('rtfExecutableOrg').value; } else { document.getElementById('rtfExecutable').disabled = true; document.getElementById('rtfExecutable').value = ''; }">
                                <label class="form-check-label" for="rtfEnabled"><img src="images/file/rtf.gif" alt="" />&nbsp;&nbsp;.rtf file (Rich Text Document)</label>
                            </section>
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="rtfExecutable">Path to UnRTF Executable:</label>
                                <section class="col-sm-8">
                                    <input class="form-control" type="text" name="rtfExecutable" id="rtfExecutable" />
                                    <input type="hidden" name="rtfExecutableOrg" id="rtfExecutableOrg" />
                                </section>
                            </section>
                            <input type="button" class="btn btn-primary" value="Test Configuration" onclick="Installpage_populate('a=testResumeParsing&amp;docExecutable='+escape(document.getElementById('docExecutable').value)+'&amp;pdfExecutable='+escape(document.getElementById('pdfExecutable').value)+'&amp;htmlExecutable='+escape(document.getElementById('htmlExecutable').value)+'&amp;rtfExecutable='+escape(document.getElementById('rtfExecutable').value));" />&nbsp;&nbsp;&nbsp;
                            <input type="button" class="btn btn-outline-secondary" value="Skip this Step" onclick="document.getElementById('resumeParsing').style.display='none';showTextBlock('mailSettings');Installpage_populate('a=mailSettings');">
                        </div>
                        <div id="mailSettings" style="display: none;">
                            <h1 class="h5 mb-3">Mail Settings</h1>
                            Please enter your e-mail address (for where OpenCATS e-mails should be replied to, etc.).
                            <section class="row g-2 mb-3">
                                <label class="col-sm-4 col-form-label" for="mailFromAddress"><span id="mailFromAddressLabel">E-mail:</span> <span class="text-danger">*</span></label>
                                <section class="col-sm-8">
                                    <input class="form-control" type=text size=35 id="mailFromAddress" value="" />
                                </section>
                            </section>
                            <p>OpenCATS sends automatic E-Mails on different events.  Please choose the mechanism for E-Mail delivery via OpenCATS.</p>
                            <form name="mailForm">
                                <section class="row g-2 mb-3">
                                    <label class="col-sm-4 col-form-label" for="mailSupport">Mail Support: <span class="text-danger">*</span></label>
                                    <section class="col-sm-8">
                                        <select id="mailSupport" name="mailSupport" onChange="changeMailForm();" class="form-select">
                                            <option value="opt0">None</option>
                                            <option value="opt1">PHP Built-In Mail Support (recommended)</option>
                                            <option value="opt2">Sendmail</option>
                                            <option value="opt3">SMTP</option>
                                            <option value="opt4">SMTP w/Authorization</option>
                                        </select>
                                    </section>
                                </section>
                                <div id="mailSendmailBox" style="display: none;">
                                    <section class="row g-2 mb-3">
                                        <label class="col-sm-4 col-form-label" for="mailSendmail">Sendmail Location: <span class="text-danger">*</span></label>
                                        <section class="col-sm-8">
                                            <input class="form-control" type=text size=20 id="mailSendmail" value="" />
                                        </section>
                                    </section>
                                </div>
                                <div id="mailSmtpBox" style="display: none;">
                                    <section class="row g-2 mb-3">
                                        <label class="col-sm-4 col-form-label" for="mailSmtpHost">SMTP Host: <span class="text-danger">*</span></label>
                                        <section class="col-sm-8">
                                            <input class="form-control" type=text size=20 id="mailSmtpHost" value="" />
                                        </section>
                                    </section>
                                    <section class="row g-2 mb-3">
                                        <label class="col-sm-4 col-form-label" for="mailSmtpPort">SMTP Port: <span class="text-danger">*</span></label>
                                        <section class="col-sm-8">
                                            <input class="form-control" type=text size=20 id="mailSmtpPort" value="" />
                                        </section>
                                    </section>
                                </div>
                                <div id="mailSmtpAuthorizationBox" style="display: none;">
                                    <section class="row g-2 mb-3">
                                        <label class="col-sm-4 col-form-label" for="mailSmtpUsername">SMTP Username: <span class="text-danger">*</span></label>
                                        <section class="col-sm-8">
                                            <input class="form-control" type=text size=20 id="mailSmtpUsername" value="" />
                                        </section>
                                    </section>
                                    <section class="row g-2 mb-3">
                                        <label class="col-sm-4 col-form-label" for="mailSmtpPassword">SMTP Password: <span class="text-danger">*</span></label>
                                        <section class="col-sm-8">
                                            <input class="form-control" type=text size=20 id="mailSmtpPassword" value="" />
                                        </section>
                                    </section>
                                </div>
                            </form>
                            <br />
                            <input type="button" class="btn btn-primary" id="setMailSettings" value="Next -->" onclick="if (document.getElementById('mailFromAddress').value=='') alert('Please enter a reply E-Mail Address.'); else {Installpage_populate('a=setMailSettings&amp;mailSupport='+escape(document.getElementById('mailSupport').value)+'&amp;mailSendmail='+escape(document.getElementById('mailSendmail').value)+'&amp;mailSmtpHost='+escape(document.getElementById('mailSmtpHost').value)+'&amp;mailSmtpPort='+escape(document.getElementById('mailSmtpPort').value)+'&amp;mailSmtpUsername='+escape(document.getElementById('mailSmtpUsername').value)+'&amp;mailSmtpPassword='+escape(document.getElementById('mailSmtpPassword').value)+'&amp;mailFromAddress='+escape(document.getElementById('mailFromAddress').value));}" />
                        </div>
                        <div class="alert alert-info" role="status" id="detectingOptional" style="display: none;">
                            <h1 class="h5 mb-3">Loading Extras - Detecting Installed Components</h1>
                            <p>Please wait while the installer checks what components you have installed...</p>
                            <img src="images/indicator.gif" alt="" />
                        </div>
                        <div class="alert alert-info" role="status" id="installingComponents" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Installing</h1>
                            <p>Please wait while the selected components are installed...</p>
                            <img src="images/indicator.gif" alt="" />
                        </div>
                        <div class="alert alert-info" role="status" id="installingComponentsExtra" style="display: none;">
                            <h1 class="h5 mb-3">Loading Extras - Installing</h1>
                            <p>Please wait while the selected components are installed...</p>
                            <img src="images/indicator.gif" alt="" />
                        </div>
                        <div class="alert alert-info" role="status" id="installingComponentsMaint" style="display: none;">
                            <h1 class="h5 mb-3">Performing Maintenance</h1>
                            <p>Please wait whilst the OpenCATS database is brought up to date...</p>
                            <span id="upToDateModuleName">
                            </span>
                            <div id="d3" class="progress" role="progressbar" aria-label="Database upgrade" aria-valuemin="0" aria-valuemax="100">
                                <div id="d2" class="progress-bar" style="width: 0%;">
                                    <div id="d1">
                                    </div>
                                </div>
                            </div>
                            <span id="upToDateSqlQueryLabel" style="display:none;">SQL Query Being Executed:</span><br />
                            <div id="upToDateSqlQuery" class="installer-query border rounded p-2 bg-body">
                            </div>
                            <img src="images/indicator.gif" alt="" />
                        </div>
                        <div class="alert alert-info" role="status" id="installingComponentsMaintResume" style="display: none;">
                            <h1 class="h5 mb-3">Performing Maintenance</h1>
                            <p>Please wait while your unindexed resumes are reindexed...</p>
                            <img src="images/indicator.gif" alt="" />
                        </div>
                        <div id="emptyDatabase" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Empty Database</h1>
                            <p>The installer is ready to set up your OpenCATS data. Please pick the way you want the installer to set up OpenCATS:</p>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroup" id="emptyCheckBox" value="empty" checked>&nbsp;New Installation (Recommended)</label>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroup" value="demo">&nbsp;Demonstration Installation</label>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroup" value="restore">&nbsp;Restore Installation from Backup</label>
                            <p>You can always run the installer again to clear the database and choose a different option.</p>
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="Installpage_populate('a=selectDBType&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroup'))));" />
                        </div>
                        <div id="unknownDataInDatabase" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Unknown Data in the Database</h1>
                            <p>The installer has scanned the database and found unknown tables in the database. This could indicate that another application has been
                            installed in the database beforehand.</p>
                            <a href="javascript:void(0);" onclick="document.getElementById('tableNamesUnknown').style.display = ''" class="fw-semibold">View a list of tables in the database</a>
                            <br />
                            <span id="tableNamesUnknown" style="font-weight: bold; display: none;"></span>
                            <br />
                            <p>Please remove all of the-non OpenCATS tables to continue.</p>
                            <input type="button" class="btn btn-primary" value="Remove Non-OpenCATS Tables" onclick="Installpage_populate('a=resetDatabase');">&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" class="btn btn-primary" value="Do Nothing and Retry Installation" onclick="Installpage_populate('a=startInstall');">
                        </div>
                        <div id="catsUpToDate" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Existing Database</h1>
                            The installer has detected an existing installation of OpenCATS.<br />
                            <p>How would you like to proceed?</p>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroupexists" id="currentCheckBox" value="current" checked />&nbsp;Use existing OpenCATS installation and automatically perform any necessary upgrade (recommended).</label>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroupexists" value="empty" />&nbsp;Delete existing data and create a new installation.</label>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroupexists" value="demo" />&nbsp;Delete existing data and install the OpenCATS demonstration database.</label>
                            <label class="d-block mb-2"><input class="form-check-input" type="radio" name="installgroupexists" value="restore" />&nbsp;Delete existing data and restore a previous OpenCATS installation from backup.</label>
                            If you choose to use the existing OpenCATS installation, you can always run<br />
                            the installer again later and choose a different option.
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="if (getCheckedValue(document.getElementsByName('installgroupexists')) == 'current') Installpage_upgradeExisting(); else {document.getElementById('catsUpToDate').style.display='none';showTextBlock('queryResetDatabase');}" />
                        </div>
                        <div id="queryInstallBackup" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Restore from Backup</h1>
                            <p>The installer is ready to restore your backup.</p>
                            Please upload the file catsbackup.bak into the <b>restore</b> directory. The installer will load the data
                            out of the catsbackup.bak and set up your site accordingly, and then it will delete the file (preventing
                            unauthorized access to the backup file).
                            <label class="d-block mb-2"><input class="form-check-input" type="checkbox" id="continueRestoreCheck" onclick="if (this.checked) document.getElementById('continueRestoreButton').style.display = ''; else document.getElementById('continueRestoreButton').style.display = 'none';"> I have uploaded the file catsbackup.bak into the restore directory.</label>
                            <input type="button" class="btn btn-primary" value="Continue" id="continueRestoreButton" style="display: none;" onclick="document.getElementById('queryInstallBackup').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=restoreFromBackup');" />&nbsp;&nbsp;&nbsp;
                            <input type="button" class="btn btn-outline-secondary" value="Cancel" onclick="Installpage_populate('a=detectRevision');" />
                        </div>
                        <div id="queryInstallDemo" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Demo</h1>
                            <p>The installer is about to install a demonstration database for the fictitious company 'MyCompany.NET'.</p>
                            <p>The database will be pre-populated with test data that you can use to train new users on using OpenCATS. You can login using
                            username: john@mycompany.net, password john99 (or by clicking Login to demo on the login screen).</p>
                            <p>To clear the demo data and start a production system, run the installer again.</p>
                            <input type="button" class="btn btn-primary" value="Continue" onclick="document.getElementById('queryInstallDemo').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=onLoadDemoData');" />&nbsp;&nbsp;&nbsp;
                            <input type="button" class="btn btn-outline-secondary" value="Cancel" onclick="Installpage_populate('a=detectRevision');" />
                        </div>
                        <div class="alert alert-success" id="installCompleteProd" style="display: none;">
                            <h1 class="h5 mb-3">Finishing Installation</h1>
                            <p>The installer has finished installing OpenCATS! The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.</p>
                            <p>You may now login to OpenCATS. If it is a new installation, use the following logon information:</p>
                            Username: admin<br />
                            <p>Password: cats</p>
                            <p>You will be required to change this password when you first log in.</p>
                            <input type="button" class="btn btn-primary" value="Start OpenCATS" onclick="window.location.href='index.php';" />
                        </div>
                        <div class="alert alert-success" id="installCompleteDemo" style="display: none;">
                            <h1 class="h5 mb-3">Finishing Installation - Demo</h1>
                            <p>The installer has finished installing OpenCATS! The installer has been disabled to prevent unauthorized access. To run the installer again, delete the file 'INSTALL_BLOCK' in your OpenCATS directory.</p>
                            <p>You may now login to OpenCATS. To login, either click "Login to Demo Account" on the logon screen or use the following logon information:</p>
                            Username: john@mycompany.net<br />
                            <p>Password: john99</p>
                            <input type="button" class="btn btn-primary" value="Start OpenCATS" onclick="window.location.href='index.php';" />
                        </div>
                        <div class="alert alert-danger" id="queryResetDatabase" style="display: none;">
                            <h1 class="h5 mb-3">Loading Data - Existing Database</h1>
                            <p>Warning: This option will delete ALL of the data in your current database. Are you sure you want to proceed?</p>
                            <p>THIS ACTION CANNOT BE UNDONE! Make sure you have backed up and saved all your data before you do this!</p>
                            <input type="button" class="btn btn-danger" value="Yes - Delete All Data" onclick="document.getElementById('queryResetDatabase').style.display='none';showTextBlock('installingComponents');Installpage_populate('a=resetDatabase&amp;type='+escape(getCheckedValue(document.getElementsByName('installgroupexists'))));" />&nbsp;&nbsp;&nbsp;&nbsp;
                            <input type="button" class="btn btn-primary" value="No - Do Not Delete Data" onclick="Installpage_populate('a=detectRevision');" />
                        </div>
                        <div id="pickOptionalComponents" style="display:none;">
                            <h1 class="h5 mb-3">Loading Extras - Localization</h1>
                            <section class="mb-3"><label for="timeZone" class="form-label">Please choose your time zone.</label>
                                <?php if ($isSupportedPHPVersion) TemplateUtility::printTimeZoneSelect('timeZone', '', 'form-select', OFFSET_GMT); ?></section>
                            <section class="mb-3"><label for="dateFormat" class="form-label">Please choose your preferred date format.</label>
                                <select id="dateFormat" name="dateFormat" class="form-select">
                                    <option value="mdy" selected="selected">MM-DD-YYYY (US)</option>
                                    <option value="dmy">DD-MM-YYYY (UK)</option>
                                </select></section>
                            <section class="mb-3"><label for="timeFormat" class="form-label">Please choose your preferred time format.</label>
                                <select id="timeFormat" name="timeFormat" class="form-select">
                                    <option value="12" selected="selected">12-hour (1:30 PM)</option>
                                    <option value="24">24-hour (13:30)</option>
                                </select></section>
                            <section class="mb-3"><label for="defaultPhoneCountryCodeDigits" class="form-label">Please enter your default phone country calling code.</label>
                                <span>+</span>
                                <input class="form-control" type="text" name="defaultPhoneCountryCodeDigits" id="defaultPhoneCountryCodeDigits" value="" size="5" maxlength="5" oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                />
                            </section>
                            <h1 class="h5 mb-3">Loading Extras - Choose Extras</h1>
                            <p>OpenCATS comes with some optional features. You can enable them below.</p>
                            <p>To add or remove features in the future, run the installer again.</p>
                            <section id="extrasList" class="table-responsive"></section>
                        </div>
                        <section id="subFormBlock" class="mt-3" aria-live="polite"></section>
                        <div id="testPassed" style="display: none;">
                            <section class="alert alert-success" role="status">All tests passed successfully!</section>
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="Installpage_populate('a=databaseConnectivity');" />
                        </div>
                        <div id="testPassedParsing" style="display: none;">
                            <section class="alert alert-success" role="status">All tests passed successfully!</section>
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="document.getElementById('resumeParsing').style.display='none';document.getElementById('subFormBlock').style.display='none';document.getElementById('testPassedParsing').style.display='none';showTextBlock('mailSettings'); Installpage_populate('a=mailSettings');" />
                        </div>
                        <div id="testWarning" style="display: none;">
                            <section class="alert alert-warning" role="status">One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.
                                If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</section>
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="Installpage_populate('a=databaseConnectivity');" />
                        </div>
                        <div id="testFailed" style="display: none;">
                            <section class="alert alert-danger" role="status">One or more tests failed. Please correct the errors and try again.
                                If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</section>
                            <input type="button" class="btn btn-primary" value="Retry Installation" onclick="Installpage_populate('a=startInstall');" />
                        </div>
                        <div id="testFailedWarning" style="display: none;">
                            <section class="alert alert-warning" role="status">One or more tests issued a warning. You may still proceed, but read the warnings carefully and address them if you can.
                                If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</section>
                            <section class="alert alert-danger" role="status">One or more tests failed. Please correct the errors and try again.
                                If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</section>
                            <input type="button" class="btn btn-primary" value="Retry Installation" onclick="Installpage_populate('a=startInstall');" />
                        </div>
                        <div id="MySQLTestPassed" style="display: none;">
                            <section class="alert alert-success" role="status">All tests passed successfully!</section>
                            <input type="button" class="btn btn-primary" value="Next -->" onclick="Installpage_populate('a=detectRevision');" />
                        </div>
                        <div id="MySQLTestFailed" style="display: none;">
                            <section class="alert alert-danger" role="status">One or more tests failed. Please correct the errors and try again.
                                If you have any questions, visit the OpenCATS forums at <a href="http://www.opencats.org/forums/">http://www.opencats.org/forums/</a>.</section>
                        </div>
                        <div id="execute" style="display: none;"><!--This is so we can execute incoming JS.--></div>
                    </section>

                            <script type="text/javascript">
                                <?php if ($isSupportedPHPVersion): ?>
                                    Installpage_populate('a=startInstall');
                                <?php else: ?>
                                    setActiveStep(1);
                                    showTextBlock("phpVersion");
                                    showTextBlock("testFailed");
                                <?php endif; ?>
                            </script>
                </section>
            </div>
        </main>
    </body>
</html>
