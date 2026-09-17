<?php TemplateUtility::printHeader('EEO Reports', array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Reports: EEO Report (Work In Progress)</h1>
            </header>

            <p class="small text-body-secondary mb-2">Generate a report on Equal Employment Opportunity Statistics.</p>

            <form name="jobOrderReportForm" id="jobOrderReportForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="reports">
                <input type="hidden" name="a" value="generateEEOReportPreview">
                
                <div class="row g-2 align-items-start">
                    <div class="col-12 col-lg-4">
                        <div class="card card-body p-2">
                            <fieldset class="mb-2">
                                <legend id="siteNameLabel" class="form-label small fw-semibold">Date Range:</legend>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="period" id="period_all" value="all" <?php if ($this->modePeriod == 'all'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="period_all">All time</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="period" id="period_month" value="month" <?php if ($this->modePeriod == 'month'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="period_month">Last Month</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="period" id="period_week" value="week" <?php if ($this->modePeriod == 'week'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="period_week">Last Week</label>
                                </div>
                            </fieldset>
                            <fieldset class="mb-2">
                                <legend id="companyNameLabel" class="form-label small fw-semibold">Status:</legend>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_all" value="all" <?php if ($this->modeStatus == 'all'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="status_all">All</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_placed" value="placed" <?php if ($this->modeStatus == 'placed'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="status_placed">Placed</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_rejected" value="rejected" <?php if ($this->modeStatus == 'rejected'): ?>checked<?php endif; ?>>
                                    <label class="form-check-label" for="status_rejected">Not in Consideration</label>
                                </div>
                            </fieldset>
                            <div><input type="submit" class="btn btn-primary btn-sm" name="submit" value="Preview Report"></div>
                        </div>
                    </div>
                    <?php if (isset($this->EEOReportStatistics)): ?>
                        <div class="col-12 col-lg-8">
                            <section class="card">
                                <h2 class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold mb-0">Report Preview:</h2>
                                <div class="card-body p-2">
                                    <?php if ($this->EEOSettingsRS['ethnicTracking'] == 1): ?>
                                        <section class="mb-3">
                                            <h3 class="h6">Candidates by Ethnic Types:</h3>
                                            <div class="row g-2">
                                                <div class="col-12 col-xl-7 overflow-auto">
                                                    <img src="<?php echo($this->urlEthnicGraph); ?>" alt="Candidates by Ethnic Types:">
                                                </div>
                                                <div class="col-12 col-xl-5">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped mb-0" aria-label="Candidates by Ethnic Types:">
                                                            <tbody>
                                                                <?php foreach ($this->EEOReportStatistics['rsEthnicStatistics'] as $data): ?>
                                                                    <tr>
                                                                        <th scope="row" class="fw-normal"><?php $this->_($data['EEOEthnicType']); ?>:</th>
                                                                        <td class="text-end"><?php $this->_($data['numberOfCandidates']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                    <?php if ($this->EEOSettingsRS['veteranTracking'] == 1): ?>
                                        <section class="mb-3">
                                            <h3 class="h6">Candidates by Veteran Status:</h3>
                                            <div class="row g-2">
                                                <div class="col-12 col-xl-7 overflow-auto">
                                                    <img src="<?php echo($this->urlVeteranGraph); ?>" alt="Candidates by Veteran Status:">
                                                </div>
                                                <div class="col-12 col-xl-5">
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-striped mb-0" aria-label="Candidates by Veteran Status:">
                                                            <tbody>
                                                                <?php foreach ($this->EEOReportStatistics['rsVeteranStatistics'] as $data): ?>
                                                                    <tr>
                                                                        <th scope="row" class="fw-normal"><?php $this->_($data['EEOVeteranType']); ?>:</th>
                                                                        <td class="text-end"><?php $this->_($data['numberOfCandidates']); ?></td>
                                                                    </tr>
                                                                <?php endforeach; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </section>
                                    <?php endif; ?>
                                    <div class="row g-2">
                                        <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                                            <div class="col-12 col-xl-6 overflow-auto">
                                                <img src="<?php echo($this->urlGenderGraph); ?>" alt="Candidates by Gender">
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                                            <div class="col-12 col-xl-6 overflow-auto">
                                                <img src="<?php echo($this->urlDisabilityGraph); ?>" alt="Candidates by Disability Status">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </section>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <script type="text/javascript">
                document.jobOrderReportForm.siteName.focus();
            </script>
        </div>
</main>
<?php TemplateUtility::printFooter(); ?>
