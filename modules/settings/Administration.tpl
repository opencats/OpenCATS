<?php /* $Id: Administration.tpl 3722 2007-11-27 21:49:36Z andrew $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
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
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Settings");?>: <?php echo __("Administration");?></h2></td>
                </tr>
            </table>

                        <p class="noteUnsized"><?php echo __("Site Management");?></p>

                        <table class="searchTable" width="100%">
                            <tr>
                                <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                    <?php if ($this->careerPortalUnlock): ?>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings"><?php echo __("Careers Website");?></a>
                                    <?php else: ?>
                                    <a href="http://www.catsone.com/?a=careerswebsite"><b><?php echo __("Careers Website");?></b></a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo __("Configure your website where applicants can apply and post their resumes for your jobs.");?>
                                </td>
                            </tr>
                           <tr>
                                <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=siteName">
                                        <?php echo __("Change Site Details");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Change the site details such as site name and institution configuration.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers">
                                        <?php echo __("User Management");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Add, edit and delete users for your site.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=loginActivity">
                                        <?php echo __("Login Activity");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Shows you the login history for your site.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailSettings">
                                        <?php echo __("General E-Mail Configuration");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Configure E-Mail preferences such as return address and when E-Mails are sent.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates">
                                        <?php echo __("E-Mail Template Configuration");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Configure E-Mail templates for your site.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=localization">
                                        <?php echo __("Localization");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Change how addresses and times are displayed and behave for different regions.");?>
                                </td>
                            </tr>
                            <tr <?php if (!$this->totalCandidates): ?>style="background-color: #DAE3F7;"<?php endif; ?>>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=import">
                                        <?php if (!$this->totalCandidates): ?><b><?php endif; ?><?php echo __("Data Import");?><?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                    </a>
                                </td>
                                <td>
                                    <?php if (!$this->totalCandidates): ?><b><?php endif; ?><?php echo __("Import resumes, candidates, companies or contacts from files on your computer.");?><?php if (!$this->totalCandidates): ?></b><?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(E::routeHref('excel/import'));?>">
                                        <?php echo __("Data Import Excel");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Import resumes, candidates, companies or contacts from files on your computer.");?>
                                </td>
                            </tr>                            
                            
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=createBackup">
                                        <?php echo __("Site Backup");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Produce a downloadable backup with all the content in your site.");?>
                                </td>
                            </tr>
                        </table>
                        <br />

                        <p class="noteUnsized"><?php echo __("Feature Settings");?></p>

                        <table class="searchTable" width="100%">
                            <!--<tr>
                                <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=reports">
                                        <?php echo __("Reports");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Configure how your site's reports look by default.");?>
                                </td>
                            </tr>-->
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=eeo">
                                        <?php echo __("EEO / EOC Support");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Enable and configure EEO / EOC compliance tracking.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=tags">
                                        <?php echo __("Configure Tags");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Add/Remove tags, description for tags");?>
                                </td>
                            </tr>
                        </table>
                        <br />

                        <p class="noteUnsized"><?php echo __("GUI Customization");?></p>

                        <table class="searchTable" width="100%">
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar">
                                        <?php echo __("Customize Calendar");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Change calendar settings, such as the duration of a work day.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="images/bullet_black.gif" alt="" />
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeExtraFields">
                                        <?php echo __("Customize Extra Fields");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Add, rename, and remove extra text fields from various data types.");?>
                                </td>
                            </tr>
                        </table>
                        <br />

                        <?php if ($this->systemAdministration): ?>
                            <p class="noteUnsized"><?php echo __("System");?></p>

                            <table class="searchTable" width="100%">
                                <!--<tr>
                                    <td width="230">
                                        <img src="images/bullet_black.gif" alt="" border="0" />
                                        <?php echo __("Scheduler");?>
                                    </td>
                                    <td>
                                        <i><?php echo __("Change how CATS interacts with the server to schedule tasks.");?></i>
                                    </td>
                                </tr>-->
                                <tr>
                                    <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=passwords">
                                            <?php echo __("Passwords");?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo __("Change how CATS stores user passwords, and how users can retrieve them.");?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=newVersionCheck">
                                            <?php echo __("New Version Check");?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo __("Change how CATS checks periodically for new versions.");?>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="230">
                                    <img src="images/bullet_black.gif" alt="" />
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration&amp;s=systemInformation">
                                            <?php echo __("System Information");?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php echo __("View information about this CATS installation.");?>
                                    </td>
                                </tr>
                            </table>
                        <?php endif; ?>

                        <?php if (!empty($this->extraSettings)): ?>
                            <br />

                            <p class="noteUnsized"><?php echo __("Other Settings");?></p>

                            <table class="searchTable" width="100%">
                                <?php foreach ($this->extraSettings as $setting): ?>
                                    <tr>
                                        <td width="230">
                                            <img src="images/bullet_black.gif" alt="" />
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
<?php TemplateUtility::printFooter(); ?>
