<?php /* $Id: SystemInformation.tpl 3575 2007-11-12 17:40:45Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" alt="Settings" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Settings");?>: <?php echo __("Administration");?></h2></td>
                </tr>
            </table>

            <p class="note"><?php echo __("System Information");?></p>

            <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" colspan="2"><span style="font-weight: bold;"><?php echo __("General Information");?></span></td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php echo __("Operating System");?>:
                        </td>
                        <td class="tdData">
                            <?php echo(php_uname()); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php echo __("Operating System Type");?>:
                        </td>
                        <td class="tdData">
                            <?php echo __("CATS thinks your operating system is");?> <span class="bold"><?php $this->_($this->OSType); ?>.</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php echo __("PHP Version");?>:
                        </td>
                        <td class="tdData">
                            <?php echo(PHP_VERSION); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php echo __("Database Version");?>:
                        </td>
                        <td class="tdData">
                            <?php $this->_($this->databaseVersion); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php echo __("Installation Directory");?>:
                        </td>
                        <td class="tdData">
                            <?php $this->_($this->installationDirectory); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="tdVertical" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="tdVertical" colspan="2"><span style="font-weight: bold;"><?php echo __("Module Schema Version Information");?></span></td>
                    </tr>
                    <?php foreach ($this->schemaVersions as $rowIndex => $row): ?>
                    <tr>
                        <td class="tdVertical" style="width:250px;">
                            <?php $this->_($row['name']); ?>
                        </td>
                        <td class="tdData">
                            <?php echo($row['version']); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
            </table>
            <input type="button" name="back" class="button" value="<?php echo __("Back");?>" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
