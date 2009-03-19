<?php /* $Id: ImportResumes.tpl 3584 2007-11-12 23:20:53Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Import'), array('modules/import/import.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Import" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Import Data') ?></h2></td>
                </tr>
            </table>

            <p class="note" id="importHide2"><?php _e('Import Data') ?> - <?php _e('Step') ?> 2</p>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0"><?php _e('Error') ?>!</p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0"><?php _e('Success') ?></p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

           <?php endif; ?>
            <table class="searchTable" id="importTable1" width="100%">
                <tr>
                    <td><?php _e('OSATS may discard or fail to read some of the submitted data which it does not understand how to use. Do not discard the original data') ?>!
                    </td>
                </tr>

            </table>

            <br />

            <form name="importDataForm" id="importDataForm" action="<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=importUploadResume" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <table class="searchTable" width="100%" id="importHide3">
                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file"><?php _e('Import') ?>:</label>
                        </td>
                        <td class="tdData">
                            <img src="images/file/doc.gif">&nbsp;<?php _e('Resume') ?>&nbsp;<a href="javascript:void(0);" onclick="showPopWin('index.php?m=import&a=whatIsBulkResumes', 420, 275, null);">(<?php _e('How do I use bulk resumes?') ?>)</a>
                        </td>
                    </tr>

                    <tr id="importMultiple">
                        <td class="tdVertical">
                            <label id="fileLabel" for="file">
                                <br />
                                <?php _e('Multiple File Import') ?>:
                            </label>
                        </td>
                        <td class="tdData">
                            <?php if($this->allowAspFlashUploader == true): ?>
                                <br />
                                <?php _e('Step') ?> 1:<br />
                                <?php _e('Upload resumes you wish to parse to the OSATS server.') ?><br />
								<?php _e('OSATS can parse doc, pdf, txt, and rtf format resumes.') ?><br />
								<?php _e('Zip format archives of resumes are alos accepted.') ?>
                                <OBJECT id="FlashFilesUpload" codeBase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0"
                                		width="450" height="350" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" VIEWASTEXT>
                                		<!-- Replace symbols " with the &quot; at all parameters values and
                                		symbols "&" with the "%26" at URL values or &amp; at other values!
                                		The same parameters values should be set for EMBED object below. -->
                                	<PARAM NAME="FlashVars" VALUE="uploadUrl=<?php echo(osatutil::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/upload.php?session_id=<?php echo(session_id()); ?>&showLink=false&labelUploadText=%20 &backgroundColor=#FFFFFF">
                                	<PARAM NAME="BGColor" VALUE="#FFFFFF">
                                	<PARAM NAME="Movie" VALUE="modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf">
                                	<PARAM NAME="Src" VALUE="modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf">
                                	<PARAM NAME="WMode" VALUE="Window">
                                	<PARAM NAME="Play" VALUE="-1">
                                	<PARAM NAME="Loop" VALUE="-1">
                                	<PARAM NAME="Quality" VALUE="High">
                                	<PARAM NAME="SAlign" VALUE="">
                                	<PARAM NAME="Menu" VALUE="-1">
                                	<PARAM NAME="Base" VALUE="">
                                	<PARAM NAME="AllowScriptAccess" VALUE="always">
                                	<PARAM NAME="Scale" VALUE="ShowAll">
                                	<PARAM NAME="DeviceFont" VALUE="0">
                                	<PARAM NAME="EmbedMovie" VALUE="0">
                                	<PARAM NAME="SWRemote" VALUE="">
                                	<PARAM NAME="MovieData" VALUE="">
                                	<PARAM NAME="SeamlessTabbing" VALUE="1">
                                	<PARAM NAME="Profile" VALUE="0">
                                	<PARAM NAME="ProfileAddress" VALUE="">
                                	<PARAM NAME="ProfilePort" VALUE="0">

                                	<!-- Embed for Netscape,Mozilla/FireFox browsers support. Flashvars parameters are the same.-->
                                		<!-- Replace symbols " with the &quot; at all parameters values and
                                		symbols "&" with the "%26" at URL values or &amp; at other values! -->
                                	<embed bgcolor="#FFFFFF" id="EmbedFlashFilesUpload" src="<?php echo(osatutil::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/ElementITMultiPowUpload1.7.swf" quality="high" pluginspage="http://www.macromedia.com/shockwave/download/index.cgi?P1_Prod_Version=ShockwaveFlash"	type="application/x-shockwave-flash" width="450" height="350"
                                	flashvars="uploadUrl=<?php echo(osatutil::getNonSSLDirectoryURL()); ?>modules/asp/lib/MultiPowUpload/upload.php?session_id=<?php echo(session_id()); ?>&showLink=false&labelUploadText=%20 &backgroundColor=#FFFFFF">
                                	</embed>
                                  </OBJECT>
                                  <br />
                                  <?php _e('When you are finishing uploading your resumes, press the below button to continue.') ?><br />
                                <input type="button" class="button" value="<?php _e('Step') ?> 2: <?php _e('Parse Resumes') ?>" onclick="document.location.href='?m=import&a=showMassImport';" />
                            <?php elseif($this->allowMultipleFiles == true): ?>
                                <br />
                                <?php _e('To import multiple files, add the files you would like to import to the \'upload\' directory on your OSATS web server, and press the button below to have OSATS scan for uploaded documents. If you need assistance in uploading files to your web server, contact your system administrator.') ?><br />
                                <br />
                                <input type="button" class="button" value="<?php _e('Scan /upload/ folder for resumes') ?>" onclick="document.location.href='?m=import&a=showMassImport';" />
                            <?php else: ?>
                                <br />
                                <?php _e('The automated bulk resume import feature has been temporarily disabled.') ?><br /><br />
                                <?php _e('To import resumes into the bulk resume pool, please contact %s for assistance from the OSATS team.', '<a href="mailto:support@OSATSone.com">support@OSATSone.com</a>') ?>
                                <br />
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    <?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>
    <script type="text/javascript">
        initPopUp();
    </script>

