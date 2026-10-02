<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Reports</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Report Settings</p>
            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form name="editCalendarForm" id="editCalendarForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        URL to logo image for report:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="textbox" name="reportImageURL" class="form-control form-control-sm">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        Text caption for logo:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="textbox" name="reportImageURL" class="form-control form-control-sm">
                                    </div>
                                </div>
                            </div>
                            <input type="submit" name="submit" id="submit" value="Save"  class="btn btn-sm btn-primary" />&nbsp;
                            <input type="reset"  name="reset"  id="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />&nbsp;
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
