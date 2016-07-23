<?php /* $Id: JobOrders.tpl 3676 2007-11-21 21:02:15Z brian $ */ ?>
<?php TemplateUtility::printHeader('Job Orders', array( 'js/highlightrows.js',  'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <style type="text/css">
    div.addJobOrderButton { background: #4172E3 url(images/nodata/jobOrdersButton.jpg); cursor: pointer; width: 337px; height: 67px; }
    div.addJobOrderButton:hover { background: #4172E3 url(images/nodata/jobOrdersButton-o.jpg); cursor: pointer; width: 337px; height: 67px; }
    </style>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents"<?php echo !$this->totalJobOrders ? ' style="background-color: #E6EEFF; padding: 0;"' : ''; ?>>
            <?php if ($this->totalJobOrders): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Job Orders: Home</h2></td>

                    <?php TemplateUtility::printPopupContainer(); ?>

                    <td align="right">
                        <form name="jobOrdersViewSelectorForm" id="jobOrdersViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="joborders" />
                            <input type="hidden" name="a" value="list" />

                            <table class="viewSelector">
                                <tr>
                                    <td>
                                        <select name="view" id="view" onchange="<?php echo($this->dataGrid->getJSAddFilter('Status', '==', 'this.value', 'true')); ?>" class="selectBox">
                                            <option value="Active / OnHold / Full"<?php if ($this->dataGrid->getFilterValue('Status') == 'Active / OnHold / Full'): ?> selected="selected"<?php endif; ?>>Active / On Hold / Full</option>
                                            <option value="Active"<?php if ($this->dataGrid->getFilterValue('Status') == 'Active'): ?> selected="selected"<?php endif; ?>>Active</option>
                                            <option value="OnHold / Full"<?php if ($this->dataGrid->getFilterValue('Status') == 'OnHold / Full'): ?> selected="selected"<?php endif; ?>>On Hold / Full</option>
                                            <option value="Closed / Canceled"<?php if ($this->dataGrid->getFilterValue('Status') == 'Closed / Canceled'): ?> selected="selected"<?php endif; ?>>Closed / Canceled</option>
                                            <option value="Upcoming / Lead"<?php if ($this->dataGrid->getFilterValue('Status') == 'Upcoming / Lead'): ?> selected="selected"<?php endif; ?>>Upcoming / Lead</option>
                                            <option value=""<?php if ($this->dataGrid->getFilterValue('Status') == ''): ?> selected="selected"<?php endif; ?>>All</option>
                                        </select>
                                    </td>

                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyMyJobOrders" id="onlyMyJobOrders" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                                        <label for="onlyMyJobOrders">Only My Job Orders</label>&nbsp;

                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyHotJobOrders" id="onlyHotJobOrders" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                                        <label for="onlyHotJobOrders">Only Hot Job Orders</label>&nbsp;
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <?php if ($this->errMessage != ''): ?>
            <div id="errorMessage" style="padding: 25px 0px 25px 0px; border-top: 1px solid #800000; border-bottom: 1px solid #800000; background-color: #f7f7f7;margin-bottom: 15px;">
            <table>
                <tr>
                    <td align="left" valign="center" style="padding-right: 5px;">
                        <img src="images/large_error.gif" align="left">
                    </td>
                    <td align="left" valign="center">
                        <span style="font-size: 12pt; font-weight: bold; color: #800000; line-height: 12pt;">There was a problem with your request:</span>
                        <div style="font-size: 10pt; font-weight: bold; padding: 3px 0px 0px 0px;"><?php echo $this->errMessage; ?></div>
                    </td>
                </tr>
            </table>
            </div>
            <?php endif; ?>

            <?php if ($this->totalJobOrders): ?>
            <p class="note">
                <span style="float:left;">Job Orders  -
                    Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                    (<?php if ($this->dataGrid->getFilterValue('Status') != '') echo ($this->dataGrid->getFilterValue('Status')); else echo ('All'); ?>)
                    <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>(Only My Job Orders)<?php endif; ?>
                    <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Job Orders)<?php endif; ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">
                    <?php $this->dataGrid->printActionArea(); ?>&nbsp;
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>
            <?php else: ?>

            <br /><br /><br /><br />
            <div style="height: 95px; background: #E6EEFF url(images/nodata/jobOrdersTop.jpg);">
                &nbsp;
            </div>
            <br /><br />
                 <?php if ($this->getUserAccessLevel('joborders.add') >= ACCESS_LEVEL_EDIT): ?>
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Add a job order, then attach candidates
                    to the pipeline with their status (interviewing, qualifying, etc.)
                    </div>

                    <a href="javascript:void(0);"  onclick="showPopWin('<?php echo CATSUtility::getIndexName(); ?>?m=joborders&amp;a=addJobOrderPopup', 400, 250, null);">
                    <div class="addJobOrderButton">&nbsp;</div>
                    </a>
                </td>

                </tr>
            </table>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
