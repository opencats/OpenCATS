<?php TemplateUtility::printHeader('Reports'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Reports: New Data Items</h1>
            </header>

            <div class="row g-2">
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Today</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="Today statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersToday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesToday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesToday']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=today" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsToday']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=today" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsToday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsToday']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Yesterday</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="Yesterday statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersYesterday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesYesterday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesYesterday']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=yesterday" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsYesterday']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=yesterday" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsYesterday']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsYesterday']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">This Week</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="This Week statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersThisWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesThisWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesThisWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisWeek" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsThisWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisWeek" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsThisWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsThisWeek']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Last Week</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="Last Week statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersLastWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesLastWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesLastWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastWeek" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsLastWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastWeek" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsLastWeek']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsLastWeek']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">This Month</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="This Month statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersThisMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesThisMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesThisMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisMonth" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsThisMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisMonth" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsThisMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsThisMonth']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Last Month</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="Last Month statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersLastMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesLastMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesLastMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastMonth" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsLastMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastMonth" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsLastMonth']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsLastMonth']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">This Year</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="This Year statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersThisYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesThisYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesThisYear']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=thisYear" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsThisYear']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=thisYear" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsThisYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsThisYear']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Last Year</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="Last Year statistics">
                                <tbody>
                            <tr>
                                <td>New Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['jobOrdersLastYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['candidatesLastYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['companiesLastYear']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=lastYear" target="_blank">New Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['submissionsLastYear']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=lastYear" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['placementsLastYear']); ?></td>
                            </tr>
                            <tr>
                                <td>New Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['contactsLastYear']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
                <div class="col-12 col-md-6 col-xl-4">
                    <section class="card h-100">
                        <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">To Date</h2>
                        <div class="table-responsive">
                            <table class="table table-sm table-striped mb-0" aria-label="To Date statistics">
                                <tbody>
                            <tr>
                                <td>Total Companies</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalCompanies']); ?></td>
                            </tr>
                            <tr>
                                <td>Total Candidates</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalCandidates']); ?></td>
                            </tr>
                            <tr>
                                <td>Total Job Orders</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalJobOrders']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showSubmissionReport&amp;period=toDate" target="_blank">Total Submissions</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalSubmissions']); ?></td>
                            </tr>
                            <tr>
                                <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=showPlacementReport&amp;period=toDate" target="_blank">New Placements</a>
                                </td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalPlacements']); ?></td>
                            </tr>
                            <tr>
                                <td>Total Contacts</td>
                                <td class="text-end"><?php $this->_($this->statisticsData['totalContacts']); ?></td>
                            </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </div>
        </div>
</main>
<?php TemplateUtility::printFooter(); ?>
