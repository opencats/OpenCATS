<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

                        <p class="h6 bg-secondary-subtle rounded p-2 mb-0">Site Management</p>

                        <div class="list-group mb-3">
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <?php if ($this->careerPortalUnlock): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings">Careers Website</a>
                                    <?php else: ?>
                                    <a href="http://www.catsone.com/?a=careerswebsite"><b>Careers Website</b></a>
                                    <?php endif; ?>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Configure your website where applicants can apply and post their resumes for your jobs.
                                </div>
                            </div></div>
                           <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=siteName">
                                        Change Site Details
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Change the site details such as site name and institution configuration.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers">
                                        User Management
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Add, edit and delete users for your site.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=loginActivity">
                                        Login Activity
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Shows you the login history for your site.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailSettings">
                                        General E-Mail Configuration
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Configure E-Mail preferences such as return address and when E-Mails are sent.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates">
                                        E-Mail Template Configuration
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Configure E-Mail templates for your site.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=localization">
                                        Localization
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Change how addresses and times are displayed and behave for different regions.
                                </div>
                            </div></div>
                            <div class="list-group-item <?php if (!$this->totalCandidates): ?>bg-primary-subtle<?php endif; ?>"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=import">
                                        <?php if (!$this->totalCandidates): ?><b><?php endif; ?>Data Import<?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    <?php if (!$this->totalCandidates): ?><b><?php endif; ?>Import resumes, candidates, companies or contacts from files on your computer.<?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=createBackup">
                                        Site Backup
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Produce a downloadable backup with all the content in your site.
                                </div>
                            </div></div>
                        </div>
                        <br />

                        <p class="h6 bg-secondary-subtle rounded p-2 mb-0">Feature Settings</p>

                        <div class="list-group mb-3">
                            <!--<div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=reports">
                                        Reports
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Configure how your site's reports look by default.
                                </div>
                            </div></div>-->
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=eeo">
                                        EEO / EOC Support
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Enable and configure EEO / EOC compliance tracking.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=tags">
                                        Configure Tags
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Add/Remove tags, description for tags
                                </div>
                            </div></div>
                        </div>
                        <br />

                        <p class="h6 bg-secondary-subtle rounded p-2 mb-0">GUI Customization</p>

                        <div class="list-group mb-3">
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar">
                                        Customize Calendar
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Change calendar settings, such as the duration of a work day.
                                </div>
                            </div></div>
                            <div class="list-group-item"><div class="row g-2">
                                <div class="col-sm-4 col-lg-3 fw-semibold">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeExtraFields">
                                        Customize Extra Fields
                                    </a>
                                </div>
                                <div class="col-sm-8 col-lg-9">
                                    Add, rename, and remove extra text fields from various data types.
                                </div>
                            </div></div>
                        </div>
                        <br />

                        <?php if ($this->systemAdministration): ?>
                            <p class="h6 bg-secondary-subtle rounded p-2 mb-0">System</p>

                            <div class="list-group mb-3">
                                <!--<div class="list-group-item"><div class="row g-2">
                                    <div class="col-sm-4 col-lg-3 fw-semibold">
                                        Scheduler
                                    </div>
                                    <div class="col-sm-8 col-lg-9">
                                        <i>Change how CATS interacts with the server to schedule tasks.</i>
                                    </div>
                                </div></div>-->
                                <div class="list-group-item"><div class="row g-2">
                                    <div class="col-sm-4 col-lg-3 fw-semibold">
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=passwords">
                                            Passwords
                                        </a>
                                    </div>
                                    <div class="col-sm-8 col-lg-9">
                                        Change how CATS stores user passwords, and how users can retrieve them.
                                    </div>
                                </div></div>
                                <div class="list-group-item"><div class="row g-2">
                                    <div class="col-sm-4 col-lg-3 fw-semibold">
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=newVersionCheck">
                                            New Version Check
                                        </a>
                                    </div>
                                    <div class="col-sm-8 col-lg-9">
                                        Change how CATS checks periodically for new versions.
                                    </div>
                                </div></div>
                                <div class="list-group-item"><div class="row g-2">
                                    <div class="col-sm-4 col-lg-3 fw-semibold">
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=systemInformation">
                                            System Information
                                        </a>
                                    </div>
                                    <div class="col-sm-8 col-lg-9">
                                        View information about this CATS installation.
                                    </div>
                                </div></div>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($this->extraSettings)): ?>
                            <br />

                            <p class="h6 bg-secondary-subtle rounded p-2 mb-0">Other Settings</p>

                            <div class="list-group mb-3">
                                <?php foreach ($this->extraSettings as $setting): ?>
                                    <div class="list-group-item"><div class="row g-2">
                                        <div class="col-sm-4 col-lg-3 fw-semibold">
                                            <a href="<?php echo($setting[1]); ?>"><?php $this->_($setting[0]); ?></a>
                                        </div>
                                        <div class="col-sm-8 col-lg-9">
                                            <?php $this->_($setting[3]); ?>
                                        </div>
                                    </div></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
