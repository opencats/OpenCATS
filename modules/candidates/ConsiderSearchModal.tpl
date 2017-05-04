<?php /* $Id: ConsiderSearchModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader(__('Candidates'), array(), __('Add Candidates to Job Order Pipeline')); ?>

    <?php if (!$this->isFinishedMode): ?>
        <p><?php echo __("Search for a job order below, and then click on the job title to add the candidate to the selected job order pipeline.");?></p>

        <table class="searchTable">
            <form id="searchByJobTitleForm" name="searchByJobTitleForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch" method="post">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="mode_jobtitle" name="mode" value="searchByJobTitle" />
                <input type="hidden" id="candidateID_jobtitle" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>" />

                <tr>
                    <td><?php echo __("Search by Job Title");?>:&nbsp;</td>
                    <td><input type="text" class="inputbox" id="wildCardString_jobTitle" name="wildCardString"style="width:200px;" />&nbsp;*</td>
                </tr>
                <tr>
                    <td><input type="submit" class="button" id="searchByJobTitle" name="searchByJobTitle" value="<?php echo __("Search by Job Title");?>" /></td>
                </tr>
                <tr>
                    <td>&nbsp;</td>
                </tr>
            </form>

            <form id="searchByCompanyNameForm" name="searchByCompanyNameForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch" method="post">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="mode_companyname" name="mode" value="searchByCompanyName" />
                <input type="hidden" id="candidateID_companyname" name="candidateIDArrayStored" value="<?php echo($this->candidateIDArrayStored); ?>" />

                <tr>
                    <td><?php echo __("Search by Company Name");?>:&nbsp;</td>
                    <td><input type="text" class="inputbox" id="wildCardString_companyname" name="wildCardString" style="width:200px;" />&nbsp;*</td>
                </tr>
                <tr>
                    <td><input type="submit" class="button" id="searchByCompanyName" name="searchByCompanyName" value="<?php echo __("Search by Company Name");?>" /></td>
                </tr>
            </form>
        </table>

        <?php if (empty($_POST['mode']) || $_POST['mode'] == 'searchByJobTitle'): ?>
            <script type="text/javascript">
                document.searchByJobTitleForm.wildCardString.focus();
            </script>
        <?php else: ?>
            <script type="text/javascript">
                document.searchByCompanyNameForm.wildCardString.focus();
            </script>
        <?php endif; ?>

        <?php if ($this->isResultsMode): ?>
            <br />
            <p class="noteUnsized"><?php echo __("Search Results");?></p>

            <?php if (!empty($this->rs)): ?>
                <table class="sortable" width="100%">
                    <tr>
                        <th align="left"><?php echo __("Ref. #");?></th>
                        <th align="left"><?php echo __("Title");?></th>
                        <th align="left"><?php echo __("Company");?></th>
                        <th align="left"><?php echo __("Type");?></th>
                        <th align="left"><?php echo __("Status");?></th>
                        <th align="left"><?php echo __("Created");?></th>
                        <th align="left"><?php echo __("Start");?></th>
                        <th align="left"><?php echo __("Recruiter");?></th>
                        <th align="left"><?php echo __("Owner");?></th>
                        <th align="center"><?php echo __("Action");?></th>
                    </tr>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td align="left" valign="top"><?php $this->_($data['jobID']); ?></td>
                            <td align="left" valign="top">
                                <?php if (!$data['inPipeline']): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addToPipeline&amp;getback=getback&amp;candidateIDArrayStored=<?php echo($this->candidateIDArrayStored); ?>&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                                        <?php $this->_($data['title']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['title']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td align="left" valign="top"><?php $this->_($data['companyName']); ?></td>
                            <td align="left" valign="top"><?php $this->_($data['type']); ?></td>
                            <td align="left" valign="top"><?php $this->_($data['status']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['dateCreated']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['startDate']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['recruiterAbbrName']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?></td>
                            <td align="center" nowrap="nowrap">
                                <a href="#" title="Show Job Order" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;display=popup&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>', 'viewJobOrderDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" border="0" class="absmiddle" />
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p><?php echo __("No matching entries found.");?></p>
            <?php endif; ?>
            <input type="button" class="button" id="showRecentJobOrders" name="showRecentJobOrders" value="<?php echo __("Show Recently Modified Job Orders");?>" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch&amp;candidateIDArrayStored=<?php echo($this->candidateIDArrayStored); ?>';" />
        <?php else: ?>
            <br />
            <p class="noteUnsized"><?php echo __("Recently Modified Job Orders");?></p>

            <?php if (!empty($this->rs)): ?>
                <table class="sortable" width="100%">
                    <tr>
                        <th align="left"><?php echo __("Ref. #");?></th>
                        <th align="left"><?php echo __("Title");?></th>
                        <th align="left"><?php echo __("Company");?></th>
                        <th align="left"><?php echo __("Type");?></th>
                        <th align="left"><?php echo __("Status");?></th>
                        <th align="left"><?php echo __("Modified");?></th>
                        <th align="left"><?php echo __("Start");?></th>
                        <th align="left"><?php echo __("Recruiter");?></th>
                        <th align="left"><?php echo __("Owner");?></th>
                        <th align="center"><?php echo __("Action");?></th>
                    </tr>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td align="left" valign="top"><?php $this->_($data['jobID']); ?></td>
                            <td align="left" valign="top">
                                <?php if (!$data['inPipeline']): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addToPipeline&amp;getback=getback&amp;candidateIDArrayStored=<?php echo($this->candidateIDArrayStored); ?>&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>" class="<?php $this->_($data['linkClass']); ?>">
                                        <?php $this->_($data['title']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="<?php $this->_($data['linkClass']); ?>"><?php $this->_($data['title']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td align="left" valign="top"><?php $this->_($data['companyName']); ?></td>
                            <td align="left" valign="top"><?php $this->_($data['type']); ?></td>
                            <td align="left" valign="top"><?php $this->_($data['status']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['dateModified']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['startDate']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['recruiterAbbrName']); ?></td>
                            <td align="left" valign="top" nowrap="nowrap"><?php $this->_($data['ownerAbbrName']); ?></td>
                            <td align="center" nowrap="nowrap">
                                <a href="#" title="Show Job Order" onclick="javascript:openCenteredPopup('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;display=popup&amp;jobOrderID=<?php $this->_($data['jobOrderID']); ?>', 'viewJobOrderDetails', 1000, 675, true); return false;">
                                    <img src="images/new_browser_inline.gif" alt="consider" width="16" height="16" border="0" class="absmiddle" />
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php else: ?>
                <p><?php echo __("No recent job orders found.");?></p>
            <?php endif; ?>
        <?php endif; ?>
    <?php else: ?>
        <p>
        <?php if(count($this->candidateIDArray)>1): ?>
        <?php echo(count($this->candidateIDArray)); ?>
        	<?php echo sprintf(__("The  %s candidates have been successfully added to the pipeline for the selected job order."),count($this->candidateIDArray));?>
        <?php else: ?>
        	<?php echo __("The candidate has been successfully added to the pipeline for the selected job order.");?>
        <?php endif; ?>
        </p>

        <form method="get" action="<?php echo(CATSUtility::getIndexName()); ?>">
            <input type="button" name="close" value="<?php echo __("Close");?>" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>

    </body>
</html>
