<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Change Site Name</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeSiteNameForm" onsubmit="return checkSiteNameForm(document.changeSiteNameForm);" method="post" autocomplete="off">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeSiteName" />
                            Current site name: <?php echo($_SESSION['CATS']->getSiteName())?><br />
                            <br />
                            <label id="siteNameLabel" for="siteName" class="form-label small mb-0">New Site Name:</label>
                            <br />
                            <input type="text" name="siteName" id="siteName" value="<?php echo($_SESSION['CATS']->getSiteName())?>"  class="form-control form-control-sm" /><br /><br />
                            <input type="submit" name="save" value="Save"  class="btn btn-sm btn-primary" />
                            <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';"  class="btn btn-sm btn-outline-secondary" />
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
