<div class="mt-3">

<?php if (isset($this->documents)): ?>
<div id="uploadQueue" class="alert alert-warning">
You have <?php echo number_format(count($this->documents), 0); ?> document<?php echo count($this->documents) != 1 ? 's' : ''; ?> in your upload queue.
<br /><br />
<input type="button" value="Delete File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="deleteUploadFiles();" class="btn btn-sm btn-outline-danger" />
<input type="button" class="btn btn-sm btn-primary" value="Import File<?php echo count($this->documents) != 1 ? 's' : ''; ?>" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=import&a=massImport&step=2';" />
</div>
<?php endif; ?>

<?php if ($this->multipleFilesEnabled): ?>
    <span>
    <?php if ($this->uploadPath !== false): ?>
        To import multiple files, move or copy your resume documents to the following directory on the computer
        that hosts CATS:
        <br /><br />

        <b><?php echo $this->uploadPath; ?></b>

        <br /><br />
        Once you have resumes in this folder, <a href="<?php echo CATSUtility::getIndexName(); ?>?m=import&a=importSelectType&typeOfImport=resume">
        reload</a> this page to start the import process.

        <br /><br />

        If you need assistance in uploading files to your web server, contact your system administrator.<br />
    <?php else: ?>
        In order to import resume documents into CATS, you need to create a directory named "<b>upload</b>" on the computer
        that hosts cats. This directory needs to have its permissions set to allow files to be created by your
        web server.
        <br /><br />
        <b>Linux Instructions:</b>
        <br />
        <blockquote>
        <span class="text-body-secondary">&gt;</span> mkdir /PATH/TO/CATS/upload<br />
        <span class="text-body-secondary">&gt;</span> chmod -R 777 /PATH/TO/CATS/upload
        </blockquote>
        <br />
        <b>Windows Instructions:</b>
        <blockquote>
        <span class="text-body-secondary">&gt;</span> Create a folder named <b>upload</b> in the directory you installed CATS.<br />
        <span class="text-body-secondary">&gt;</span> Set the appropriate permissions by right clicking the file and selecting <b>Properties</b>, then <b>Security</b>.<br />
        <span class="text-body-secondary">&gt;</span> Make sure all users have access to read, write and delete files and directories.
        </blockquote>
    <?php endif; ?>
    </span>

<?php else: ?>
    The automated bulk resume import feature has been temporarily disabled.<br /><br />
    To import resumes into the bulk resume pool, please contact <a href="mailto:support@catsone.com">support@catsone.com</a>
    for assistance from the CATS team.
    <br />

<?php endif; ?>

<div class="card card-body bg-body-tertiary mt-3">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <a href="http://www.resfly.com" target="_blank"><img src="images/poweredByResfly.jpg" class="img-fluid" alt="Resfly" /></a>
        </div>
        <div class="col">
            <p class="fw-semibold mb-2">Resume parsing is enabled.</p>
            <p class="mb-0">The Resfly parsing service searches your resume files for contact
            and resume information. CATS will import all applicable resume documents as candidates.</p>
        </div>
    </div>
</div>
</div>
