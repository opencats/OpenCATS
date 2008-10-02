<div class="stepContainer">

<?php if (isset($this->documents)): ?>
<div id="uploadQueue" style="background-color: #f0f0f0; color: #800000; border: 1px solid #800000; text-align: center; font-size: 14px; padding: 10px; margin: 0 0 15px 0; font-weight: bold;">
You have <?php echo number_format(count($this->documents), 0); ?> document<?php echo count($this->documents) != 1 ? 's' : ''; ?> in your upload queue.
<br /><br />
<input type="button" value="Delete File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="deleteUploadFiles();" class="button" />
<input type="button" class="button" value="Import File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=import&a=massImport&step=2';" />
</div>
<?php endif; ?>

<?php if ($this->isDemo): ?>
    <img src="modules/asp/website/images/demoImport.jpg" border="0" />
<?php elseif ($this->flashUploaderEnabled): ?>
    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="left" valign="top" width="50%">
                <img src="images/massImport.jpg" border="0" />

                <p />
                <b>Supported File Types</b>:
                <table cellpadding="2" cellspacing="0" border="0" style="padding-left: 80px;">
                    <tr><td><img src="images/fileTypeDoc.jpg" border="0" /> Microsoft Word Documents (.doc)</td></tr>
                    <tr><td><img src="images/fileTypePdf.jpg" border="0" /> Adobe Portable Document Format (.pdf)</td></tr>
                    <tr><td><img src="images/fileTypeRtf.jpg" border="0" /> Rich Text Format (.rtf)</td></tr>
                    <tr><td><img src="images/fileTypeHtml.jpg" border="0" /> HTML Web Pages (.html)</td></tr>
                    <tr><td><img src="images/fileTypeTxt.jpg" border="0" /> Plain Text Files (.txt)</td></tr>
                </table>
                <br />
                <b>Supported File Archives</b>:
                <table cellpadding="2" cellspacing="0" border="0" style="padding-left: 80px; padding-top: 10px;">
                    <tr><td><img src="images/fileTypeZip.jpg" border="0" /> Zip Archives (.zip)</td></tr>
                    <tr><td><img src="images/fileTypeGz.jpg" border="0" /> GNU Zip Archives (.gz)</td></tr>
                </table>
            </td>

            <td align="left valign="top" width="50%">
                <object id="FlashFilesUpload" codeBase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
            		width="450" height="350" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" VIEWASTEXT>
            		<!-- Replace symbols " with the &quot; at all parameters values and
            		symbols "&" with the "%26" at URL values or &amp; at other values!
            		The same parameters values should be set for EMBED object below. -->
               	    <param name="FlashVars" VALUE="uploadUrl=<?php echo(CATSUtility::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/upload.php?session_id=<?php echo(session_id()); ?>&amp;showLink=false&amp;labelUploadText=%20&amp;backgroundColor=#FFFFFF">
                   	<param name="BGColor" VALUE="#FFFFFF">
                   	<param name="Movie" VALUE="<?php echo(CATSUtility::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf">
                   	<param name="Src" VALUE="<?php echo(CATSUtility::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf">
                   	<param name="WMode" VALUE="Window">
                   	<param name="Play" VALUE="-1">
                   	<param name="Loop" VALUE="-1">
                   	<param name="Quality" VALUE="High">
                   	<param name="SAlign" VALUE="">
                   	<param name="Menu" VALUE="-1">
                   	<param name="Base" VALUE="">
                   	<param name="AllowScriptAccess" VALUE="always">
                   	<param name="Scale" VALUE="ShowAll">
                   	<param name="DeviceFont" VALUE="0">
                   	<param name="EmbedMovie" VALUE="0">
                   	<param name="SWRemote" VALUE="">
                   	<param name="MovieData" VALUE="">
                   	<param name="SeamlessTabbing" VALUE="1">
                   	<param name="Profile" VALUE="0">
                   	<param name="ProfileAddress" VALUE="">
                   	<param name="ProfilePort" VALUE="0">
                	<!-- Embed for Netscape,Mozilla/FireFox browsers support. Flashvars parameters are the same.-->
                    <!-- Replace symbols " with the &quot; at all parameters values and
                    symbols "&" with the "%26" at URL values or &amp; at other values! -->
                	<embed bgcolor="#FFFFFF" id="EmbedFlashFilesUpload" src="<?php echo(CATSUtility::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash" type="application/x-shockwave-flash" width="450" height="350"
                    flashvars="uploadUrl=<?php echo(CATSUtility::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/upload.php?session_id=<?php echo(session_id()); ?>&amp;showLink=false&amp;labelUploadText=%20&amp;backgroundColor=#FFFFFF">
                	</embed>
                </object>
            </td>
        </tr>
    </table>

    <div style="text-align: right;">
    <input type="button" class="button" value="Continue ->" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=import&a=massImport&step=2';" />
    </div>


