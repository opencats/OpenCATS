<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: My Profile</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Profile</p>

            <?php if ($this->isDemoUser): ?>
                Note that as a demo user, you do not have privileges to modify any settings.
                <br /><br />
            <?php endif; ?>

            <div class="list-group mb-3">
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($this->userID); ?>&amp;privledged=false">View Profile
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    View your current profile to verify your information is correct.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=changePassword">Change Password
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Change your CATS login password.
                                </div>
                            </div></div>
                            <!--<div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=notificationOptions">Change Notification Options
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Change how CATS notifies you of new events.
                                </div>
                            </div></div>-->
                        </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
