<?php /* $Id: Professional.tpl 3678 2007-11-21 23:10:42Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<script>
<?php echo $this->webForm->getJavaScript(); ?>
</script>
<style>
<?php echo $this->webForm->getCSS(); ?>
td.featuresRow {
    padding-bottom: 20px;
    font-size: 14px;
}
span.titleText {
    font-weight: bold;
    color: #473F1F;
}
</style>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>
        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" alt="Settings" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Professional Membership</h2></td>
                </tr>
            </table>
            <p class="note">Account Settings</p>

    <?php if ($this->message != ''): ?>
            <div style="padding: 15px; border: 1px solid black; margin: 20px; font-size: 20px; font-weight: bold; color: #800000; text-align: center;">
            <?php echo $this->message; ?>
            </div>
    <?php endif; ?>



    <?php if ($this->upgradeStatus): ?>
            <table>
                <tr>
                    <td valign="top" align="left">
                        <img src="images/professionalLeft.jpg" border="0" />
                    </td>
                    <td valign="top" align="left" style="padding-left: 20px;">
                        <span style="font-size: 22px; font-weight: bold;">
                        You are now registered as a Professional member
                        </span>
                        <p />
                        <span style="font-size: 14px; color: #000000;">
                        Welcome <span style="font-size: 12px; font-weight: bold;"><?php echo $this->license->getName(); ?></span>!
                        As a Professional member, you're entitled to the latest features and releases of CATS,
                        helpful support services, and plug-ins to make finding and managing talent easier than ever.
                        <br /><br />
                        For more information on getting started and your Professional membership, please visit the CATS official
                        Professional membership website at <a href="http://www.catsone.com/professional" target="_blank">www.catsone.com/<b>professional</b></a>.
                        From there, you'll find information about the latest new releases and all the tools and resources
                        available to you.
                        <br /><br />
                        <b>All the professional features in CATS have been unlocked and are ready-to-use.</b>
                        <br /><br />
                        <b>Number of seats/user licenses: </b><?php echo ucfirst(StringUtility::cardinal($this->license->getNumberOfSeats())); ?>
                        <br />
                        <b>Service valid until:</b> <?php echo date('F dS, Y', $this->license->getExpirationDate()); ?>

                        </span>
                    </td>
                </tr>
            </table>



    <?php elseif (LicenseUtility::isProfessional()): ?>
            <table>
                <tr>
                    <td valign="top" align="left">
                        <img src="images/professionalLeft.jpg" border="0" />
                    </td>
                    <td valign="top" align="left" style="padding-left: 20px;">
                        <span style="font-size: 22px; font-weight: bold;">
                        You are a registered CATS Professional user
                        </span>
                        <br /><br />
                        <span style="font-size: 14px; color: #000000;">
                        All the features available to you have been unlocked are are ready-to-use. To download
                        plug-ins, get the latest information about Professional or to upgrade or renew your
                        Professional account, visit our CATS Professional website at
                        <a href="http://www.catsone.com/professional">http://www.catsone.com/<b>professional</b></a>.
                        </span>
                        <p />
                        <b>Registered to:</b>
                        <br />
                        <?php echo LicenseUtility::getName(); ?>
                        <p />
                        <b>Valid until:</b>
                        <br />
                        <?php echo date('F j, Y', LicenseUtility::getExpirationDate()); ?>
                        <p />
                        <b>User licenses/seats:</b>
                        <br />
                        <?php echo ucfirst(StringUtility::cardinal(LicenseUtility::getNumberOfSeats())); ?> (<?php echo LicenseUtility::getNumberOfSeats(); ?>)
                        </span>
                    </td>
                </tr>
            </table>

            <p />

            <table cellspacing="15">
                <tr>
                    <td width="50%" valign="top">
                        <div style="font-size: 14px; font-weight: bold; background-color: #666666; padding: 2px; color: white; margin-bottom: 5px;">
                        UPGRADING / RENEWAL
                        </div>
                        <div style="font-size: 12px; color: #000000; text-align: justify;">
                        Need to renew or upgrade your licenses? Visit our official <a href="http://www.catsone.com/professional" target="_blank">Professional</a>
                        membership site to get a new license key and enter it here:
                        <table>
                            <tr>
                                <td valign="top" align="left">
                                    <form id="keyEntry" name="keyEntry" method="get" action="<?php echo CATSUtility::getIndexName(); ?>">
                                    <input type="hidden" name="m" value="settings" />
                                    <input type="hidden" name="a" value="professional" />
                                    <p />
                                    <?php echo $this->webForm->getForm(); ?>
                                    <p />
                                    <?php echo $this->webForm->getButton('Upgrade / Renew', 'keyEntry'); ?>
                                    </form>
                                </td>
                            </tr>
                        </table>
                        </div>
                    </td>
                    <td width="50%" valign="top">
                        <div style="font-size: 14px; font-weight: bold; background-color: #666666; padding: 2px; color: white; margin-bottom: 5px;">
                        PROFESSIONAL LINKS & RESOURCES
                        </div>
                        <div style="font-size: 12px; color: #000000; text-align: justify;">
                        For more information on getting started and your Professional membership, please visit the CATS official
                        Professional membership website at <a href="http://www.catsone.com/professional" target="_blank">www.catsone.com/<b>Professional</b></a>.
                        From there, you'll find information about the latest new releases and all the tools and resources
                        available to you.
                        </div>
                    </td>

                </tr>
            </table>



    <?php else: ?>
        <center>
        <table>
            <tr>
                <td valign="top" align="left" style="padding-right: 10px;">
                    <img src="images/professionalLeft.jpg" border="0" />
                </td>
                <td valign="top" align="left">
                    <table cellpadding="0" cellspacing="0" border="0" width="600">
                        <tr>
                            <td width="50%" valign="top" align="left" style="padding-right: 20px; padding-bottom: 10px;">
                                <div style="width: 100%; padding: 5px; background-color: #D5E4F6; font-size: 14px;">
                                    <b>Plug-ins and Add-ons</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    Import candidates from Monster, HotJobs and others. Convert your resume documents
                                    directly into candidates.
                                    </span>
                                </div>
                            </td>
                            <td width="50%" align="left" valign="top">
                                <div style="width: 100%; padding: 5px; background-color: #E7F1FC; font-size: 14px;">
                                    <b>No Hassles</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    Run CATS on your server. We'll support and manage it, keeping it up to date
                                    with the latest patches and features available.
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" valign="top" align="left" style="padding-right: 20px; padding-bottom: 10px;">
                                <div style="width: 100%; padding: 5px; background-color: #D5E4F6; font-size: 14px;">
                                    <b>CD-ROM</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    Turn any ordinary computer into a CATS server with our optional CD-ROM installer.
                                    Available as a download or we'll mail it.
                                    </span>
                                </div>
                            </td>
                            <td width="50%" align="left" valign="top">
                                <div style="width: 100%; padding: 5px; background-color: #E7F1FC; font-size: 14px;">
                                    <b>Custom Servers</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    Purchase an optional custom server pre-loaded with CATS Professinal. Just plug it
                                    into your network. CATS is already installed!
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="50%" valign="top" align="left" style="padding-right: 20px; padding-bottom: 10px;">
                                <div style="width: 100%; padding: 5px; background-color: #D5E4F6; font-size: 14px;">
                                    <b>Growing?</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    Is your business expanding? Add users or extend your support contract at any
                                    time, automatically on our website.
                                    </span>
                                </div>
                            </td>
                            <td width="50%" align="left" valign="top">
                                <div style="width: 100%; padding: 5px; background-color: #E7F1FC; font-size: 14px;">
                                    <b>Regular Updates</b>
                                    <br />
                                    <span style="font-size: 12px; line-height: 14px; color: #666666;">
                                    We'll keep your server up to date with the latest plug-ins and patches. You'll
                                    never lag behind because you forgot to upgrade.
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td width="100%" colspan="2" valign="top" align="left">
                                <div style="width: 100%; padding: 5px; background-color: #BED4EE; font-size: 16px; text-align: center; font-weight: Bold;">
                                    Spend more time recruiting, leave the software to us.
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <div style="text-align: right; padding: 20px 40px 0 50px;">
            <table cellpadding="0" cellspacing="0" border="0" width="830">
                <tr>
                    <td align="left" valign="bottom">
                        <img src="images/recruitingEasy.jpg" border="0" />
                    </td>
                    <td align="right" valign="bottom">
                        <a href="https://www.catsone.com/professional/">
                        <img src="images/signUp.jpg" border="0" />
                        </a>
                    </td>
                </tr>
            </table>
        </div>

        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-top: 20px; padding-left: 25px;">
            <tr>
                <td width="33%" align="center" valign="bottom">
                    <div style="width: 240px; text-align: left; font-size: 13px; font-weight: bold;">
                        <img src="images/massResumeImport.jpg" border="0" />
                        <br />
                        Upload your resume documents and import them as candidates.
                    </div>
                </td>

                <td width="33%" align="center" valign="bottom">
                    <div style="width: 280px;">
                        <center>
                        <a href="http://www.catsone.com/?a=addons">
                        <img src="images/toolbarImport.jpg" border="0" />
                        </a>
                        <br />
                        <div style="width: 250px; text-align: left; font-size: 13px; font-weight: bold;">
                        Import candidates from websites using your web browser.
                        </div>
                        </center>
                    </div>
                </td>

                <td width="33%" align="center" valign="bottom">
                    <img src="images/serverSystem.jpg" border="0" />
                    <br />
                    <div style="width: 240px; text-align: left; font-size: 13px; font-weight: bold;">
                        Setup is included. We'll install and support CATS for you.
                    </div>
                </td>
            </tr>
        </table>

        <br /><br />

        <div style="width: 830px; padding: 20px; ">
            <form id="keyEntry" name="keyEntry" method="get" action="<?php echo CATSUtility::getIndexName(); ?>">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td align="left" valign="top" colspan="2" style="font-size: 22px; font-weight: bold;">
                        Activate CATS Professional
                        <br />
                        <span style="font-size: 13px; font-weight: normal; line-height: 18px;">
                        Need a license key? Go to the CATS Professional website at <a href="http://www.catsone.com/professional" target="_blank">
                        http://www.catsone.com/professional</a> to get one. Once you enter your key, all the features
                        available to CATS Professional users will become available to you -- immediately.
                        </span>
                        <br /><br />
                    </td>
                </tr>
                <tr>
                    <td valign="top" align="left">
                        <input type="hidden" name="m" value="settings" />
                        <input type="hidden" name="a" value="professional" />
                        <?php echo $this->webForm->getForm(); ?>
                    </td>
                    <td align="left" valign="top" style="padding-left: 20px; padding-top: 2px;">
                        <?php echo $this->webForm->getButton('Enable Professional Services', 'keyEntry'); ?>
                    </td>
                </tr>
            </table>
            </form>
        </div>

        </center>

    <?php endif; ?>


        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
