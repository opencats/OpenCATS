<?php /* $Id: EmailSignature.tpl $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'js/sorttable.js', 'ckeditor/ckeditor.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<div id="main">
    <?php TemplateUtility::printQuickSearch(); ?>

    <div id="contents">
        <table>
            <tr>
                <td width="3%">
                    <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                </td>
                <td><h2>Settings: E-Mail Signature</h2></td>
            </tr>
        </table>

        <p class="note">My E-Mail Signature</p>

        <form name="changeEmailSignatureForm" id="changeEmailSignatureForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=myProfile&amp;s=emailSignature" method="post">
            <input type="hidden" name="postback" id="postback" value="postback" />

            <?php if ($this->isDemoUser): ?>
            Note that as a demo user, you do not have privileges to modify any settings.
            <br /><br />
            <?php endif; ?>

            <table class="searchTable">
                <tr>
                    <td colspan="4">
                        <span class="bold">Change Signature</span>
                        <br />
                        <br />
                        <span id='passwordErrorMessage' style="font:smaller; color: red">
                                <?php if (isset($this->errorMessage)): ?>
                            <?php $this->_($this->errorMessage); ?>
                            <?php endif; ?>
                            </span>
                    </td>
                </tr>


                <tr>
                    <td>
                        <label id="signatureLabel" for="signature">Current Signature:</label>&nbsp;
                    </td>
                    <td>
                        <textarea rows=7 cols=60 class="inputbox" id="signature" name="signature"><?php echo $this->emailSignature; ?></textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <br />
                        <input type="submit" class="button" id="changeSignature" name="action" value="Save" />
                        <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>
<script type="text/javascript">
    CKEDITOR.replace( 'signature' );
    CKEDITOR.on('instanceReady', function(ev)
    {
        var tags = ['p', 'ol', 'ul', 'li']; // etc.

        for (var key in tags) {
            ev.editor.dataProcessor.writer.setRules(
                tags[key],
                {
                    indent : false,
                    breakBeforeOpen : false,
                    breakAfterOpen : false,
                    breakBeforeClose : false,
                    breakAfterClose : false,
                });
        }
    });
</script>
<?php TemplateUtility::printFooter(); ?>
