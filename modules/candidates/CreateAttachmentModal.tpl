<?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js'), 'Create Candidate Attachment'); ?>
<main class="container-fluid p-2 oc-candidate-createattachmentmodal">

    <?php if (!$this->isFinishedMode){ ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkCreateAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback">
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>">

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-sm-4"><label for="file">Attachment:</label></div>
                    <div class="col-12 col-sm"><input type="file" id="file" name="file" class="form-control form-control-sm"></div>
                </div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-sm-4">Resume:</div>
                    <div class="col-12 col-sm">
                        <input type="radio" id="resume" name="resume" value="1" checked="checked" class="form-check-input"><label for="resume">Yes</label>
                        <input type="radio" id="resumeNo" name="resume" value="0" class="form-check-input"><label for="resumeNo">No</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Create Attachment">Create Attachment</button>&nbsp;
            <button type="button" class="btn btn-sm btn-outline-secondary" name="cancel" value="Cancel" onclick="parentHidePopWin();">Cancel</button>
        </form>
    <?php } else { ?>
        <?php if(isset($this->resumeText) && $this->resumeText == ''): ?>
            <div class="alert alert-success py-2" role="alert">The file has been successfully attached, but OpenCATS was unable to index the resume keywords to make the document searchable.  The file format may be unsupported by OpenCATS.</div>
        <?php else: ?>
            <div class="alert alert-success py-2" role="alert">The file has been successfully attached.</div>
        <?php endif; ?>
        <form>
            <button type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" class="btn btn-sm btn-outline-secondary">Close</button>
        </form>
    <?php } ?>
</main>
    </body>
</html>
