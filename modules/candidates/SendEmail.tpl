<?php TemplateUtility::printHeader('Candidates', array('vendor/ckeditor/ckeditor/ckeditor.js', 'js/ckeditor-manager.js', 'modules/candidates/validator.js', 'js/searchSaved.js', 'js/sweetTitles.js', 'js/searchAdvanced.js', 'js/highlightrows.js', 'js/export.js', 'js/emailHandler.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-candidate-sendemail-page">

        <div id="contents">
            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Send E-mail</h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Send Candidates E-mail</h2>

            <?php
            if($this->success == true)
            {
                ?>

                <br>
                <div class="alert alert-success">
                Your e-mail has been successfully sent to the following recipients:
                <blockquote>
                <?php
                echo $this->success_to;
                ?>
                </blockquote></div>


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

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">
                        <form name="emailForm" id="emailForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=emailCandidates" method="post" onsubmit="return checkEmailForm(document.emailForm);" autocomplete="off" enctype="multipart/form-data">
                        <input type="hidden" name="postback" id="postback" value="postback">
                        <?php foreach($this->recipients as $data): ?>
                            <input type="hidden" name="candidateID[]" value="<?php echo($data['email1'].'='.$data['candidate_id'])?>">
                        <?php endforeach; ?>
                        <div>
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label for="emailTo" class="form-label small mb-1">To</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <textarea class="form-control form-control-sm" id="emailTo" name="emailTo" rows="2" cols="90" tabindex="99" readonly><?php echo($emailTo); ?></textarea>
                                </div>
                            </div>
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="emailSubjectLabel" for="emailSubject" class="form-label small mb-1">Subject</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <input id="emailSubject" tabindex="<?php echo($tabIndex++); ?>" type="text" name="emailSubject" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="emailTemplateLabel" for="emailTemplate" class="form-label small mb-1">Template</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="emailTemplate" name="emailTemplate" tabindex="<?php echo($tabIndex++);?>" onchange="showTemplate('<?php echo($this->sessionCookie); ?>');" class="form-select form-select-sm">
                                        <option selected="selected" value="-1">----</option>
                                        <?php foreach($this->emailTemplatesRS as $data): ?>
                                            <option value="<?php echo($data['emailTemplateID']); ?>"><?php echo($data['emailTemplateTitle']); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <label id="emailPreviewLabel" for="candidateName" class="form-label small mb-1">Preview for:</label>
                                    <select id="candidateName" tabindex="<?php echo($tabIndex++); ?>" onchange="replaceTemplateTags('<?php echo($this->sessionCookie); ?>')" class="form-select form-select-sm">
                                        <option selected="selected" value="-1">----</option>
                                        <?php foreach($this->recipients as $data): ?>
                                            <option value="<?php echo($data['candidate_id']); ?>"><?php echo($data['last_name'].", ".$data['first_name']." (".$data['email1']).")"; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="emailBodyLabel" for="emailBody" class="form-label small mb-1">Body</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <textarea id="emailBody" tabindex="<?php echo($tabIndex++); ?>" name="emailBody" rows="10" cols="90" class="form-control form-control-sm"></textarea>
                                </div>
                                <div class="col-12 col-sm">
                                    <div id="emailPreview" tabindex="<?php echo($tabIndex++); ?>" name="emailPreview" rows="10" cols="90"></div>
                                </div>
                            </div>
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-12 col-sm">
                                    <button type="submit" tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-primary" value="Send E-Mail">Send E-Mail</button>&nbsp;
                                    <button type="reset"  tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-outline-secondary" value="Reset">Reset</button>&nbsp;
                                </div>
                            </div>
                        </div>

                        </form>

            <script>
                        document.emailForm.emailSubject.focus();
                        //added the code below for the ckeditor html box - Jamin 2-19-2010
                        //adjusted code to remove or prevent extra breaks in email - Jamin 2-23-2010
                        placeCkEditorIn('emailBody');
                    </script>

                    </div>
                </div>
            </div>
            <?php
            }
            ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
