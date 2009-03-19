<?php /* $Id: Error.tpl 1889 2007-02-20 05:21:54Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Companies')); ?>
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
                        <img src="images/companies.gif" width="24" height="24" border="0" alt="Companies" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Companies')?>: <?php _e('Error')?></h2></td>
                </tr>
            </table>

            <p class="fatalError">
                <?php _e('A fatal error has occurred.')?><br />
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
