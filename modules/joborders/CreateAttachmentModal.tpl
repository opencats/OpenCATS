<?php TemplateUtility::printModalHeader('Job Order', array('modules/joborders/validator.js'), 'Create Job Order Attachment'); ?>
<main class="container-fluid p-2 oc-joborder-createattachmentmodal">

    <?php if (!$this->isFinishedMode): ?>
    <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkAttachmentForm(document.createAttachmentForm);">
        <input type="hidden" name="postback" id="postback" value="postback">
        <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>">

        <div class="mb-3">
            <label for="file" class="form-label small fw-semibold">Attachment:</label><input class="form-control form-control-sm" type="file" id="file" name="file"></div>
        <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Create Attachment">Create Attachment</button>&nbsp;
        <button type="button" class="btn btn-sm btn-outline-secondary" name="close" value="Cancel" onclick="parentHidePopWin();">Cancel</button>
    </form>
    <?php else: ?>
    <div class="alert alert-success" role="alert">The file has been successfully attached.</div>

    <form>
        <button class="btn btn-sm btn-outline-secondary" type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();">Close</button>
    </form>
    <?php endif; ?>
</main>
</body>
</html>
