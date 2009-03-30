<?php /* $Id: Import1.tpl 3780 2007-12-03 21:13:56Z andrew $ */ ?>
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
                    <td><h2><?php _e('Import Data')?></h2></td>
                </tr>
            </table>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0"><?php _e('Error')?>!</p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0"><?php _e('Success')?></p>

                <table class="searchTable" id="importHide1" width="100%">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->pendingCommits)): ?>

                <p class="warning" id="importHide0"><?php _e('Notice')?></p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php _e('You have recently imported CSV data. You can click here to review or delete the imported data.')?><br />
                            <input type="button" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=viewpending';" value="View Recent Imports" class="button" />
                        </td>
                    </tr>
                </table>

                <br />

            <?php endif; ?>

            <p class="note"><?php _e('Import Data')?></p>

            <table class="searchTable" id="importTable1" width="100%">
                <tr>
                    <td><?php _e('OSATS may discard or fail to read some of the submitted data which it does not understand how to use. Do not discard the original data')?>!
                    </td>
                </tr>
            </table>

            <br />

            <table class="searchTable" id="importTable2" width="100%">
                <tr>
                    <td><?php _e('What would you like to import?')?><br />
                    <br />
                    <form name="importDataForm" id="importDataForm" action="<?php echo(osatutil::getIndexName()); ?>" method="get" autocomplete="off">
                        <input type="hidden" name="m" value="import">
                        <input type="hidden" name="a" value="importSelectType">

                        <input type="radio" name="typeOfImport" value="resume" checked>&nbsp;<img src="images/file/doc.gif">&nbsp;<?php _e('Resumes')?><br />
                        <input type="radio" name="typeOfImport" value="Candidates">&nbsp;<img src="images/candidate_inline.gif">&nbsp;<?php _e('Candidates')?><br />
                        <input type="radio" name="typeOfImport" value="Companies" >&nbsp;<img src="images/mru/company.gif">&nbsp;<?php _e('Companies')?><br />
                        <input type="radio" name="typeOfImport" value="Contacts" >&nbsp;<img src="images/mru/contact.gif">&nbsp;<?php _e('Contacts')?><br />
                        <br />
                        <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
						<input class="button" type="submit" value="<?php _e('Next')?>">
                        </td>
                    </form>
                </tr>
            </table>

            <?php if ($this->bulk['numBulkAttachments'] > 0 && $_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
            <br />
            <div style="background-color: #f0f0f0; color: #000000; border: 1px solid #000000; text-align: left; font-size: 14px; padding: 10px; margin: 0 0 15px 0; font-weight: normal;">
                <?php echo __('You have uploaded %s unclassified resume documents. You can search these documents, but they are not attached to candidates because candidate information - like their name, address, etc. - was not available when they were uploaded.', number_format($this->bulk['numBulkAttachments'], 0))?>
                <br /><br />
                <?php _e('Rescan the documents to try to automatically detect candidate information. Enter it manually if necessary.')?>
                <br /><br />
                <table cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td style="padding-right: 10px;">
                            <input type="button" value="<?php _e('Rescan Documents')?>" name="rescan" id="rescanButton" class="button" onclick="document.location.href='<?php echo osatutil::getIndexName(); ?>?m=import&a=importBulkResumes';" />
                        </td>
                        <td>
                            <input type="button" value="<?php _e('Delete Documents')?>" name="delete" id="deleteButton" class="button" onclick="if (confirm(<?php _e('This will delete all searchable attachments that have not been associated with candidates. This action cannot be undone. Are you sure you want to continue?')?>)) document.location.href='<?php echo osatutil::getIndexName(); ?>?m=import&a=deleteBulkResumes';" />
                        </td>
                    </tr>
                </table>
            </div>
            <?php endif; ?>

            <br />

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