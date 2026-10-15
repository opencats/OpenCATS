<?php TemplateUtility::printHeader('Settings', array()); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Administration: E-Mail Templates</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">E-Mail Templates</p>

            <script type="text/javascript">
                $(document).ready(function() { 
                    $("select option:last").attr("selected", "selected");
                    showLastTemplate();
                });
                function showTemplate(templateID)
                {
                    <?php foreach ($this->emailTemplatesRS as $data): ?>
                        document.getElementById('editTable<?php echo($data['emailTemplateID']); ?>').style.display = 'none';
                    <?php endforeach; ?>
                    document.getElementById('editTable' + templateID).style.display = '';
                }
                function showLastTemplate()
                {
                    <?php foreach ($this->emailTemplatesRS as $data): ?>
                        document.getElementById('editTable<?php echo($data['emailTemplateID']); ?>').style.display = 'none';
                    <?php endforeach; ?>
                    <?php $templateID = end($this->emailTemplatesRS)['emailTemplateID'];?>
                    document.getElementById('editTable' + <?php echo $templateID; ?>).style.display = '';
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
                    echo('<input type="button" class="btn btn-outline-secondary btn-sm mb-1 w-100" value="'.$description.'" onclick="insertAtCursor(document.getElementById(\'messageText'.$data['emailTemplateID'].'\'),  \''.$value.'\');"><br />');
                } ?>
                <?php function generateInsertAtCursorLinkConditional($data, $description, $value)
                {
                    if (strrpos($data['possibleVariables'], $value) !== false)
                    {
                        generateInsertAtCursorLink($data, $description, $value);
                    }
                } ?>
            </script>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=addEmailTemplate" style="display:inline;">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="submit" value="Add a Template"  class="btn btn-sm btn-primary" />
                        </form>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm">
                                    <div class="fw-semibold">
                                        Template:
                                    </div>
                                </div>
                                <div class="col-12 col-sm">
                                    <span id="selectorSpan">
                                        <select id="titleSelect" onclick="showTemplate(this.value);" class="form-select form-select-sm">
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">

                        <?php foreach ($this->emailTemplatesRS as $index => $data): ?>
                            <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailTemplates" method="post">
                                <input type="hidden" name="postback" value="postback" />
                                <input type="hidden" name="templateID"  value="<?php echo($data['emailTemplateID']); ?>" />
                                <div id="editTable<?php echo($data['emailTemplateID']); ?>" <?php if ($index != 0): ?>style="display:none;"<?php endif; ?> class="card card-body p-2 mb-2">
                                    <div class="row g-2 mb-2">
                                        <!--<td class="tdVertical" style="width:150px;">
                                            Email Tag:
                                        </td>
                                        <td class="tdData">
                                            <?php echo($data['emailTemplateTag']); ?>
                                        </td>-->
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4 col-lg-3">
                                            Message:
                                        </div>
                                        <div class="col-12 col-sm">
                                            <div class="card card-body p-2 mb-2">
                                                <?php if(strpos($data['emailTemplateTag'], "CUSTOM") === 0): ?>
                                                <div class="row g-2 mb-2">
                                                    <div class="col-12 col-sm">
                                                        <input type="text" name="emailTemplateTitle" value="<?php echo($data['emailTemplateTitle']); ?>" class="form-control form-control-sm" />
                                                        <input type="hidden" name="id" value="<?php echo $data['emailTemplateID']?>"/>
                                                        <input type="submit" value="Delete Template" onclick="if (!confirm('Delete this template?')) { return false; } this.form.action='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=deleteEmailTemplate';"  class="btn btn-sm btn-outline-danger" />
                                                    </div>
                                                </div>
                                                <?php endif; ?>
                                                
                                                <div class="row g-2 mb-2" style="vertical-align:top;">
                                                    <div class="col-12 col-sm">
                                                        <textarea name="messageText" <?php if ($data['disabled'] == 1) echo('disabled'); ?> id="messageText<?php echo($data['emailTemplateID']); ?>" rows="12" onclick="document.getElementById('selectorSpan').style.display='none'; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='';"  class="form-control form-control-sm"><?php echo($this->_($data['text'])); ?></textarea>
                                                        <input type="hidden" name="messageTextOrigional" id="messageTextOrigional<?php echo($data['emailTemplateID']); ?>" value="<?php echo($this->_($data['text'])); ?>">
                                                        <br /><br />
                                                        <input type="checkbox" name="useThisTemplate" id="useThisTemplate<?php echo($data['emailTemplateID']); ?>" <?php if ($data['disabled'] == 0) echo('checked'); ?> onclick="if (this.checked) {document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=false;} else {document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=true;} document.getElementById('selectorSpan').style.display='none'; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='';" class="form-check-input"> Use this Template / Feature<br />
                                                    </div>
                                                    <div class="col-12 col-sm">
                                                    <div class="fw-semibold">Insert Formatting:</div>
                                                        <?php generateInsertAtCursorLink($data, 'Bold', '<B></B>'); ?>
                                                        <?php generateInsertAtCursorLink($data, 'Italics', '<I></I>'); ?>
                                                        <?php generateInsertAtCursorLink($data, 'Underline', '<U></U>'); ?>
                                                        <br />
                                                        <div class="fw-semibold">Insert Mail Merge Fields:</div>
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
                                                    </div>
                                                 </div>
                                             </div>
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-sm-4 col-lg-3">
                                        </div>
                                        <div class="col-12 col-sm">
                                            <input type="submit" value="Save Template" class="btn btn-sm btn-primary">
                                            <input type="reset" value="Reset Template" onclick="document.getElementById('selectorSpan').style.display=''; document.getElementById('templateTitleSpan<?php echo($data['emailTemplateID']); ?>').style.display='none'; document.getElementById('messageText<?php echo($data['emailTemplateID']); ?>').disabled=<?php if ($data['disabled'] == 0) {echo('false'); } else {echo('true'); } ?>;" class="btn btn-sm btn-outline-secondary">
                                        </div>
                                    </div>
                                </div>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
