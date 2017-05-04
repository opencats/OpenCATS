<?php /* $Id: Backup.tpl 3582 2007-11-12 22:58:48Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), array('js/backup.js')); ?>
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
                    <td><h2><?php echo __("Settings");?>: <?php echo __("Site Backup");?></h2></td>
                </tr>
            </table>

            <p class="note"><?php echo __("Create Site Backup");?></p>

            <table class="searchTable" width="100%">
                <tr>
                    <td>
                        <?php echo __("Create a backup of your entire CATS database (including all of your attachments).");?><br />
                        <?php echo __("Note: Only one backup of your database can be stored on the server at a time.  Creating a new backup will delete the previous backup.");?><br />
                        <br />
                    </td>
                </tr>
                    <td>
                    <span id="backupRunning" style="display:none;">
                        <?php echo __("Backing up database, please wait... (Now would be a good time to take a coffee break!)");?>
                        <br /><br />
                        <?php echo __("Status");?>:<br />
                    </span>
                    <span id="progressHistory">
                    </span>
                    <span id="progress">
                        <?php echo __("Last backup");?>:
                        <table class="attachmentsTable">
                            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $attachmentsData['retrievalURL']; ?>">
                                            <img src="images/file/zip.gif" alt="" width="16" height="16" border="0" />
                                        </a>
                                    </td>
                                    <td>
                                        (<?php $this->_($attachmentsData['fileSize']) ?>)&nbsp;
                                        <a href="<?php echo $attachmentsData['retrievalURLLocal']; ?>">
                                            <?php $this->_($attachmentsData['originalFilename']) ?>
                                        </a>
                                    </td>
                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                    <td>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=deleteBackup" title="<?php echo __("Delete");?>" onclick="javascript:return confirm('<?php echo __("Delete this backup?");?>');">
                                            <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                        <?php if (empty($this->attachmentsRS)): ?>
                            None<br />
                        <?php else: ?>
                            <?php echo __("Click the file above to download the backup.");?><br />
                        <?php endif; ?>

                    <br />
                    <input type="button" class="button" value="<?php echo __("Create Full System Backup");?>" onclick="startBackup('settings:backup', '');" style="margin:3px; width:200px;"><br />
                    <input type="button" class="button" value="<?php echo __("Create Attachments Backup");?>" onclick="startBackup('settings:backup', '&attachmentsOnly=true');" style="margin:3px; width:200px;">
                    </span>
                    <span id="progressBar" style="display:none;">
                    <br /><br />
                    <div id="empty" style="background-color:#eeeeee;border:1px solid black;height:20px;width:300px;padding:0px;" align="left">
                        <div id="d2" style="position:relative;top:0px;left:0px;background-color:#2244ff;height:20px;width:0px;padding-top:5px;padding:0px;">
                            <div id="d1" style="position:relative;top:0px;left:0px;color:#ffffff;height:20px;text-align:center;font:bold;padding:0px;padding-top:1px;">
                            </div>
                        </div>
                    </div>
                    </span>
                    </td>
                    <span id="tempJs" style="display:none;"></span>
                    <iframe id="progressIFrame" style="display:none;"></iframe>
                </tr>
            </table>

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
