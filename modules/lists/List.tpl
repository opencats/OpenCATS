<?php /* $Id: List.tpl 3096 2007-09-25 19:27:04Z brian $ */ ?>
<?php TemplateUtility::printHeader('Lists', array( 'js/highlightrows.js', 'js/sweetTitles.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js', 'js/lists.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Lists: <?php $this->_($this->listRS['description']); ?></h2></td>
                    <td align="right">
                        <!--<a href="javascript:void(0);" onclick="" style="text-decoration:none;"><img src="images/actions/add_job_order.gif" border="0">&nbsp;Duplicate List&nbsp;&nbsp;&nbsp;-->
                        <!--<a href="javascript:void(0);" onclick="" style="text-decoration:none;"><img src="images/actions/edit.gif" border="0">&nbsp;Rename List&nbsp;&nbsp;&nbsp;-->
                        <a href="javascript:void(0);" onclick="deleteListFromListView(<?php $this->_($this->listRS['savedListID']); ?>, <?php $this->_($this->listRS['numberEntries']); ?>);" style="text-decoration:none;"><img src="images/actions/delete.gif" border="0">&nbsp;Delete List</a>
                    </td>
                </tr>
            </table>

            <p class="note">
                <span style="float:left;"><?php $this->_($this->listRS['description']); ?>  - 
                    Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
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
                    <?php $this->dataGrid->printActionArea(); ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>

        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
