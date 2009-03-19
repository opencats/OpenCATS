<?php /* $Id: ImportRecent.tpl 3548 2007-11-09 23:54:52Z andrew $ */ ?>
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

            <?php if (isset($this->successMessage)): ?>

                <p class="note"><?php _e('Success')?></p>

                <table class="searchTable">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php endif; ?>

            <p class="note"><?php _e('Recent Commits')?></p>

            <table class="searchTable">
                <tr>
                    <td>
                        <?php foreach ($this->data as $data): ?>
							<?php echo __('Import No. %s %s - %s entries added to database.', array($data['importID'], $data['dateCreated'], $data['addedLines'])) ?><br />
                            <input type="button" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=revert&amp;importID=<?php echo($data['importID']) ?>';" value="<?php _e('Revert Import')?>" class="button">
                            <input type="button" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=viewerrors&amp;importID=<?php echo($data['importID']) ?>';" value="<?php _e('View Errors')?>" class="button">
                            <br /><br />
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
            <br />

            <?php if (isset($this->importErrors)): ?>

                <p class="note"><?php _e('Errors Reported by Import')?></p>

                <table class="searchTable" width="740">
                    <tr>
                        <td>
                            <?php echo($this->importErrors) ?>
                        </td>
                    </tr>
                </table>
                <input type="button" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=import&amp;a=revert&amp;importID=<?php echo($this->importID); ?>';" value="<?php _e('Revert Import')?>" class="button">

            <?php endif; ?>

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