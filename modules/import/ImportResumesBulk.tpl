<?php /* $Id: ImportResumesBulk.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, ''); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>
        
        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Import" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Import Data</h2></td>
                </tr>
            </table>

            <p class="note" id="importHide2">Import Data - Step 2</p>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0">Error!</p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0">Success</p>

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
                    <td>CATS may discard or fail to read some of the submitted data which it does not
                    understand how to use. Do not discard the original data!
                    </td>
                </tr>
                
            </table>
            
            <br />
            
            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=importUploadResume" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <table class="searchTable" width="100%" id="importHide3">
                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file">Import:</label>
                        </td>
                        <td class="tdData">
                            <img src="images/file/doc.gif">&nbsp;Resume&nbsp;<a href="javascript:void(0);" onclick="showPopWin('index.php?m=import&a=whatIsBulkResumes', 420, 275, null);">(How do I use bulk resumes?)</a><br />
                            <span style="font-style: italic;">This will not create candidates, it will only add resumes to the <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&a=search">resume search</a>!</span>
                        </td>
                    </tr>
                    
                    <tr id="importSingle">
                        <td class="tdVertical">
                            <label id="fileLabel" for="file">
                                <br />
                                Files:
                            </label>
                        </td>
                        <td class="tdData">
                            <br />
                            <?php if (count($this->foundFiles) == 0): ?>
                                CATS has not found any files to import in /upload/.<br />
                                <br />
                            <?php else: ?>
                                <span id="foundFilesSpan">
                                    CATS has found <?php echo(count($this->foundFiles)); ?> files to import.<br />
                                    <br />
                                </span>
                            <?php endif; ?>
                            <input class="button" type="button" value="Back" id="back" onclick="document.location.href='?m=import';">&nbsp;
                            <span id="nextScreenButton" style="display:none; font-weight:bold;">
                            </span>
                            <?php if (count($this->foundFiles) != 0): ?>
                                <input type="button" class="button" id="startImport" value="Start import" onclick="startMassImport();" />
                                <span id="pleaseWaitImport" style="display:none;">
                                    <br /><br/>
                                    Please wait, importing resumes...
                                    <span id="progressBar">
                                        <br /><br />
                                        <div id="empty" style="background-color:#eeeeee;border:1px solid black;height:20px;width:300px;padding:0px;" align="left">
                                            <div id="d2" style="position:relative;top:0px;left:0px;background-color:#2244ff;height:20px;width:0px;padding-top:5px;padding:0px;">
                                                <div id="d1" style="position:relative;top:0px;left:0px;color:#ffffff;height:20px;text-align:center;font:bold;padding:0px;padding-top:1px;">
                                                </div>
                                            </div>
                                        </div>
                                    </span>
                                    <br />
                                    <br />
                                    Processing resume <span id="processingResumeNumber"></span> / <?php echo(count($this->foundFiles)); ?>...<br /><br />
                                    <input class="button" type="button" value="Abort" id="back" onclick="abortImport=true;">&nbsp;
                                </span>
                            <?php endif; ?>
                            <br />
                            <br />
                        </td>
                    </tr>
    
                </table>    
            </form>
        </div>
    </div>
    
    <script type="text/javascript">
        initPopUp();
        
        totalFiles = <?php echo(count($this->foundFiles)); ?>;
    </script>
    
<?php TemplateUtility::printFooter(); ?>
