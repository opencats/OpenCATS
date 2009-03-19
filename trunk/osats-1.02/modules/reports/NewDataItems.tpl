<?php /* $Id: NewDataItems.tpl 3548 2007-11-09 23:54:52Z andrew $ */ ?>
<?php TemplateUtility::printHeader(__('Reports')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Reports" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Reports') ?>: <?php _e('New Data Items') ?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('New Data Items') ?></p>

            <table border="0" width="925">
                <tr>
                    <td width="320">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('Today') ?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesToday']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=today" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=today" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsToday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('Yesterday');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=yesterday" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=yesterday" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsYesterday']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('This Week');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisWeek" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisWeek" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>

                    <td>
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('Last Week');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastWeek" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastWeek" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastWeek']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320" valign="top">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('This Month');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisMonth" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisMonth" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320" valign="top">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('Last Month');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastMonth" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastMonth" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastMonth']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>

                    <td width="320" valign="top">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('This Year');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisYear" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisYear" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsThisYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320" valign="top">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('Last Year');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['jobOrdersLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['candidatesLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('New Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['companiesLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastYear" target="_blank"><?php _e('New Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['submissionsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastYear" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['placementsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('New Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['contactsLastYear']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>

                    <td width="320" valign="top">
                        <table class="statisticsTable" width="300">
                            <tr>
                                <th align="left"><?php _e('To Date');?></th>
                                <th align="left">&nbsp;&nbsp;</th>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('Total Companies') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['totalCompanies']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('Total Candidates') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['totalCandidates']); ?>&nbsp;&nbsp;</td>
                            </tr>

                            <tr class="evenTableRow">
                                <td align="left"><?php _e('Total Job Orders') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['totalJobOrders']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=toDate" target="_blank"><?php _e('Total Submissions') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['totalSubmissions']); ?>&nbsp;&nbsp;</td>
                            </tr>

							<tr class="evenTableRow">
                                <td align="left">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=toDate" target="_blank"><?php _e('New Placements') ?></a>
                                </td>
                                <td align="right"><?php $this->_($this->statisticsData['totalPlacements']); ?>&nbsp;&nbsp;</td>
                            </tr>
                            <tr class="oddTableRow">
                                <td align="left"><?php _e('Total Contacts') ?></td>
                                <td align="right"><?php $this->_($this->statisticsData['totalContacts']); ?>&nbsp;&nbsp;</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    
	</div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>