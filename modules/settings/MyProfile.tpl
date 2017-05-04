<?php /* $Id: MyProfile.tpl 2452 2007-05-11 17:47:55Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), array('modules/settings/validator.js', 'js/sorttable.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Settings");?>: <?php echo __("My Profile");?></h2></td>
                </tr>
            </table>

            <p class="note"><?php echo __("Profile");?></p>

            <?php if ($this->isDemoUser): ?>
                <?php echo __("Note that as a demo user, you do not have privileges to modify any settings.");?>
                <br /><br />
            <?php endif; ?>

            <table width="100%">
                <tr>
                    <td width="100%">
                        <table class="searchTable" width="100%">
                            <tr>
                                <td width="230">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php echo($this->userID); ?>&amp;privledged=false">
                                        <img src="images/bullet_black.gif" alt="" border="0" /><?php echo __("View Profile");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("View your current profile to verify your information is correct.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=changePassword">
                                        <img src="images/bullet_black.gif" alt="" border="0" /><?php echo __("Change Password");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Change your CATS login password.");?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                     <?php echo __("Language");?>
                                </td>
                                <td>
                                <?php
                                $userLanguage = $_SESSION['CATS']->getUserCountry(); 
                                ?>
                                   <div id="lngDiv" style="float:left;margin-right:5px;<?php echo ($userLanguage=='pl')?'background-color:#aaddee;':''; ?>"> 	
                                   <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=lang&amp;s=pl">
                                        <img src="locale/pl/flag.png" alt="" border="0" /><br/><?php echo __("Polish");?>
                                    </a>
                                   </div>
                                   <div id="lngDiv" style="float:left;margin-right:5px;<?php echo ($userLanguage=='en')?'background-color:#aaddee;':''; ?>"> 
                                   <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=lang&amp;s=en">
                                        <img src="locale/en/flag.png" alt="" border="0" /><br/><?php echo __("English");?>
                                    </a>
                                   </div>                                     
                                </td>
                            </tr>                            
                            <!--<tr>
                                <td>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=notificationOptions">
                                        <img src="images/bullet_black.gif" alt="" border="0" /><?php echo __("Change Notification Options");?>
                                    </a>
                                </td>
                                <td>
                                    <?php echo __("Change how CATS notifies you of new events.");?>
                                </td>
                            </tr>-->
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
