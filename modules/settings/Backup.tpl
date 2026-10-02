<?php TemplateUtility::printHeader('Settings', array('js/backup.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Site Backup</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Create Site Backup</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col">
                        Create a backup of your entire CATS database (including all of your attachments).<br />
                        Note: Only one backup of your database can be stored on the server at a time.  Creating a new backup will
                        delete the previous backup.<br />
                        <br />
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col">
                    <span id="backupRunning" style="display:none;">
                        Backing up database, please wait... (Now would be a good time to take a coffee break!)
                        <br /><br />
                        Status:<br />
                    </span>
                    <span id="progressHistory">
                    </span>
                    <span id="progress">
                        Last backup:
                        <div class="table-responsive"><table class="attachmentsTable table table-sm align-middle">
                            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($attachmentsData['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>">
                                            <img src="images/file/zip.gif" alt="" width="16" height="16" border="0" />
                                        </a>
                                    </td>
                                    <td>
                                        (<?php $this->_($attachmentsData['fileSize']) ?>)&nbsp;
                                        <a href="<?php echo htmlspecialchars($attachmentsData['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>">
                                            <?php $this->_($attachmentsData['originalFilename']) ?>
                                        </a>
                                    </td>
                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                    <td>
                                        <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=deleteBackup" style="display:inline;" onsubmit="return confirm('Delete this backup?');">
                                            <input type="hidden" name="postback" value="postback" />
                                            <input type="image" src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </table></div>
                        <?php if (empty($this->attachmentsRS)): ?>
                            None<br />
                        <?php else: ?>
                            Click the file above to download the backup.<br />
                        <?php endif; ?>

                    <br />
                    <input type="button" value="Create Full System Backup" onclick="startBackup('settings:backup', '');"  class="btn btn-sm btn-outline-secondary mb-2"><br />
                    <input type="button" value="Create Attachments Backup" onclick="startBackup('settings:backup', '&attachmentsOnly=true');"  class="btn btn-sm btn-outline-secondary mb-2">
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
                    </div>
                    <span id="tempJs" style="display:none;"></span>
                    <iframe id="progressIFrame" style="display:none;"></iframe>
                </div>
            </div>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
