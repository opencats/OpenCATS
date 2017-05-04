<?php /* $Id: NewVersionCheck.tpl 3585 2007-11-12 23:27:30Z andrew $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), array('modules/settings/validator.js')); ?>
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
                    <td><h2><?php echo __("Settings");?>: <?php echo __("Administration");?></h2></td>
                </tr>
            </table>

            <p class="note"><?php echo __("New Version");?></p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <?php if ($this->versionCheckPref): ?>
                            <p>
                                <?php echo __("Your current version is");?>: <span class="bold"><?php echo(CATSUtility::getVersion()); ?> <?php echo sprintf(__("Build %s"),CATSUtility::getBuild());?></span>.<br />
                                <?php if ($this->newVersion): ?>
                                    <span class="bold"><?php echo __("A new version is available!");?></span><br />
                                    Go to <a href="http://www.catsone.com/" target="_blank">www.catsone.com</a> to learn more.<br />
                                <?php else: ?>
                                    <?php echo __("Catsone.com reports your version is up to date.");?><br />
                                <?php endif; ?>
                            </p>
                            <?php $this->_($this->newVersionNews); ?>
                        <?php else: ?>
                            <span><?php echo __("Version checking is currently disabled.");?></span><br /><br />
                        <?php endif; ?>
                        <!-- <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="changeNewVersionForm" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="changeVersionCheck" />

                            <input type="checkbox" name="versionCheck" id="versionCheck"<?php if ($this->versionCheckPref): ?> checked<?php endif; ?> /><?php echo __("Enable New Version Check (Recommended)");?><br /><br />
                            <input type="submit" name="save" class = "button" value="<?php echo __("Save");?>" />
                            <input type="button" name="back" class = "button" value="<?php echo __("Back");?>" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form> -->
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
