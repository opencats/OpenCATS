<?php /* $Id: Lists.tpl 3311 2007-10-25 21:36:18Z andrew $ */ ?>
<?php TemplateUtility::printHeader(__('Lists'), array( 'js/highlightrows.js', 'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents"<?php echo !$this->dataGrid->getNumberOfRows() ? ' style="background-color: #E6EEFF; padding: 0px;"' : ''; ?>>
            <?php if ($this->dataGrid->getNumberOfRows()): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __('Lists').': '.__('Home')?></h2></td>
                    <td align="right">
                        <form name="jobOrdersViewSelectorForm" id="jobOrdersViewSelectorForm" action="<?php echo(osatutil::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="joborders" />
                            <input type="hidden" name="a" value="list" />

                            <table class="viewSelector">
                                <tr>
                                    <td>
                                    </td>
                                 </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>

            <p class="note">
                <span style="float:left;"><?php
                  echo __('Lists').' - '.__('Page').' '.$this->dataGrid->getCurrentPageHTML();
                  echo ' ('; 
                  echo format_number_choice('countItems', array($this->dataGrid->getNumberOfRows()), $this->dataGrid->getNumberOfRows());
                  echo ') ';
                ?></span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">

                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>
            <?php else: ?>

            <br /><br /><br /><br />
            <div style="height: 95px; background: #E6EEFF url(images/nodata/listsTop.jpg);">
                &nbsp;
            </div>
            <br /><br />
            <table cellpadding="0" cellspacing="0" border="0" width="956">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    <?php _e('Create lists to group candidates, job orders, companies and contacts and perform actions on them quickly.') ?>
                    <br /><br />
                    <span style="font-size: 14px; font-weight: normal;">
                    <?php _e('Create lists from the job orders, candidates, companies or contacts tab.') ?>
                    </span>
                    </div>
                </td>

                </tr>
            </table>

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
