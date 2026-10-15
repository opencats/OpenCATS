<?php
TemplateUtility::printModalHeader(
    'Companies',
    array('modules/companies/validator.js'),
                                  'Create Company Attachment'
);
?>

<main class="container-fluid p-2">
<?php if (!$this->isFinishedMode): ?>
<form name="createAttachmentForm" id="createAttachmentForm"
action="<?php echo Template::escapeAttr(
    CATSUtility::getIndexName() . '?m=companies&a=createAttachment'
); ?>"
enctype="multipart/form-data"
method="post"
onsubmit="return checkAttachmentForm(document.createAttachmentForm);">

<input type="hidden" name="postback" id="postback" value="postback">
<input type="hidden" id="companyID" name="companyID"
value="<?php echo Template::escapeAttr($this->companyID); ?>">

<div class="mb-3">
<label for="file" class="form-label small fw-semibold">
Attachment
</label>
<input type="file" id="file" name="file"
class="form-control form-control-sm">
</div>

<div class="d-flex justify-content-end gap-2">
<button type="submit" class="btn btn-sm btn-primary"
name="submit" id="submit">
Create Attachment
</button>

<button type="button" class="btn btn-sm btn-secondary"
name="cancel"
onclick="parentHidePopWin();">
Cancel
</button>
</div>
</form>

<?php else: ?>
<div class="alert alert-success py-2 mb-3" role="alert">
The file has been successfully attached.
</div>

<div class="d-flex justify-content-end">
<button type="button" class="btn btn-sm btn-primary"
name="close"
onclick="parentHidePopWinRefresh();">
Close
</button>
</div>
<?php endif; ?>
</main>

</body>
</html>
