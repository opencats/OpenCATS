<?php /* $Id: EmailTemplates.tpl 1929 2007-02-22 06:18:30Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array()); ?>
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
                    <td><h2>Administration: E-Mail Templates</h2></td>
                </tr>
            </table>

            <p class="note">E-Mail Templates</p>

            <script type="text/javascript">
                function showTemplate(templateID)
                {
                    <?php foreach ($this->emailTemplatesRS as $data): ?>
                        document.getElementById('editTable<?php echo($data['emailTemplateID']); ?>').style.display = 'none';
                    <?php endforeach; ?>
                    document.getElementById('editTable' + templateID).style.display = '';
                }
                function insertAtCursor(myField, myValue)
                {
                    if (document.selection)
                    {
                        myField.focus();
                        sel = document.selection.createRange();
                        sel.text = myValue;
                    }
                    else if (myField.selectionStart || myField.selectionStart == 0)
                    {
                        var startPos = myField.selectionStart;
                        var endPos = myField.selectionEnd;
                        myField.value = myField.value.substring(0, startPos)
                            + myValue
                            + myField.value.substring(endPos, myField.value.length);
                    }
                    else
                    {
                        myField.value += myValue;
                    }
                }
                <?php function generateInsertAtCursorLink($data, $description, $value)
                {
                    echo('<input type="button" class="button" style="width:235px;" value="'.$description.'" onclick="insertAtCursor(document.getElementById(\'messageText'.$data['emailTemplateID'].'\'),  \''.$value.'\');"><br />');
                } ?>
                <?php function generateInsertAtCursorLinkConditional($data, $description, $value)
                {
                    if (strrpos($data['possibleVariables'], $value) !== false)
                    {
                        generateInsertAtCursorLink($data, $description, $value);
                    }
                } ?>
            </script>

            <table style="width:850px;" class="searchTable">
                <tr>
                    <td>
                        <table>
                            <tr>
                                <td style="width:210px;">
                                    <div style="font-weight:bold;">
                                        Template:
                                    </div>
                                </td>
                                <td>
                                    <span id="selectorSpan">
                                        <select id="titleSelect" style="width:550px;" onclick="showTemplate(this.value);">
                                            <?php foreach ($this->emailTemplatesRS as $data): ?>
                                                <option value="<?php echo($data['emailTemplateID']); ?>"><?php echo($data['emailTemplateTitle']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </span>
                                    <?php foreach ($this->emailTemplatesRS as $data): ?>
                                        <span id="templateTitleSpan<?php echo($data['emailTemplateID']); ?>" style="display:none; border:1px solid #000000; background-color:#ffffff; padding:5px;">
                                            Editing: <?php echo($data['emailTemplateTitle']); ?>
                                        </span>
                                    <?php endforeach; ?>
                                    <!--&nbsp;&nbsp;&nbsp;&nbsp;
                                    <input type="button" class="button" value="New">-->
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>

                        <?php foreach ($this->emailTemplatesRS as $index => $data): ?>
                            <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates" method="post">
                                <input type="hidden" name="postback" value="postback" />
                                <input type="hidden" name="templateID"  value="<?php echo($data['emailTemplateID']); ?>" />
                                <table id="editTable<?php echo($data['emailTemplateID']); ?>" class="editTable" width="850" <?php if ($index != 0): ?>style="display:none;"<?php endif; ?>>
                                    <tr>
                                        <!--<td class="tdVertical" style="width:150px;">
                                            Email Tag:
                                        </td>
                                        <td class="tdData">
                                            <?php echo($data['emailTemplateTag']); ?>
                                        </td>-->
                                    </tr>
                                    <tr>
                                        <td class="tdVertical" style="width:150px;">
                                            Message:
                                        </td>
                                        <td class="tdData">
                                            <table>
                                                <tr style="vertical-align:top;">
                                                    <td>
                                                        <textarea class="inputbox" name="messageText" <?php if ($data['disabled'] == 1) echo('disabled'); ?> id="messageText<?php echo($data['emailTemplateID']); ?>" style="width:450px; height:280px;" onclick="document.getElementById('selectorSpan').style.display='none'; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='';" ><?php echo($this->_($data['text'])); ?></textarea>
                                                        <input type="hidden" name="messageTextOrigional" id="messageTextOrigional<?php echo($data['emailTemplateID']); ?>" value="<?php echo($this->_($data['text'])); ?>">
                                                        <br /><br />
                                                        <input type="checkbox" name="useThisTemplate" id="useThisTemplate<?php echo($data['emailTemplateID']); ?>" <?php if ($data['disabled'] == 0) echo('checked'); ?> onclick="if (this.checked) {document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=false;} else {document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=true;} document.getElementById('selectorSpan').style.display='none'; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='';"> Use this Template / Feature<br />
                                                    </td>
                                                    <td style="text-align: center;">
                                                    <div style="font-weight:bold;">Insert Formatting:</div>
                                                        <?php generateInsertAtCursorLink($data, 'Bold', '<B></B>'); ?>
                                                        <?php generateInsertAtCursorLink($data, 'Italics', '<I></I>'); ?>
                                                        <?php generateInsertAtCursorLink($data, 'Underline', '<U></U>'); ?>
                                                        <br />
                                                        <div style="font-weight:bold;">Insert Mail Merge Fields:</div>
                                                        <?php /* Global vars */ ?>
                                                        <?php if(!isset($this->noGlobalTemplates)): ?>
                                                            <?php generateInsertAtCursorLink($data, 'Current Date/Time', '%DATETIME%'); ?>
                                                            <?php generateInsertAtCursorLink($data, 'Site Name', '%SITENAME%'); ?>
                                                            <?php generateInsertAtCursorLink($data, 'Recruiter/Current User Name', '%USERFULLNAME%'); ?>
                                                            <?php generateInsertAtCursorLink($data, 'Recruiter/Current User E-Mail Link', '%USERMAIL%'); ?>
                                                        <?php endif; ?>

                                                        <?php /* Template specific vars */ ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Previous Candidate Status', '%CANDPREVSTATUS%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Current Candidate Status', '%CANDSTATUS%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Candidate Owner', '%CANDOWNER%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Candidate First Name', '%CANDFIRSTNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Candidate Full Name', '%CANDFULLNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'CATS Candidate URL', '%CANDCATSURL%'); ?>

                                                        <?php generateInsertAtCursorLinkConditional($data, 'Company Owner', '%CLNTOWNER%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Company Name', '%CLNTNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'CATS Company URL', '%CLNTCATSURL%'); ?>

                                                        <?php generateInsertAtCursorLinkConditional($data, 'Contact Owner', '%CONTOWNER%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Contact First Name', '%CONTFIRSTNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Contact Full Name', '%CONTFULLNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Contacts Company Name', '%CONTCLIENTNAME%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'CATS Contact URL', '%CONTCATSURL%'); ?>

                                                        <?php generateInsertAtCursorLinkConditional($data, 'Job Order Owner', '%JBODOWNER%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Job Order Title', '%JBODTITLE%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Job Order Company', '%JBODCLIENT%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'Job Order ID', '%JBODID%'); ?>
                                                        <?php generateInsertAtCursorLinkConditional($data, 'CATS Job Order URL', '%JBODCATSURL%'); ?>
                                                    </td>
                                                 </tr>
                                             </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="tdVertical" style="width:150px;">
                                        </td>
                                        <td>
                                            <input type="submit" class="button" value="Save Template">
                                            <input type="reset" class="button" value="Reset Template" onclick="document.getElementById('selectorSpan').style.display=''; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='none'; document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=<?php if ($data['disabled'] == 0) {echo('false'); } else {echo('true'); } ?>;">
                                        </td>
                                    </tr>
                                </table>
                            </form>
                        <?php endforeach; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
