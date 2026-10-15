<?php TemplateUtility::printHeader('Home', array('js/sweetTitles.js', 'js/dataGrid.js', 'js/dataGridFilters.js', 'js/home.js', 'js/chartjs/chart.umd.min.js', 'js/charts.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="home container-fluid py-2">
    <div id="contents">
        <header class="oc-page-header mb-2">
            <h1 class="h5 fw-semibold mb-0">Dashboard</h1>
        </header>
        <div class="row g-2">
            <div class="col-12 col-lg-4">
                <section class="card h-100" aria-labelledby="recentCallsHeading">
                    <h2 id="recentCallsHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">My Recent Calls</h2>
                    <div class="card-body p-2 overflow-auto">
                        <?php $this->dataGrid2->drawHTML(); ?>
                    </div>
                </section>
            </div>
            <div class="col-12 col-lg-4">
                <section class="card h-100" aria-labelledby="myUpcomingCalls">
                    <h2 id="myUpcomingCalls" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">My Upcoming Calls</h2>
                    <div class="card-body p-2 overflow-auto">
                        <?php echo($this->upcomingEventsFupHTML); ?>
                    </div>
                </section>
            </div>
            <div class="col-12 col-lg-4">
                <section class="card h-100" aria-labelledby="myUpcomingEvents">
                <h2 id="myUpcomingEvents" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">My Upcoming Events</h2>
                <div class="card-body p-2 overflow-auto">
                        <?php echo($this->upcomingEventsHTML); ?>
                    </div>
                </section>
            </div>
            <div class="col-12 col-xl-6">
                <section class="card h-100" aria-labelledby="recentHiresHeading">
                    <h2 id="recentHiresHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Recent Hires</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped sortable mb-0" aria-label="Recent Hires">
                            <thead>
                                <tr>
                                    <th scope="col">Name</th>
                                    <th scope="col">Company</th>
                                    <th scope="col">Recruiter</th>
                                    <th scope="col">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($this->placedRS as $index => $data): ?>
                                <tr class="<?php TemplateUtility::printAlternatingRowClass($index); ?>">
                                    <td><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($data['candidateID']); ?>" class="<?php echo($data['candidateClassName']); ?>"><?php $this->_($data['firstName']); ?> <?php $this->_($data['lastName']); ?></a></td>
                                    <td><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($data['companyID']); ?>" class="<?php echo($data['companyClassName']); ?>"><?php $this->_($data['companyName']); ?></a></td>
                                    <td><?php $this->_(StringUtility::makeInitialName($data['userFirstName'], $data['userLastName'], false, LAST_NAME_MAXLEN)); ?></td>
                                    <td><?php $this->_($data['date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (!count($this->placedRS)): ?>
                        <div class="card-body p-2 small text-body-secondary">No recent hires.</div>
                    <?php endif; ?>
                </section>
            </div>
            <div class="col-12 col-xl-6">
                <section class="card h-100" aria-labelledby="hiringOverviewHeading">
                    <h2 id="hiringOverviewHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Hiring Overview</h2>
                    <div class="card-body p-2">
                        <div class="btn-group btn-group-sm mb-2" role="group" aria-label="Hiring Overview period">
                            <?php foreach (array(DASHBOARD_GRAPH_WEEKLY => 'Weekly', DASHBOARD_GRAPH_MONTHLY => 'Monthly', DASHBOARD_GRAPH_YEARLY => 'Yearly') as $view => $label): ?>
                                <a class="btn btn-outline-secondary<?php if ($this->chartView === $view): ?> active<?php endif; ?>" href="<?php echo(CATSUtility::getIndexName()); ?>?m=home&amp;view=<?php echo($view); ?>" data-chart-range="<?php echo($view); ?>" data-chart-url="<?php echo(CATSUtility::getIndexName()); ?>?m=home&amp;a=hiringOverview&amp;view=<?php echo($view); ?>" aria-current="<?php echo($this->chartView === $view ? 'true' : 'false'); ?>"><?php echo($label); ?></a>
                            <?php endforeach; ?>
                        </div>
                        <div id="homeGraph" data-view="<?php echo($this->chartView); ?>" aria-live="polite">
                            <?php foreach ($this->hiringCharts as $view => $chart): ?>
                                <div data-chart-period="<?php echo($view); ?>" <?php if ($view !== $this->chartView): ?>hidden<?php endif; ?>>
                                    <?php echo(Charts::render('hiring-' . $view, $chart)); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            </div>
            <div class="col-12">
                <section class="card h-100" aria-labelledby="importantCandidatesHeading">
                    <h2 id="importantCandidatesHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Important Candidates (Submitted, Interviewing, Offered in Active Job Orders) - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?> (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)</h2>
                    <?php $this->dataGrid->draw(); ?>
                    <div class="d-flex flex-wrap justify-content-end align-items-center gap-2 p-2"><?php $this->dataGrid->printNavigation(false); ?> <?php $this->dataGrid->printShowAll(); ?></div>

                    <?php if (!$this->dataGrid->getNumberOfRows()): ?>
                        <div class="card-body p-2 small text-body-secondary">No important candidates.</div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
