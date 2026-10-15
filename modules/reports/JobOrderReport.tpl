<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Reports: Job Order Report</h1>
            </header>

            <p class="small text-body-secondary mb-2">Generate a job order report.</p>

            <form name="jobOrderReportForm" id="jobOrderReportForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="reports">
                <input type="hidden" name="a" value="generateJobOrderReportPDF">

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="siteNameLabel" for="siteName">Company Name:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="siteName" id="siteName" value="<?php $this->_($this->reportParameters['siteName']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="companyNameLabel" for="companyName">Company:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="companyName" id="companyName" value="<?php $this->_($this->reportParameters['companyName']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="jobOrderNameLabel" for="jobOrderName">Position (Title):</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="jobOrderName" id="jobOrderName" value="<?php $this->_($this->reportParameters['jobOrderName']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="periodLineLabel" for="periodLine">Job Order Period:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="periodLine" id="periodLine" value="<?php $this->_($this->reportParameters['periodLine']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="accountManagerLabel" for="accountManager">Account Manager:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="accountManager" id="accountManager" value="<?php $this->_($this->reportParameters['accountManager']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="recruiterLabel" for="recruiter">Recruiter:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="recruiter" id="recruiter" value="<?php $this->_($this->reportParameters['recruiter']); ?>" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-body p-2 mb-2">
                    <input type="hidden" name="dataSet" id="dataSet" value="0,0,0,0">
                    <script type="text/javascript">
                        function setDataSet()
                        {
                            document.getElementById('dataSet').value =
                                document.getElementById('dataSet1').value + ',' +
                                document.getElementById('dataSet2').value + ',' +
                                document.getElementById('dataSet3').value + ',' +
                                document.getElementById('dataSet4').value;
                        }
                    </script>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="dataSet1Label" for="dataSet1">Candidates Screened:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="dataSet1" id="dataSet1" value="<?php $this->_($this->reportParameters['dataSet1']); ?>" onchange="setDataSet();" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="dataSet2Label" for="dataSet2">Candidates Submitted:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="dataSet2" id="dataSet2" value="<?php $this->_($this->reportParameters['dataSet2']); ?>" onchange="setDataSet();" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="dataSet3Label" for="dataSet3">Candidates Interviewed:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="dataSet3" id="dataSet3" value="<?php $this->_($this->reportParameters['dataSet3']); ?>" onchange="setDataSet();" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="dataSet4Label" for="dataSet4">Candidates Placed:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <input type="text" class="form-control form-control-sm" name="dataSet4" id="dataSet4" value="<?php $this->_($this->reportParameters['dataSet4']); ?>" onchange="setDataSet();" /><span class="text-danger" title="Required">*</span>
                            </div>
                        </div>
                    </div>
                </div>

                <script type="text/javascript">setDataSet();</script>

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4 col-lg-3">
                            <label class="form-label small mb-0" id="notesLabel" for="notes">Misc. Notes:</label>
                        </div>
                        <div class="col-sm-8 col-lg-9">
                            <div class="d-flex align-items-center gap-1">
                                <textarea class="form-control form-control-sm" name="notes" id="notes" rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="submit" class="btn btn-primary btn-sm" name="submit" value="Generate Report" />
                <input type="reset"  class="btn btn-secondary btn-sm" name="reset"  value="Reset" />
                
                <!-- IE PDF Hack -->
                <input type="hidden" name="ext" value=".pdf" />
            </form>

            <script type="text/javascript">
                document.jobOrderReportForm.siteName.focus();
            </script>
        </div>
</main>
<?php TemplateUtility::printFooter(); ?>
