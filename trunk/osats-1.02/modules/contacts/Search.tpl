<?php /* $Id: Search.tpl 3676 2007-11-21 21:02:15Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Contacts'), array('modules/contacts/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
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
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Contacts')?>: <?php _e('Search Contacts')?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Search Contacts')?></p>

            <table class="searchTable" id="searchTable ">
                <tr>
                    <td>
                        <form name="searchForm" id="searchForm" action="<?php echo(osatutil::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="contacts" />
                            <input type="hidden" name="a" id="moduleAction" value="search" />
                            <input type="hidden" name="getback" id="getback" value="getback" />

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <label id="searchModeLabel" for="searcgMode"><?php _e('Search By')?>:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                                <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>><?php _e('Contact Name')?> </option>
                                <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected<?php endif; ?>><?php _e('Company Name')?></option>
                                <option value="searchByTitle"<?php if ($this->mode == "searchByTitle"): ?> selected<?php endif; ?>><?php _e('Title')?></option>
                            </select>&nbsp;
                            <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>" style="width:250px" />&nbsp;*&nbsp;
                            <input type="submit" class="button" id="searchContacts" name="searchContacts" value="<?php _e('Search')?>" />
                            <?php TemplateUtility::printAdvancedSearch(''); ?>
                        </form>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                document.searchForm.wildCardString.focus();
            </script>

            <?php if ($this->isResultsMode): ?>
                <br />
                <p class="note"><?php _e('Search Results')?> (<?php echo(count($this->rs)); ?>)</p>

                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>
                    <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                        <tr>
                            <th></th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', __('First Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', __('Last Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('title', __('Title')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('companyName', __('Company')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreated', __('Created')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('owner_user.last_name', __('Owner')); ?>
                            </th>
                        </tr>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td valign="top" nowrap="nowrap">
                                    <input type="checkbox" id="checked_<?php echo($data['contactID']); ?>" name="checked_<?php echo($data['contactID']); ?>" />
                                    <a href="javascript:void(0);" onclick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>')" title="<?php _e('View in New Window')?>">
                                        <img src="images/new_window.gif" alt="(Preview)" border="0" width="15" height="15" />
                                    </a>
                                </td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                        <?php $this->_($data['firstName']); ?>
                                    </a>
                                </td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                        <?php $this->_($data['lastName']); ?>
                                    </a>
                                </td>
                                <td valign="top" align="left"><?php $this->_($data['title']); ?></td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>" class="<?php $this->_($data['linkClassCompany']); ?>">
                                        <?php $this->_($data['companyName']); ?>
                                    </a>
                                </td>
                                <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['dateCreated']); ?></td>
                                <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php echo($this->exportForm['footer']); ?>
                    <?php echo($this->exportForm['menu']); ?>
                <?php else: ?>
                    <p><?php _e('No matching entries found.')?></p>
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