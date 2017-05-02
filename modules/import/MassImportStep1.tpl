<div class="stepContainer">

<?php if (isset($this->documents)): ?>
<div id="uploadQueue" style="background-color: #f0f0f0; color: #800000; border: 1px solid #800000; text-align: center; font-size: 14px; padding: 10px; margin: 0 0 15px 0; font-weight: bold;">
You have <?php echo number_format(count($this->documents), 0); ?> document<?php echo count($this->documents) != 1 ? 's' : ''; ?> in your upload queue.
<br /><br />
<input type="button" value="Delete File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="deleteUploadFiles();" class="button" />
<input type="button" class="button" value="Import File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=import&a=massImport&step=2';" />
</div>
<?php endif; ?>

<?php if ($this->multipleFilesEnabled): ?>
    <span style="font-size: 16px;">
    <?php if ($this->uploadPath !== false): ?>
        To import multiple files, move or copy your resume documents to the following directory on the computer
        that hosts CATS:
        <br /><br />

        <b><?php echo $this->uploadPath; ?></b>

        <br /><br />
        Once you have resumes in this folder, <a href="<?php echo CATSUtility::getIndexName(); ?>?m=import&a=importSelectType&typeOfImport=resume">
        reload</a> this page to start the import process.

        <br /><br />

        <?php if (LicenseUtility::isProfessional()): ?>
        If you need any assistance, please contact the CATS support team.</br >
        <?php else: ?>
        If you need assistance in uploading files to your web server, contact your system administrator.<br />
        <?php endif; ?>
    <?php else: ?>
        In order to import resume documents into CATS, you need to create a directory named "<b>upload</b>" on the computer
        that hosts cats. This directory needs to have its permissions set to allow files to be created by your
        web server.
        <br /><br />
        <b>Linux Instructions:</b>
        <br />
        <blockquote>
        <span style="color: #c0c0c0;">&gt;</span> mkdir /PATH/TO/CATS/upload<br />
        <span style="color: #c0c0c0;">&gt;</span> chmod -R 777 /PATH/TO/CATS/upload
        </blockquote>
        <br />
        <b>Windows Instructions:</b>
        <blockquote>
        <span style="color: #c0c0c0;">&gt;</span> Create a folder named <b>upload</b> in the directory you installed CATS.<br />
        <span style="color: #c0c0c0;">&gt;</span> Set the appropriate permissions by right clicking the file and selecting <b>Properties</b>, then <b>Security</b>.<br />
        <span style="color: #c0c0c0;">&gt;</span> Make sure all users have access to read, write and delete files and directories.
        </blockquote>
    <?php endif; ?>
    </span>


<?php else: ?>
    The automated bulk resume import feature has been temporarily disabled.<br /><br />
    To import resumes into the bulk resume pool, please contact <a href="mailto:support@catsone.com">support@catsone.com</a>
    for assistance from the CATS team.
    <br />


<?php endif; ?>

<?php if (LicenseUtility::isParsingEnabled()): ?>
<div style="padding: 10px; margin-top: 15px; text-align: left;">
    <table cellpadding="0" cellspacing="0" border="0">
        <tr>
            <td align="left" valign="top" style="padding-right: 20px;">
                <a href="http://www.resfly.com" target="_blank">
                    <img src="images/poweredByResfly.jpg" border="0" style="border: 1px solid #c0c0c0;" />
                </a>
            </td>
            <td align="left" valign="top">
                <span style="font-size: 16px;">
                <?php if (LicenseUtility::isProfessional()): ?>
                You are a registered CATS Professional user <b><?php echo LicenseUtility::getName(); ?></b>.
                <?php elseif (LicenseUtility::isOpenSource()): ?>
                <b>You are a registered open source user of CATS.</b>
                <?php endif; ?>
                </span>

                <p />
                <?php if ((is_array($status = LicenseUtility::getParsingStatus()) && $status['parseLimit'] == -1)): ?>
                    <span style="font-size: 14px; color: #333333;">
                    You have unlimited use of the Resfly parsing service, which searches your resume files for contact
                    and resume information. CATS will import all applicable resume documents as candidates.
                    </span>
                <?php else: ?>
                    <span style="font-size: 14px; color: #333333;">
                    Your resume documents will be imported as searchable documents but <b>not</b> as candidates unless
                    you manually complete the required fields for each document (first and last names).
                    <br /><br />
                    With the
                    Resfly parsing service, much of the candidate's information can be imported automatically.
                    <br />
                    Consider <a href="http://www.catsone.com/?a=getcats" style="font-size: 14px;" target="_blank">upgrading to CATS Professional</a>
                    for unlimited use of this service.
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    </table>
</div>
<?php endif; ?>

</div>
