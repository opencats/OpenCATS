<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <div id="header">
        <ul id="primary">
            <?php TemplateUtility::printTabs($this->active, 'Administration'); ?>
        </ul>
    </div>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Skype</p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <form action="<?php echo(osatutil::getIndexName()); ?>?m=skype&amp;a=settings" id="changeSkypeSettingsForm" method="post">
                            <input type="hidden" name="postback" value="postback" />

                            <input type="checkbox" name="skypeCheck" id="skypeCheck"<?php if ($this->skypeEnabled): ?> checked<?php endif; ?> />Transform Phone Numbers into Skype Links<br /><br />
                            <input type="submit" name="save" class="button" value="Save" />
                            <input type="button" name="back" class="button" value="Back" onclick="document.location.href = '<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=administration';" />
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>