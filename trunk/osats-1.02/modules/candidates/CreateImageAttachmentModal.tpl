<?php /* $Id: CreateImageAttachmentModal.tpl 2026 2007-02-27 22:34:05Z brian $ */ ?>
<?php TemplateUtility::printModalHeader(__('Candidates'), array('modules/candidates/validator.js')); ?>
    <p class="noteUnsized"><?php _e('Edit Profile Image');?></p>

    <?php if (!$this->isFinishedMode): ?>
        <form name="createAttachmentForm" id="createAttachmentForm" action="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=addEditImage" enctype="multipart/form-data" method="post" onsubmit="">
            <input type="hidden" name="postback" id="postback" value="postback" />
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                 <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                    <div style="text-align:center;">
                        <a href="attachments/<?php $this->_($attachmentsData['attachmentID']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>">
                            <img src="attachments/<?php $this->_($attachmentsData['attachmentID']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>" border="0" width="165">
                        </a>
                    </div>
                 <?php endif; ?>
            <?php endforeach; ?>
            <table class="editTable">
                <tr>
                    <td class="tdVertical"><?php _e('New Profile Picture');?>:</td>
                    <td class="tdData"><input type="file" id="file" name="file" /></td>
                </tr>
            </table>
            <input type="submit" class="button" name="submit" id="submit" value="<?php _e('Set Image');?>" />&nbsp;
            <input type="button" class="button" name="close" value="<?php _e('Close');?>" onclick="parentHidePopWin();" />
        </form>
    <?php else: ?>
        <p><?php _e('The picture has been saved.');?></p>

        <input type="button" name="close" value="<?php _e('Close');?>" onclick="parentHidePopWin();" />
        <script type="text/javascript">
            parentHidePopWin();
        </script>
    <?php endif; ?>
    </body>
</html>