<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader(__('Job Order'), array('modules/joborders/validator.js'), __('Create Job Order Attachment')); ?>

    <?php if (!$this->isFinishedMode): ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=createAttachment" enctype="multipart/form-data" method="post" onsubmit="return checkAttachmentForm(document.createAttachmentForm);">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />

            <table class="editTable">
                <tr>
                    <td class="tdVertical"><?php _e('Attachment') ?>:</td>
                    <td class="tdData"><input type="file" id="file" name="file" /></td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="<?php _e('Create Attachment') ?>" />&nbsp;
            <input type="button" class="button" name="close" value="<?php _e('Cancel') ?>" onclick="parentHidePopWin();" />
        </form>
    <?php else: ?>
        <p><?php _e('The file has been successfully attached.') ?></p>

        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>