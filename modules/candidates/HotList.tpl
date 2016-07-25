<?php /* $Id: HotList.tpl 3430 2007-11-06 20:44:51Z will $ */ ?>
<?php TemplateUtility::printNonSelectableHeader('Candidates', array( 'js/highlightrows.js', 'js/export.js', 'js/listEditor.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Hot Lists</h2></td>
                    <td align="right">
                        <form name="candidatesViewSelectorForm" id="candidatesViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="candidates" />
                            <input type="hidden" name="a" value="hotList" />

                            <table class="viewSelector">
                                <tr>
                                    <td valign="top" align="right" nowrap>
                                        <?php $this->pager->printNavigation('lastName', false); ?>
                                    </td>
                                    <td>
                                        <select name="view" id="hotListSelect" onChange="if (this.value != 'edit' &amp;&amp; this.value != 'nullline') { document.candidatesViewSelectorForm.submit(); } else { if (this.value == 'edit') { listEditor('Hot Lists', 'hotListSelect', 'hotListCSV', false, 'candidatesViewSelectorForm', 0); } if (this.value == 'nullline') { this.value = '(none)'; } }" >
                                            <?php if ($this->getUserAccessLevel('candidates.manageHotLists') >= ACCESS_LEVEL_DELETE): ?>
                                                <option value="edit">(Manage Hot Lists)</option>
                                            <?php else: ?>
                                                <option value="nullline">Hot Lists (Select to View):</option>
                                            <?php endif; ?>
                                            <option value="nullline">------------------------</option>
                                            <option value="-1" <?php if ($this->hotListID == -1): ?> selected<?php endif; ?>>All Hot Candidates</option>
                                            <?php foreach ($this->hotListsRS as $row => $rowIndex) : ?>
                                                <option value="<?php echo($this->hotListsRS[$row]['hotListID']) ?>"<?php if ($this->hotListID == $this->hotListsRS[$row]['hotListID']): ?> selected<?php endif; ?>"><?php echo($this->hotListsRS[$row]['description']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td valign="top" align="right" nowrap>
                                        <?php if ($_SESSION['CATS']->getCheckBox('onlyMyCandidates')) : ?>
                                            <input type="hidden" name="myCandidates" value="off" />
                                            <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" onclick="document.candidatesViewSelectorForm.submit();" checked />Only My Candidates&nbsp;
                                        <?php else: ?>
                                            <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" onclick="document.candidatesViewSelectorForm.submit();" />Only My Candidates&nbsp;
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>

                            <input type="hidden" id="hotListCSV" name="hotListCSV" value="<?php $this->_($this->hotListString); ?>" />
                        </form>
                    </td>
               </tr>
            </table>


            <p class="note">All Candidates - Page <?php echo($this->currentPage); ?> <?php if ($this->pager->getSortBy() == 'dateModifiedSort'): ?>(Most Recently Modified First)<?php endif; ?> <?php if ($this->onlyMyCandidates): ?>(Only My Candidates)<?php endif; ?></p>

                        <?php if (!empty($this->rs)): ?><?php echo($this->exportForm['formHead']); ?><?php endif; ?>
            
            <?php $this->pager->drawPager();  ?>
            
            <?php if (!empty($this->rs)): ?><?php echo($this->exportForm['formFooter']); ?><?php endif; ?>

        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
