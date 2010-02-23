<?php /* $Id: SendEmail.tpl 3078 2007-09-21 20:25:28Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Candidates'), array('ckeditor/ckeditor.js','modules/candidates/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Candidates');?>: <?php _e('Send E-Mail');?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Send E-Mail');?> <?php _e('To');?> <?php _e('Candidates');?></p>

            <?php
            if($this->success == true)
            {
                ?>

                <br />
                <span style="font-size: 12pt; font-weight: 900;">
                <?php _e('Your e-mail has been successfully sent to the following recipients:');?>
                <blockquote>
                <?php
                echo $this->success_to;
                ?>
                </blockquote>


                <?php
            }
            else
            {
                $emailTo = '';
                foreach($this->recipients as $recipient)
                {
                        if(strlen($recipient['email1']) > 0)
                        {
                            $eml = $recipient['email1'];
                        }
                        else if(strlen($recipient['email2']) > 0)
                        {
                            $eml = $recipient['email2'];
                        }
                        else
                        {
                            $eml = '';
                        }
                        if($eml != '')
                        {
                            if($emailTo != '')
                            {
                                $emailTo .= ', ';
                            }
                            $emailTo .= $eml;
                        }
                }
                $tabIndex = 1;
                ?>

            <table class="editTable" width="100%">
                <tr>
                    <td>
                        <form name="emailForm" id="emailForm" action="<?php echo(osatutil::getIndexName()); ?>?m=candidates&amp;a=emailCandidates" method="post" onsubmit="return checkEmailForm(document.emailForm);" autocomplete="off" enctype="multipart/form-data">
                        <input type="hidden" name="postback" id="postback" value="postback" />
                        <table>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <?php _e('To');?>
                                </td>
                                <td class="tdData">
                                    <textarea class="inputbox" name="emailTo" rows="2", cols="90" tabindex="99" style="width: 600px;" readonly><?php echo($emailTo); ?></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailSubjectLabel" for="emailSubject"><?php _e('Subject');?></label>
                                </td>
                                <td class="tdData">
                                    <input id="emailSubject" tabindex="<?php echo($tabIndex++); ?>" type="text" name="emailSubject" class="inputbox" style="width: 600px;" />
                                </td>
                            </tr>
                            <tr>
                                <td class="tdVertical" style="text-align: right;">
                                    <label id="emailBodyLabel" for="emailBody"><?php _e('Body');?></label>
                                </td>
                                <td class="tdData">
                                    <textarea id="emailBody" tabindex="<?php echo($tabIndex++); ?>" name="emailBody" rows="10" cols="90" style="width: 600px;" class="inputbox"></textarea />
                                </td>
                            </tr>
                            <tr>
                                <td align="right" valign="top" colspan="2">
                                    <input type="submit" tabindex="<?php echo($tabIndex++); ?>" class="button" value="<?php _e('Send E-Mail');?>" />&nbsp;
                                    <input type="reset"  tabindex="<?php echo($tabIndex++); ?>" class="button" value="<?php _e('Reset');?>" />&nbsp;
                                </td>
                            </tr>
                        </table>

                        </form>

                        <script type="text/javascript">
                        document.emailForm.emailSubject.focus();
                        //added the code below for the ckeditor html box - Jamin 2-19-2010
                        //adjusted code to remove or prevent extra breaks in email - Jamin 2-23-2010
						CKEDITOR.replace( 'emailBody' ), {  enterMode : CKEDITOR.ENTER_BR, shiftEnterMode : CKEDITOR.ENTER_BR } );
						CKEDITOR.on( 'instanceReady', function( ev )
        				{
         					ev.editor.dataProcessor.writer.setRules( 'br',
         					{
            					indent : false,
            					breakBeforeOpen : false,
            					breakAfterOpen : false,
            					breakBeforeClose : false,
            					breakAfterClose : false
         					});
   						});
                        </script>
                    </td>
                </tr>
            </table>
            <?php
            }
            ?>
        </div>
<?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>
