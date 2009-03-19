<?php /* $Id: Search.tpl 3676 2007-11-21 21:02:15Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Job Orders'), array('modules/joborders/validator.js', 'js/sweetTitles.js',  'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js', 'js/searchSaved.js')); ?>
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
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Job Orders') ?>: <?php _e('Search Job Orders') ?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Search Job Orders') ?></p>

            <table class="searchTable" id="searchTable">
                <tr>
                    <td>
                        <form name="searchForm" id="searchForm" action="<?php echo(osatutil::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="joborders" />
                            <input type="hidden" name="a" id="moduleAction" value="search" />
                            <input type="hidden" name="getback" id="getback" value="getback" />

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <label id="searchModeLabel" for="searchMode"><?php _e('Search By') ?>:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                                <option value="searchByJobTitle"<?php if ($this->mode == "searchByJobTitle"): ?> selected="selected"<?php endif; ?>><?php _e('Job Title') ?></option>
                                <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected="selected"<?php endif; ?>><?php _e('Company Name') ?></option>
                            </select>&nbsp;
                            <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>" style="width:250px" />&nbsp;*&nbsp;
                            <input type="submit" class="button" id="searchJobOrders" name="searchJobOrders" value="<?php _e('Search') ?>" />
                            <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
                        </form>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                document.searchForm.wildCardString.focus();
            </script>

            <?php if ($this->isResultsMode): ?>
                <br />
                <p class="note"><?php _e('Search Results') ?></p>

                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>

                    <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                        <tr>
                            <th></th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('title', __('Title')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('companyName', __('Company')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('type', __('Type')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('status', __('Status')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreated', __('Created')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('startDate', __('Start')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('recruiterLastName', __('Recruiter')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('owner_user.last_name', __('Owner')); ?>
                            </th>
                        </tr>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td valign="top" nowrap="nowrap">
                                    <input type="checkbox" id="checked_<?php echo($data['jobOrderID']); ?>" name="checked_<?php echo($data['jobOrderID']); ?>" />
                                     <a href="javascript:void(0);" onclick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>')" title="<?php _e('View in New Window') ?>">
                                        <img src="images/new_window.gif" alt="(<?php _e('Preview') ?>)" border="0" width="15" height="15" />
                                    </a>
                                </td>
                                <td valign="top">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                                        <?php $this->_($data['title']); ?>
                                    </a>
                                </td>
                                <td valign="top">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>">
                                        <?php $this->_($data['companyName']); ?>
                                    </a>
                                </td>
                                <td align="left" valign="top"><?php $this->_($data['type']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['status']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                                <td align="left" valign="top"><?php $this->_($data['startDate']); ?>&nbsp;</td>
                                <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['recruiterAbbrName']); ?>&nbsp;</td>
                                <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php echo($this->exportForm['footer']); ?>
                    <?php echo($this->exportForm['menu']); ?>
                <?php else: ?>
                    <p><?php _e('No matching entries found.') ?></p>
                <?php endif; ?>
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