<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/eeo.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Equal Employment Opportunity Tracking Settings</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form name="EEOForm" id="EEOForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=eeo" method="post">
                            <input type="hidden" name="postback" value="postback" />

                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="enabled">Enable Candidate EEO Tracking:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="enabled" id="enabled"<?php if ($this->EEOSettingsRS['enabled'] == '1'): ?> checked<?php endif; ?> onchange="checkUnckeckEEOSettings(this.checked);" class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="genderTracking">Track Gender:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="genderTracking" id="genderTracking"<?php if ($this->EEOSettingsRS['genderTracking'] == '1'): ?> checked<?php endif; ?> onchange="if (this.checked) document.getElementById('enabled').checked=true;" class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="ethnicTracking">Track Ethnic Background:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="ethnicTracking" id="ethnicTracking"<?php if ($this->EEOSettingsRS['ethnicTracking'] == '1'): ?> checked<?php endif; ?> onchange="if (this.checked) document.getElementById('enabled').checked=true;" class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="veteranTracking">Track Veteran Status:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="veteranTracking" id="veteranTracking"<?php if ($this->EEOSettingsRS['veteranTracking'] == '1'): ?> checked<?php endif; ?> onchange="if (this.checked) document.getElementById('enabled').checked=true;" class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="disabilityTracking">Track Disability Status:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="disabilityTracking" id="disabilityTracking"<?php if ($this->EEOSettingsRS['disabilityTracking'] == '1'): ?> checked<?php endif; ?> onchange="if (this.checked) document.getElementById('enabled').checked=true;" class="form-check-input">
                                    </div>
                                </div>
                            </div>
                            <input type="submit" value="Save Settings"  class="btn btn-sm btn-primary" />&nbsp;
                            <br />
                            <br />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
