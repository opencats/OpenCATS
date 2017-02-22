<?php get_header(); ?>
<style>
#profButton {
    background: #E7EFFF url(images/profButton.jpg);
    width: 169px;
    height: 34px;
    cursor: pointer;
    margin-top: 20px;
}
#profButton:hover {
    background: #E7EFFF url(images/profButton-o.jpg);
}
</style>
<div class="content">


                        <div class="hpanel">
                            <div class="panel-heading">
                                Site Management
                            </div>
                            <div class="panel-body">
                                <dl class="dl-horizontal">
                                    <dt>
                                        <?php if ($careerPortalUnlock): ?>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings">Careers Website</a>
                                        <?php else: ?>
                                        <a href="http://www.catsone.com/?a=careerswebsite"><b>Careers Website</b></a>
                                        <?php endif; ?>
                                    </dt>
                                    <dd>
                                        Configure your website where applicants can apply and post their resumes for your jobs.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=siteName">
                                            Change Site Details
                                        </a>
                                    </dt>
                                    <dd>
                                        Change the site details such as site name and institution configuration.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers">
                                            User Management
                                        </a>
                                    </dt>
                                    <dd>
                                        Add, edit and delete users for your site.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=loginActivity">
                                            Login Activity
                                        </a>
                                    </dt>
                                    <dd>
                                        Shows you the login history for your site.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailSettings">
                                            E-Mail Configuration
                                        </a>
                                    </dt>
                                    <dd>
                                        Configure E-Mail preferences such as return address and when E-Mails are sent.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates">
                                            E-Mail Templates
                                        </a>
                                    </dt>
                                    <dd>
                                        Configure E-Mail templates for your site.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=localization">
                                            Localization
                                        </a>
                                    </dt>
                                    <dd>
                                        Change how addresses and times are displayed and behave for different regions.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=import">
                                            <?php if (!$totalCandidates): ?><b><?php endif; ?>Data Import<?php if (!$totalCandidates): ?></b><?php endif; ?>
                                        </a>
                                    </dt>
                                    <dd>
                                        <?php if (!$totalCandidates): ?><b><?php endif; ?>Import resumes, candidates, companies or contacts from files on your computer.<?php if (!$totalCandidates): ?></b><?php endif; ?>
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=createBackup">
                                            Site Backup
                                        </a>
                                    </dt>
                                    <dd>
                                        Produce a downloadable backup with all the content in your site.
                                    </dd>
                                </dl>
                            </div>
                        </div>


                        <div class="hpanel">
                            <div class="panel-heading">
                                Feature Settings
                            </div>
                            <div class="panel-body">

                                <dl class="dl-horizontal">
                            <!--
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=reports">
                                        Reports
                                    </a>
                                </dt>
                                <dd>
                                    Configure how your site's reports look by default.
                                </dd>
                            -->
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=eeo">
                                        EEO / EOC Support
                                    </a>
                                </dt>
                                <dd>
                                    Enable and configure EEO / EOC compliance tracking.
                                </dd>
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=tags">
                                        Configure Tags
                                    </a>
                                </dt>
                                <dd>
                                    Add/Remove tags, description for tags
                                </dd>
                            </dl>
                        </div>
                    </div>

                        <div class="hpanel">
                            <div class="panel-heading">
                                GUI Customization
                            </div>
                            <div class="panel-body">

                        <dl class="dl-horizontal">
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar">
                                        Customize Calendar
                                    </a>
                                </dt>
                                <dd>
                                    Change calendar settings, such as the duration of a work day.
                                </dd>
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeExtraFields">
                                        Customize Extra Fields
                                    </a>
                                </dt>
                                <dd>
                                    Add, rename, and remove extra text fields from various data types.
                                </dd>
                                <dt>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=themeSettings">
                                        Theme Settings
                                    </a>
                                </dt>
                                <dd>
                                    Change OpenCATS theme settings, such as switching between OpenCATS 2016 and  OpenCATS 2017 theme
                                </dd>
                        </dl>
                        </div>
                    </div>

                        <?php if ($systemAdministration): ?>
                        <div class="hpanel">
                            <div class="panel-heading">
                                System
                            </div>
                            <div class="panel-body">

                            <dl class="dl-horizontal">
                                <!--
                                    <dt>
                                        <img src="images/bullet_black.gif" alt="" border="0" />
                                        Scheduler
                                    </dt>
                                    <dd>
                                        <i>Change how CATS interacts with the server to schedule tasks.</i>
                                    </dd>
                                -->
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=passwords">
                                            Passwords
                                        </a>
                                    </dt>
                                    <dd>
                                        Change how CATS stores user passwords, and how users can retrieve them.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=newVersionCheck">
                                            New Version Check
                                        </a>
                                    </dt>
                                    <dd>
                                        Change how CATS checks periodically for new versions.
                                    </dd>
                                    <dt>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=systemInformation">
                                            System Information
                                        </a>
                                    </dt>
                                    <dd>
                                        View information about this CATS installation.
                                    </dd>
                            </dl>
                        </div>
                    </div>
                        <?php endif; ?>

                        <?php if (!empty($extraSettings)): ?>
                        <div class="hpanel">
                            <div class="panel-heading">
                                Other Settings
                            </div>
                            <div class="panel-body">

                            <dl class="dl-horizontal">
                                <?php foreach ($extraSettings as $setting): ?>
                                    <dt>
                                        <a href="<?php echo($setting[1]); ?>"><?php $_($setting[0]); ?></a>
                                    </dt>
                                    <dd>
                                        <?php $_($setting[3]); ?>
                                    </dd>
                                <?php endforeach; ?>
                            </dl>
                        </div>
                    </div>
                        <?php endif; ?>
                    </div>
<?php get_footer(); ?>
