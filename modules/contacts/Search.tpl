<?php /* $Id: Search.tpl 3676 2007-11-21 21:02:15Z brian $ */ ?>
<?php TemplateUtility::printHeader('Contacts', array('modules/contacts/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Contacts: Search Contacts</h2></td>
                </tr>
            </table>

            <p class="note">Search Contacts</p>

            <table class="searchTable" id="searchTable ">
                <tr>
                    <td>
                        <form name="searchForm" id="searchForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get" autocomplete="off">
                            <input type="hidden" name="m" id="moduleName" value="contacts" />
                            <input type="hidden" name="a" id="moduleAction" value="search" />
                            <input type="hidden" name="getback" id="getback" value="getback" />

                            <?php TemplateUtility::printSavedSearch($this->savedSearchRS); ?>

                            <label id="searchModeLabel" for="searcgMode">Search By:</label>&nbsp;
                            <select id="searchMode" name="mode" onclick="advancedSearchConsider();" class="selectBox">
                                <option value="searchByFullName"<?php if ($this->mode == "searchByFullName"): ?> selected<?php endif; ?>>Contact Name</option>
                                <option value="searchByCompanyName"<?php if ($this->mode == "searchByCompanyName"): ?> selected<?php endif; ?>>Company Name</option>
                                <option value="searchByTitle"<?php if ($this->mode == "searchByTitle"): ?> selected<?php endif; ?>>Title</option>
                            </select>&nbsp;
                            <input type="text" class="inputbox" id="searchText" name="wildCardString" value="<?php if (!empty($this->wildCardString)) echo(urldecode($this->wildCardString)); ?>" style="width:250px" />&nbsp;*&nbsp;
                            <input type="submit" class="button" id="searchContacts" name="searchContacts" value="Search" />
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
                <p class="note">Search Results (<?php echo(count($this->rs)); ?>)</p>

                <?php if (!empty($this->rs)): ?>
                    <?php echo($this->exportForm['header']); ?>
                    <table class="sortable" width="100%" onmouseover="javascript:trackTableHighlight(event)">
                        <tr>
                            <th></th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('title', 'Title'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('companyName', 'Company'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateCreated', 'Created'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('owner_user.last_name', 'Owner'); ?>
                            </th>
                        </tr>

                        <?php foreach ($this->rs as $rowNumber => $data): ?>
                            <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                                <td valign="top" nowrap="nowrap">
                                    <input type="checkbox" id="checked_<?php echo($data['contactID']); ?>" name="checked_<?php echo($data['contactID']); ?>" />
                                    <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>')" title="View in New Window">
                                        <img src="images/new_window.gif" alt="(Preview)" border="0" width="15" height="15" />
                                    </a>
                                </td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                        <?php $this->_($data['firstName']); ?>
                                    </a>
                                </td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($data['contactID']); ?>" class="<?php $this->_($data['linkClassContact']); ?>">
                                        <?php $this->_($data['lastName']); ?>
                                    </a>
                                </td>
                                <td valign="top" align="left"><?php $this->_($data['title']); ?></td>
                                <td valign="top" align="left">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php $this->_($data['companyID']); ?>" class="<?php $this->_($data['linkClassCompany']); ?>">
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
                    <p>No matching entries found.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