<?php elseif ($this->multipleFilesEnabled): ?>
    <span style="font-size: 16px;">
    <?php if ($this->uploadPath !== false): ?>
        To import multiple files, move or copy your resume documents to the following directory on the computer
        that hosts CATS:
        <br /><br />

        <b><?php echo $this->uploadPath; ?></b>

        <br /><br />
        Once you have resumes in this folder, <a href="<?php echo CATSUtility::getIndexName(); ?>?m=import&a=importSelectType&typeOfImport=resume">
        reload</a> this page to start the import process.

        <br /><br />

        <?php if (LicenseUtility::isProfessional()): ?>
        If you need any assistance, please contact the CATS support team.</br >
        <?php else: ?>
        If you need assistance in uploading files to your web server, contact your system administrator.<br />
        <?php endif; ?>
    <?php else: ?>
        In order to import resume documents into CATS, you need to create a directory named "<b>upload</b>" on the computer
        that hosts cats. This directory needs to have its permissions set to allow files to be created by your
        web server.
        <br /><br />
        <b>Linux Instructions:</b>
        <br />
        <blockquote>
        <span style="color: #c0c0c0;">&gt;</span> mkdir /PATH/TO/CATS/upload<br />
        <span style="color: #c0c0c0;">&gt;</span> chmod -R 777 /PATH/TO/CATS/upload
        </blockquote>
        <br />
        <b>Windows Instructions:</b>
        <blockquote>
        <span style="color: #c0c0c0;">&gt;</span> Create a folder named <b>upload</b> in the directory you installed CATS.<br />
        <span style="color: #c0c0c0;">&gt;</span> Set the appropriate permissions by right clicking the file and selecting <b>Properties</b>, then <b>Security</b>.<br />
        <span style="color: #c0c0c0;">&gt;</span> Make sure all users have access to read, write and delete files and directories.
        </blockquote>
    <?php endif; ?>
    </span>


<?php else: ?>
    The automated bulk resume import feature has been temporarily disabled.<br /><br />
    To import resumes into the bulk resume pool, please contact <a href="mailto:support@catsone.com">support@catsone.com</a>
    for assistance from the CATS team.
    <br />


<?php endif; ?>

<?php if (LicenseUtility::isParsingEnabled()): ?>
<div style="padding: 10px; margin-top: 15px; text-align: left;">
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="left" valign="top" style="padding-right: 20px;">
                <a href="http://www.resfly.com" target="_blank">
                    <img src="images/poweredByResfly.jpg" border="0" style="border: 1px solid #c0c0c0;" />
                </a>
            </td>
            <td align="left" valign="top">
                <span style="font-size: 16px;">
                <?php if (LicenseUtility::isProfessional()): ?>
                You are a registered CATS Professional user <b><?php echo LicenseUtility::getName(); ?></b>.
                <?php elseif (LicenseUtility::isOpenSource()): ?>
                <b>You are a registered open source user of CATS.</b>
                <?php endif; ?>
                </span>

                <p />
                <?php if (file_exists('modules/asp') || (is_array($status = LicenseUtility::getParsingStatus()) && $status['parseLimit'] == -1)): ?>
                    <span style="font-size: 14px; color: #333333;">
                    You have unlimited use of the Resfly parsing service, which searches your resume files for contact
                    and resume information. CATS will import all applicable resume documents as candidates.
                    </span>
                <?php else: ?>
                    <span style="font-size: 14px; color: #333333;">
                    Your resume documents will be imported as searchable documents but <b>not</b> as candidates unless
                    you manually complete the required fields for each document (first and last names).
                    <br /><br />
                    With the
                    Resfly parsing service, much of the candidate's information can be imported automatically.
                    <br />
                    Consider <a href="http://www.catsone.com/?a=getcats" style="font-size: 14px;" target="_blank">upgrading to CATS Professional</a>
                    for unlimited use of this service.
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>

</div>
