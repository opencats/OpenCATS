<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js'), 'Create Candidate Attachment'); ?>

    <?php if (!$this->isFinishedMode){ ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkCreateAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />

            <table class="editTable">
                <tr>
                    <td class="tdVertical">Attachment:</td>
                    <td class="tdData"><input type="file" id="file" name="file" /></td>
                </tr>
                <tr>
                    <td class="tdVertical">Resume:</td>
                    <td>
                        <input type="radio" id="resume" name="resume" value="1" checked="checked" />Yes
                        <input type="radio" id="resume" name="resume" value="0" />No
                    </td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="Create Attachment" />&nbsp;
            <input type="button" class="button" name="cancel" value="Cancel" onclick="parentHidePopWin();" />
        </form>
    <?php } else { ?>
        <?php if(isset($this->resumeText) && $this->resumeText == ''): ?>
            <p>The file has been successfully attached, but OpenCATS was unable to index the resume keywords to make the document searchable.  The file format may be unsupported by OpenCATS.</p>
        <?php else: ?>
            <p>The file has been successfully attached.</p>
        <?php endif; ?>
        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php } ?>
    </body>
</html>
