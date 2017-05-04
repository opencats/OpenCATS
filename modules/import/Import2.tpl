<?php /* $Id: Import2.tpl 3370 2007-11-01 16:43:07Z andrew $ */ ?>
<?php TemplateUtility::printHeader(__('Import'), array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Import" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Import Data");?></h2></td>
                </tr>
            </table>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0">Error!</p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0"><?php echo __("Success");?></p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->pendingCommits)): ?>

                <p class="warning" id="importHide0"><?php echo __("Notice");?></p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php echo __("You have recently imported CSV data.  You can click here to review or delete the imported data.");?><br />
                            <input type="button" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=viewpending';" value="<?php echo __("View Recent Imports");?>" class="button" />
                        </td>
                    </tr>
                </table>

                <br />


            <?php else: ?>

            <p class="note" id="importHide2"><?php echo __("Import Data");?> - <?php echo __("Step 2");?></p>

            <table class="searchTable" id="importTable1" width="100%">
                <tr>
                    <td><p class="warning">
                    <?php echo __("CATS may discard or fail to read some of the submitted data which it does not understand how to use. Do not discard the original data!");?>
                    </p>
                    </td>
                </tr>

            </table>

            <br />
            <?php endif; ?>

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=importUploadFile" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <table class="searchTable" width="740" id="importHide3" width="100%">
                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file"><?php echo __("Import Into");?>:</label>
                        </td>
                        <td class="tdData">
                            <?php if ($this->typeOfImport == 'Candidates'): ?>
                                <img src="images/candidate_inline.gif">&nbsp;<?php echo __("Candidates");?>
                            <?php elseif ($this->typeOfImport == 'Companies'): ?>
                                <img src="images/mru/company.gif">&nbsp;<?php echo __("Companies");?>
                            <?php elseif ($this->typeOfImport == 'Contacts'): ?>
                                <img src="images/mru/contact.gif">&nbsp;<?php echo __("Contacts");?>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file"><?php echo __("File");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="file" id="file" name="file" style="width: 260px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataTypeLabel" for="dataType"><?php echo __("File Format");?>:</label>
                        </td>
                        <td class="tdData">
                                <input type="hidden" name="typeOfImport" value="<?php echo($this->typeOfImport); ?>">

                                <input type="radio" name="typeOfFile" value="csv" checked>&nbsp;<?php echo __("Comma Delimited (CSV)");?><br />
                                <input type="radio" name="typeOfFile" value="semi" checked>&nbsp;<?php echo __("Semicolon Delimited (CSV)");?><br />
                                <input type="radio" name="typeOfFile" value="tab" >&nbsp;<?php echo __("Tab Delimited");?><br />
                                <br />
                                <span id="nextSpan">
                                    <input class="button" type="button" value="<?php echo __("Back");?>" onclick="document.location.href='?m=import';">
                                    <input class="button" type="submit" value="<?php echo __("Next");?>">
                                </span>
                                <span id="uploadingSpan" style="display:none;">
                                    <?php echo __("Uploading file, please wait...");?><br />
                                    <img src="images/loading.gif" />
                                </span>
                                </td>
                        </td>
                    </tr>

                </table>
            </form>
        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
