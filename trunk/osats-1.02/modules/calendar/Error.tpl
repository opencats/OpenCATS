<?php /* $Id: Error.tpl 1930 2007-02-22 08:39:53Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Calendar')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
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
                        <img src="images/calendar.gif" width="24" height="24" alt="Calendar" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Calendar');?></h2></td>
                </tr>
            </table>

            <p class="fatalError">
                <?php _e('A fatal error has occurred.');?><br />
                <br />
                <?php echo($this->errorMessage); ?>
            </p>
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
