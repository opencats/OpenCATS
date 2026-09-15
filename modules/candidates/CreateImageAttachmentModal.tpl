<?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js')); ?>
<main class="container-fluid p-2 oc-candidate-createimageattachmentmodal">
    <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Edit Profile Image</h2>

    <?php if (!$this->isFinishedMode): ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addEditImage" enctype="multipart/form-data" method="post" onsubmit="">
            <input type="hidden" name="postback" id="postback" value="postback">
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>">
            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                 <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                    <div>
                        <a href="<?php echo htmlspecialchars($attachmentsData['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>">
                            <img src="<?php echo htmlspecialchars($attachmentsData['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>" width="165">
                        </a>
                    </div>
                 <?php endif; ?>
            <?php endforeach; ?>
            <div class="card card-body p-2 mb-2">
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-sm-4"><label for="file">New Profile Picture:</label></div>
                    <div class="col-12 col-sm"><input type="file" id="file" name="file" class="form-control form-control-sm"></div>
                </div>
            </div>
            <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Set Image">Set Image</button>&nbsp;
            <button type="button" class="btn btn-sm btn-outline-secondary" name="close" value="Close" onclick="parentHidePopWin();">Close</button>
        </form>
    <?php else: ?>
        <div class="alert alert-success py-2" role="alert">The picture has been saved..</div>

        <button type="button" name="close" value="Close" onclick="parentHidePopWin();" class="btn btn-sm btn-outline-secondary">Close</button>
        <script>
            parentHidePopWin();
        </script>
    <?php endif; ?>
</main>
    </body>
</html>
