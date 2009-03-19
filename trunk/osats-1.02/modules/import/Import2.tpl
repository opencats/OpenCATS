<?php /* $Id: Import2.tpl 3370 2007-11-01 16:43:07Z andrew $ */ ?>
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

            <p class="note" id="importHide2"><?php _e('Import Data')?> - <?php _e('Step')?> 2</p>

            <table class="searchTable" id="importTable1" width="100%">
                <tr>
                    <td><?php _e('OSATS may discard or fail to read some of the submitted data which it does not understand how to use. Do not discard the original data')?>!
                    </td>
                </tr>

            </table>

            <br />

            <form name="importDataForm" id="importDataForm" action="<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=importUploadFile" enctype="multipart/form-data" method="post" autocomplete="off" onsubmit="document.getElementById('nextSpan').style.display='none'; document.getElementById('uploadingSpan').style.display='';">
                <table class="searchTable" width="740" id="importHide3" width="100%">
                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file"><?php _e('Import Into')?>:</label>
                        </td>
                        <td class="tdData">
                            <?php if ($this->typeOfImport == __('Candidates')): ?>
                                <img src="images/candidate_inline.gif">&nbsp;<?php _e('Candidates')?>
                            <?php elseif ($this->typeOfImport == __('Companies')): ?>
                                <img src="images/mru/company.gif">&nbsp;<?php _e('Companies')?>
                            <?php elseif ($this->typeOfImport == __('Contacts')): ?>
                                <img src="images/mru/contact.gif">&nbsp;<?php _e('Contacts')?>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="fileLabel" for="file"><?php _e('File')?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="file" id="file" name="file" style="width: 260px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataTypeLabel" for="dataType"><?php _e('File Format')?>:</label>
                        </td>
                        <td class="tdData">
                                <input type="hidden" name="typeOfImport" value="<?php echo($this->typeOfImport); ?>">

                                <input type="radio" name="typeOfFile" value="csv" checked>&nbsp;<?php _e('Comma Delimited')?> (CSV)<br />
                                <input type="radio" name="typeOfFile" value="tab" >&nbsp;<?php _e('Tab Delimited')?><br />
                                <br />
                                <span id="nextSpan">
                                    <input class="button" type="button" value="<?php _e('Back')?>" onclick="document.location.href='?m=import';">
                                    <input class="button" type="submit" value="<?php _e('Next')?>">
                                </span>
                                <span id="uploadingSpan" style="display:none;">
                                    <?php _e('Uploading file, please wait')?>...<br />
                                    <img src="images/loading.gif" />
                                </span>
                                </td>
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