<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/Settings.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">E-Mail Settings</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form name="emailSettingsForm" id="emailSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=emailSettings" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="configured" value="1" />

                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2" id="fromAddressRow">
                                    <div class="col-sm-4 col-lg-3">
                                        <label for="fromAddress" id="fromAddressLabel" class="form-label small mb-0">From E-Mail Address for Outgoing Messages:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="text" name="fromAddress" id="fromAddress" value="<?php $this->_($this->mailerSettingsRS['fromAddress']); ?>"  class="form-control form-control-sm" />
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">&nbsp;</div>
                                    <div class="col-12 col-sm">&nbsp;</div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">&nbsp;</div>
                                    <div class="col-12 col-sm">
                                        <label class="form-label small" for="testEmailAddress">Send Test E-Mail To:</label><br/>
                                        <input type="text" name="testEmailAddress" id="testEmailAddress" value=""   class="form-control form-control-sm" />
                                        <span id="testButtonSpanActive" style="display:none;">
                                            &nbsp;<img src="images/indicator2.gif">
                                        </span><br />
                                        <span id="testButtonSpan">
                                            <input type="button" name="test" id="test" onclick="testEmailSettings('<?php echo($this->sessionCookie); ?>');" value="Test Configuration"  class="btn btn-sm btn-outline-secondary" />
                                        </span>
                                        <div id="testOutput">
                                        </div>
                                        <div id="divider">
                                            <br />
                                            <br />
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row g-2 mb-2" id="fromAddressRow">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0">E-Mail Messages Generated for:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="statusChangeContacted" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CONTACTED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Contacted<br />
                                        <input type="checkbox" name="statusChangeReplied" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CANDIDATE_REPLIED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Candidate Replied<br />
                                        <input type="checkbox" name="statusChangeQualifying" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_QUALIFYING]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Qualifying<br />
                                        <input type="checkbox" name="statusChangeSubmitted" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_SUBMITTED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Submitted<br />
                                        <input type="checkbox" name="statusChangeInterviewing" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_INTERVIEWING]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Interviewing<br />
                                        <input type="checkbox" name="statusChangeOffered" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_OFFERED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Offered<br />
                                        <input type="checkbox" name="statusChangeCandidateDeclined" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CANDIDATEDECLINED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Candidate Declined<br />
                                        <input type="checkbox" name="statusChangeClientDeclined" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_CLIENTDECLINED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Client Declined<br />
                                        <input type="checkbox" name="statusChangePlaced" <?php if($this->candidateJoborderStatusSendsMessage[PIPELINE_STATUS_PLACED]==1): ?>checked<?php endif; ?> class="form-check-input">Status Change: Placed<br />
                                        <?php foreach ($this->emailTemplatesRS as $index => $data): ?>
                                            <input type="checkbox" name="useThisTemplate<?php echo($data['emailTemplateID']); ?>" id="useThisTemplate<?php echo($data['emailTemplateID']); ?>" <?php if ($data['disabled'] == 0) echo('checked'); ?> class="form-check-input"> <?php echo($data['emailTemplateTitle']); ?><br />
                                        <?php endforeach; ?>
                                  </div>
                                </div>
                            </div>
                            <input type="submit" name="submit" id="submit" value="Save"  class="btn btn-sm btn-primary" />&nbsp;
                            <input type="reset"  name="reset"  id="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />&nbsp;
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
