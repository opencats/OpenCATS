<?php /* $Id: Search.tpl 3813 2007-12-05 23:16:22Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Candidates'), array('modules/candidates/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
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
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Candidates');?>: <?php _e('Search Candidates');?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Search Candidates');?></p>

            <table class="searchTable" id="searchTable ">
                <tr>
                    <td>
                        <form name="searchForm" id="searchForm" action="<?php echo(osatutil::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="candidates" />
                            <input type="hidden" name="a" id="moduleAction" value="search" />
                            <input type="hidden" name="getback" id="getback" value="getback" />

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <label id="searchModeLabel" for="searchMode"><?php _e('Search By');?>:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                                <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>><?php _e('Candidate');?> <?php _e('Name');?></option>
                                <option value="searchByResume"<?php if ($this->mode == "searchByResume" || empty($this->mode)): ?> selected<?php endif; ?>><?php _e('Resume');?> <?php _e('Keywords');?></option>
                                <option value="searchByKeySkills"<?php if ($this->mode == "searchByKeySkills"): ?> selected<?php endif; ?>><?php _e('Key Skills');?></option>
                                <option value="phoneNumber"<?php if ($this->mode == "phoneNumber"): ?> selected<?php endif; ?>><?php _e('Phone Number');?></option>
                            </select>&nbsp;
                            <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) $this->_($this->wildCardString); ?>" style="width:250px" />&nbsp;*&nbsp;
                            <input type="submit" class="button" id="searchCandidates" name="searchCandidates" value="<?php _e('Search');?>" />
                            <?php TemplateUtility::printAdvancedSearch('searchByKeySkills,searchByResume'); ?>
                        </form>
                    </td>
                </tr>
            </table>

            <script type="text/javascript">
                document.searchForm.wildCardString.focus();
            </script>

            <?php if ($this->isResumeMode && $this->isResultsMode): ?>
                <br />
                <?php if (!empty($this->rs)): ?>
                    <p class="note"><?php _e('Search Results');?> &nbsp;<?php $this->_($this->pageStart); ?> to <?php $this->_($this->pageEnd); ?> of <?php $this->_($this->totalResults); ?></p>
                    <?php echo($this->exportForm['header']); ?>
                <?php else: ?>
                    <p class="note"><?php _e('Search Results');?></p>
                <?php endif; ?>

                <table class="sortable" width="925">
                    <thead>
                        <tr>
                            <th nowrap>&nbsp;</th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', __('First Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', __('Last Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap"><?php _e('Resume');?></th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('city', __('City')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('state', __('State')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreatedSort', __('Created')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateModifiedSort', __('Modified')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('ownerSort', __('Owner')); ?>
                            </th>
                        </tr>
                    </thead>

                    <?php if (!empty($this->rs)): ?>
                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <?php if ($data['candidateID'] > 0): ?>
                                    <td valign="top" nowrap>
                                        <input type="checkbox" id="checked_<?php echo($data['candidateID']); echo($data['attachmentID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" />
                                        <a href="javascript:void(0);" onClick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="<?php _e('View in New Window');?>">
                                            <img src="images/new_window.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                                        </a>
                                    </td>
                                    <td valign="top">
                                        <a href="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                            <?php $this->_($data['firstName']); ?>
                                        </a>
                                    </td>
                                    <td valign="top">
                                        <a href="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                            <?php $this->_($data['lastName']); ?>
                                        </a>
                                    </td>
                                <?php else: ?>
                                    <td>&nbsp;</td>
                                    <td valign="top" nowrap="nowrap">
                                    </td>
                                    <td valign="top" colspan="2">
                                        <a href="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=add&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>">
                                            <img src="images/candidate_tiny.gif" width="16" height="16" border="0" class="absmiddle" alt="" title="<?php _e('Create Candidate Profile');?>" />
                                        </a>
                                        &nbsp;<?php _e('Bulk Resume');?>
                                    </td>
                                <?php endif; ?>
                                <td valign="top">
                                    <a href="#" onclick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['attachmentID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')">
                                        <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                                    </a>&nbsp;
                                    <?php echo($data['excerpt']); ?>
                                </td>
                                <td valign="top"><?php $this->_($data['city']); ?></td>
                                <td valign="top"><?php $this->_($data['state']); ?></td>
                                <td valign="top"><?php $this->_($data['dateCreated']); ?></td>
                                <td valign="top"><?php $this->_($data['dateModified']); ?></td>
                                <td valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8"><?php _e('No matching entries found.');?></td>
                        </tr>
                    <?php endif; ?>
                </table>
                <?php echo($this->exportForm['footer']); ?>
                <?php echo($this->exportForm['menu']); ?>
                <?php if (!empty($this->rs)): ?>
                    <div style="float: right"><?php $this->pager->printNavigation(); ?></div>
                    <br />
                <?php endif; ?>
            <?php elseif ($this->isResultsMode): ?>
                <br />
                <p class="note"><?php _e('Search Results');?> (<?php echo(count($this->rs)); ?>)</p>

                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>
                    <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                        <tr>
                            <th nowrap>&nbsp;</th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', __('First Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', __('Last Name')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">Key Skills</th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('city', __('City')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('state', __('State')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreated', __('Created')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateModified', __('Modified')); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('owner_user.last_name', __('Owner')); ?>
                            </th>
                        </tr>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td nowrap>
                                    <input type="checkbox" id="checked_<?php echo($data['candidateID']); ?>" name="checked_<?php echo($data['candidateID']); ?>" />
                                    <a href="javascript:void(0);" onClick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>')" title="<?php _e('View in New Window');?>">
                                        <img src="images/new_window.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                                    </a>&nbsp;
                                </td>
                                <td>
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                        <?php $this->_($data['firstName']); ?>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php $this->_($data['candidateID']); ?>">
                                        <?php $this->_($data['lastName']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (isset($data['resumeID'])): ?>
                                        <a href="javascript:void(0);" onclick="window.open('<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=viewResume&amp;wildCardString=<?php $this->_(urlencode($this->wildCardString)); ?>&amp;attachmentID=<?php $this->_($data['resumeID']); ?>', 'viewResume', 'scrollbars=1,width=700,height=600')" Title="<?php _e('View resume');?>">
                                            <img src="images/resume_preview_inline.gif" class="abstop" alt="(Preview)" border="0" width="15" height="15" />
                                        </a>
                                    <?php endif; ?>
                                    <?php $this->_($data['keySkills']); ?>&nbsp;
                                </td>
                                <td><?php $this->_($data['city']); ?>&nbsp;</td>
                                <td><?php $this->_($data['state']); ?>&nbsp;</td>
                                <td><?php $this->_($data['dateCreated']); ?>&nbsp;</td>
                                <td><?php $this->_($data['dateModified']); ?>&nbsp;</td>
                                <td nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?>&nbsp;</td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php echo($this->exportForm['footer']); ?>
                    <?php echo($this->exportForm['menu']); ?>
                <?php else: ?>
                    <p><?php _e('No matching entries found.');?></p>
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
