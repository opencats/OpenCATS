<?php /* $Id: Administration.tpl 3722 2007-11-27 21:49:36Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>

<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">


            <table width="100%">
                <tr>
                    <td width="100%">


                        <p class="noteUnsized">Administration Management</p>

                        <table class="searchTable" width="100%">
                            <tr>
                                <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=siteName">
                                        Change Site Details
                                    </a>
                                </td>
                                <td>
                                    Change the site details such as site name and institution configuration.
                                </td>
                            </tr>
							<tr>
                                <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings">Applicant Job Portal</a>
                                </td>
                                <td>
                                    Administer the look and feel of the Applicant Job Portal.
                                </td>
                            </tr>

                            <tr>
                                <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=downloads">
                                        Add-ins and Downloadables
                                    </a>

                                </td>
                                <td>
                                    We currently have a couple available. More coming soon!
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers">
                                        User Management
                                    </a>
                                </td>
                                <td>
                                    Add, edit and delete users for your site.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=loginActivity">
                                        Login Activity
                                    </a>
                                </td>
                                <td>
                                    Shows you the login history for your site.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailSettings">
                                        General E-Mail Configuration
                                    </a>
                                </td>
                                <td>
                                    Configure E-Mail preferences such as return address and when E-Mails are sent.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates">
                                        E-Mail Template Configuration
                                    </a>
                                </td>
                                <td>
                                    Configure E-Mail templates for your site.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=localization">
                                        Localization
                                    </a>
                                </td>
                                <td>
                                    Change how addresses and times are displayed and behave for different regions.
                                </td>
                            </tr>
                            <tr <?php if (!$this->totalCandidates): ?>style="background-color: #DAE3F7;"<?php endif; ?>>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=import">
                                        <?php if (!$this->totalCandidates): ?><b><?php endif; ?>Data Import<?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!$this->totalCandidates): ?><b><?php endif; ?>Import resumes, candidates, companies or contacts from files on your computer.<?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=createBackup">
                                        Site Backup
                                    </a>
                                </td>
                                <td>
                                    Produce a downloadable backup with all the content in your site.
                                </td>
                            </tr>
							<tr>
                                <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=reports">
                                        Reports
                                    </a>
                                </td>
                                <td>
                                    Configure how your site's reports look by default.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=eeo">
                                        EEO / EOC Support
                                    </a>
                                </td>
                                <td>
                                    Enable and configure EEO / EOC compliance tracking.
                                </td>
                            </tr>
							<tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar">
                                        Customize Calendar
                                    </a>
                                </td>
                                <td>
                                    Change calendar settings, such as the duration of a work day.
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/2dot1c.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeExtraFields">
                                        Customize Extra Fields
                                    </a>
                                </td>
                                <td>
                                    Add, rename, and remove extra text fields from various data types.
                                </td>
                            </tr>
							<tr>
                                    <td width="230">
                                        <img src="images/2dot1c.gif" alt="" border="0" />
                                        Scheduler
                                    </td>
                                    <td>
                                        <i>Change how OSATS interacts with the server to schedule tasks.</i>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=passwords">
                                            Passwords
                                        </a>
                                    </td>
                                    <td>
                                        Change how OSATS stores user passwords. (Working on fixing this-Jamin)
                                    </td>
                                </tr>
                                <tr>
                                    <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=newVersionCheck">
                                            New Version Check
                                        </a>
                                    </td>
                                    <td>
                                        Change how CATS checks periodically for new versions.
                                    </td>
                                </tr>
                                <tr>
                                    <td width="230">
                                    <img src="images/2dot1c.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=systemInformation">
                                            System Information
                                        </a>
                                    </td>
                                    <td>
                                        View information about this CATS installation.
                                    </td>
                                </tr>
                        </table>
                        <br />


                        <?php if (!empty($this->extraSettings)): ?>
                            <br />

                            <p class="noteUnsized">Other Settings</p>

                            <table class="searchTable" width="100%">
                                <?php foreach ($this->extraSettings as $setting): ?>
                                    <tr>
                                        <td width="230">
                                            <img src="images/2dot1c.gif" alt="" />
                                            <a href="<?php echo($setting[1]); ?>"><?php $this->_($setting[0]); ?></a>
                                        </td>
                                        <td>
                                            <?php $this->_($setting[3]); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>
                        <?php endif; ?>
                       <br />
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>