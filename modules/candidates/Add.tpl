<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
?>
<?php if ($this->isModal): ?>
    <?php TemplateUtility::printModalHeader('Candidates', array('modules/candidates/validator.js', 'js/addressParser.js', 'js/listEditor.js',  'js/candidate.js', 'js/candidateParser.js'), 'Add New Candidate to this Job Order'); ?>
<main class="container-fluid p-2 oc-candidate-add-page">
<?php else: ?>
    <?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/addressParser.js', 'js/listEditor.js',  'js/candidate.js', 'js/candidateParser.js')); ?>
    <?php TemplateUtility::printHeaderBlock(); ?>
    <?php TemplateUtility::printTabs($this->active, $this->subActive); ?>

    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-candidate-add-page">

        <div id="contents">

            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Add Candidate</h1></header>

<?php endif; ?>

<script>
    window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
</script>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Basic Information</h2>

            <div class="alert alert-warning py-2" style="display:none;" id="candidateAlreadyInSystemTable">
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-sm-4">
                        This profile may already be in the system. Possible duplicate candidate profile:
                        <a href="javascript:void(0);" onclick="window.open('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID='+candidateIsAlreadyInSystemID);">
                            <img src="images/new_window.gif">
                            <img src="images/candidate_small.gif">
                            <span id="candidateAlreadyInSystemName"></span>
                        </a>
                    </div>
                </div>
            </div>

            <?php if ($this->isModal): ?>
                <?php $URI = CATSUtility::getIndexName() . '?m=joborders&amp;a=addCandidateModal&jobOrderID=' . $this->jobOrderID; ?>
            <?php else: ?>
                <?php $URI = CATSUtility::getIndexName() . '?m=candidates&amp;a=add'; ?>
            <?php endif; ?>

            <form name="addCandidateForm" id="addCandidateForm" enctype="multipart/form-data" action="<?php echo($URI); ?>" method="post" onsubmit="return (checkAddForm(document.addCandidateForm) && onSubmitEmailInSystem() && onSubmitPhoneInSystem());" autocomplete="off">
                <?php if ($this->isModal): ?>
                    <input type="hidden" name="jobOrderID" id="jobOrderID" value="<?php echo($this->jobOrderID); ?>">
                <?php endif; ?>
                <input type="hidden" name="postback" id="postback" value="postback">

                <div class="row g-3"><div class="col-12 col-lg-6"><div class="card card-body p-2 mb-2">

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="firstNameLabel" for="firstName" class="form-label small mb-1">First Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" tabindex="1" name="firstName" id="firstName" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['firstName'])) $this->_($this->preassignedFields['firstName']); ?>"><span class="text-danger" title="Required">*</span></div>
                        </div>


                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="middleNameLabel" for="middleName" class="form-label small mb-1">Middle Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="2" name="middleName" id="middleName" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['middleName'])) $this->_($this->preassignedFields['middleName']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="lastNameLabel" for="lastName" class="form-label small mb-1">Last Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" tabindex="2" name="lastName" id="lastName" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['lastName'])) $this->_($this->preassignedFields['lastName']); ?>"><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="emailLabel" for="email1" class="form-label small mb-1">E-Mail:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="3" name="email1" id="email1" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['email'])) $this->_($this->preassignedFields['email']); elseif (isset($this->preassignedFields['email1'])) $this->_($this->preassignedFields['email1']); ?>" onchange="checkEmailAlreadyInSystem(this.value);">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="email2Label" for="email2" class="form-label small mb-1">2nd E-Mail:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="4" name="email2" id="email2" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['email2'])) $this->_($this->preassignedFields['email2']); ?>" onchange="checkEmailAlreadyInSystem(this.value);">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="webSiteLabel" for="webSite" class="form-label small mb-1">Web Site:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="5" name="webSite" id="webSite" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['webSite'])) $this->_($this->preassignedFields['webSite']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneHomeLabel" for="phoneHome" class="form-label small mb-1">Home Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="6" name="phoneHome" id="phoneHome" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['phoneHome'])) $this->_($this->preassignedFields['phoneHome']); ?>" onchange="checkPhoneAlreadyInSystem(this.value);">

                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneCellLabel" for="phoneCell" class="form-label small mb-1">Cell Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="7" name="phoneCell" id="phoneCell" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['phoneCell'])) $this->_($this->preassignedFields['phoneCell']); ?>" onchange="checkPhoneAlreadyInSystem(this.value);">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneWorkLabel" for="phoneWork" class="form-label small mb-1">Work Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="8" name="phoneWork" id="phoneWork" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['phoneWork'])) $this->_($this->preassignedFields['phoneWork']); ?>" onchange="checkPhoneAlreadyInSystem(this.value);">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="addressLabel" for="address" class="form-label small mb-1">Address:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="9" name="address" id="address" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['address'])) $this->_($this->preassignedFields['address']); ?>">
                             <img src="images/indicator2.gif" id="addressParserIndicator" alt="" style="visibility: hidden;" height="16" width="16">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="address2Label" for="address2" class="form-label small mb-1">Address 2:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="10" name="address2" id="address2" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['address2'])) $this->_($this->preassignedFields['address2']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="cityLabel" for="city" class="form-label small mb-1">City:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="11" name="city" id="city" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['city'])) $this->_($this->preassignedFields['city']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="stateLabel" for="state" class="form-label small mb-1">State:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="12" name="state" id="state" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['state'])) $this->_($this->preassignedFields['state']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="zipLabel" for="zip" class="form-label small mb-1">Postal Code:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="13" name="zip" id="zip" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['zip'])) $this->_($this->preassignedFields['zip']); ?>">&nbsp;
                            <button type="button" tabindex="92" onclick="CityState_populate('zip', 'ajaxIndicator');" value="Lookup" class="btn btn-sm btn-outline-secondary">Lookup</button>
                            <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style=" visibility: hidden;">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="countryLabel" for="country" class="form-label small mb-1">Country:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <?php TemplateUtility::printCountrySelect('country', (isset($this->preassignedFields['country']) ? $this->preassignedFields['country'] : ''), true); ?>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="bestTimeToCallLabel" for="bestTimeToCall" class="form-label small mb-1">Best Time to Call:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="13" name="bestTimeToCall" id="bestTimeToCall" class="form-control form-control-sm" value="<?php if(isset($this->preassignedFields['bestTimeToCall'])) $this->_($this->preassignedFields['bestTimeToCall']); ?>">
                        </div>
                    </div>

                    <?php $tabIndex = 15; ?>
                </div></div><div class="col-12 col-lg-6"><section class="card card-body p-2 mb-2"><h2 class="h6">Resume / address import</h2>
<div class="mb-2"><?php if ($this->isParsingEnabled): ?>
                                <?php if ($this->isModal): ?> <?php else: ?> <?php endif; ?>
                                <img id="transfer" src="images/parser/transfer<?php echo ($this->contents != '' ? '' : '_grey'); ?>.gif" <?php echo ($this->contents != '' ? 'style="cursor: pointer;"' : ''); ?> alt="Import Resume" onclick="parseDocumentFileContents();">
                            <?php else: ?>
                                 <button id="arrowButton" tabindex="91" type="button" value="&lt;--" class="btn btn-sm btn-outline-secondary arrowbutton" onclick="AddressParser_parse('addressBlock', 'person', 'addressParserIndicator', 'arrowButton'); document.addCandidateForm.firstName.focus();">&lt;--</button>
                            <?php endif; ?></div>
                            <?php if ($this->isParsingEnabled): ?>
                                <input type="hidden" name="loadDocument" id="loadDocument" value="">
                                <input type="hidden" name="parseDocument" id="parseDocument" value="">
                                <input type="hidden" name="documentTempFile" id="documentTempFile" value="<?php echo (isset($this->preassignedFields['documentTempFile']) ? $this->preassignedFields['documentTempFile'] : ''); ?>">
                                <div>
                                    <div class="row g-2 align-items-start mb-2">
                                        <div class="col-12 col-sm">
                                            <img src="images/parser/arrow.gif">
                                            <input type="hidden" name="MAX_FILE_SIZE" VALUE="10000000">
                                            <label for="documentFile" class="form-label small mb-1">Resume file</label><input type="file" id="documentFile" name="documentFile" onchange="documentFileChange();" size="<?php if ($this->isModal): ?>20<?php else: ?>40<?php endif; ?>" class="form-control form-control-sm">
                                            <button type="button" id="documentLoad" value="Upload" onclick="loadDocumentFileContents();" disabled class="btn btn-sm btn-outline-secondary">Upload</button>
                                            &nbsp;
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-start mb-2">
                                        <div class="col-12 col-sm">
                                            <?php if (isset($this->preassignedFields['documentTempFile']) && ($tempFile = $this->preassignedFields['documentTempFile']) != ''): ?>
                                            <div id="showAttachmentDetails">
                                                <div>
                                                    <div class="row g-2 align-items-start mb-2">
                                                        <div class="col-12 col-sm">
                                                            <img src="images/parser/attachment.gif">
                                                            Attachment: <span><?php echo $tempFile; ?></span>
                                                        </div>
                                                        <div class="col-12 col-sm">
                                                            <a href="javascript:void(0);" onclick="removeDocumentFile();">(remove)</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endif; ?>

                                            <label for="documentText" class="form-label small mb-1">Resume contents</label><textarea class="form-control form-control-sm" tabindex="90" name="documentText" id="documentText" rows="5" cols="40" onmousemove="documentCheck();" onchange="documentCheck();" onmousedown="documentCheck();" onkeypress="documentCheck();"><?php echo $this->contents; ?></textarea>
                                            <br>
                                            <div>
                                            (<b>hint:</b> you may also paste the resume contents)
                                            <div class="mb-2"></div>
                                            Need to upload multiple resumes? <a href="<?php echo CATSUtility::getIndexName(); ?>?m=import&a=massImport">Click here!</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php $freeformTop = '<p class="freeformtop">Cut and paste freeform address here.</p>'; ?>
                                <?php eval(Hooks::get('CANDIDATE_TEMPLATE_ABOVE_FREEFORM')); ?>
                                <?php echo($freeformTop); ?>

                                <label for="addressBlock" class="form-label small mb-1">Freeform address</label><textarea class="form-control form-control-sm" tabindex="90" name="addressBlock" id="addressBlock" rows="5" cols="40"></textarea>

                                <?php $freeformBottom = '<p class="freeformbottom">Cut and paste freeform address here.</p>'; ?>
                                <?php eval(Hooks::get('CANDIDATE_TEMPLATE_BELOW_FREEFORM')); ?>
                                <?php echo($freeformBottom); ?>
                            <?php endif; ?>
                        </section></div></div>

                <?php if (!$this->isParsingEnabled || $this->associatedAttachment != 0): ?>
                <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Resume</h2>

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">Resume:</div>
                        <div class="col-12 col-sm">
                            <?php if ($this->associatedAttachment == 0): ?>
                                <div class="d-flex flex-wrap align-items-center gap-2"> <?php /* FIXME:  remove nobr stuff */ ?>
                                    <?php if (isset($this->overAttachmentQuota)): ?>
                                        <span>(You have already reached your limit of <?php echo(FREE_ACCOUNT_SIZE/1024); ?> MB of attachments, and cannot add additional file attachments.)<br></span>Copy and Paste Resume:&nbsp;
                                    <?php else: ?>
                                        <input type="file" id="file" name="file" size="21" tabindex="<?php echo($tabIndex++); ?>" <?php if($this->associatedTextResume !== false): ?>disabled<?php endif; ?> class="form-control form-control-sm"> &nbsp;
                                    <?php endif; ?>
                                    <a href="javascript:void(0);" onclick="if (document.getElementById('textResumeTD').style.display != '') { document.getElementById('textResumeTD').style.display = ''; document.getElementById('file').disabled=true; } else { document.getElementById('textResumeTD').style.display='none'; document.getElementById('file').disabled = false; }">
                                        <img src="images/package_editors.gif"  class="absmiddle" alt="" title="Copy / Paste Resume">
                                    </a>
                                </div>
                             <?php else: ?>
                                <a href="<?php echo htmlspecialchars($this->associatedAttachmentRS['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>">
                                    <img src="<?php $this->_($this->associatedAttachmentRS['attachmentIcon']) ?>" alt="" width="16" height="16">
                                </a>
                                <a href="<?php echo htmlspecialchars($this->associatedAttachmentRS['retrievalURL'], ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING, false); ?>">
                                    <?php $this->_($this->associatedAttachmentRS['originalFilename']) ?>
                                </a>
                                <?php echo($this->associatedAttachmentRS['previewLink']); ?>
                                <input type="hidden" name="associatedAttachment" value="<?php echo($this->associatedAttachment); ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-12 col-sm">&nbsp;</div>
                    </div>
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-12 col-sm">
                            <input type="hidden" name="textResumeFilename" value="<?php if(isset($this->preassignedFields['textResumeFilename'])) $this->_($this->preassignedFields['textResumeFilename']); else echo('resume.txt'); ?>">
                            <div id="textResumeTD" <?php if($this->associatedTextResume === false): ?>style="display:none;"<?php endif; ?>>
                                <p class="freeformtop">Cut and paste resume text here.</p>

                                &nbsp;<textarea class="form-control form-control-sm" tabindex="90" name="textResumeBlock" id="textResumeBlock" rows="5" cols="60"><?php if ($this->associatedTextResume !== false) $this->_($this->associatedTextResume); ?></textarea>

                                <p class="freeformtop">Cut and paste resume text here.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <br>
                <?php endif; ?>

                <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                    <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">EEO Information</h2>
                    <div class="card card-body p-2 mb-2">
                         <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                             <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="genderLabel" for="gender" class="form-label small mb-1">Gender:</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="gender" name="gender" class="form-select form-select-sm" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="M"<?php if (isset($this->preassignedFields['gender']) && $this->preassignedFields['gender'] == 'M') echo ' selected'; ?>>Male</option>
                                        <option value="F"<?php if (isset($this->preassignedFields['gender']) && $this->preassignedFields['gender'] == 'F') echo ' selected'; ?>>Female</option>
                                    </select>
                                </div>
                             </div>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['ethnicTracking'] == 1): ?>
                             <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="raceLabel" for="race" class="form-label small mb-1">Ethnic Background:</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="race" name="race" class="form-select form-select-sm" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="1"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '1') echo ' selected'; ?>>American Indian</option>
                                        <option value="2"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '2') echo ' selected'; ?>>Asian or Pacific Islander</option>
                                        <option value="3"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '3') echo ' selected'; ?>>Hispanic or Latino</option>
                                        <option value="4"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '4') echo ' selected'; ?>>Non-Hispanic Black</option>
                                        <option value="5"<?php if (isset($this->preassignedFields['race']) && $this->preassignedFields['race'] == '5') echo ' selected'; ?>>Non-Hispanic White</option>
                                    </select>
                                </div>
                             </div>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['veteranTracking'] == 1): ?>
                             <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="veteranLabel" for="veteran" class="form-label small mb-1">Veteran Status:</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="veteran" name="veteran" class="form-select form-select-sm" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="1"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '1') echo ' selected'; ?>>No</option>
                                        <option value="2"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '2') echo ' selected'; ?>>Eligible Veteran</option>
                                        <option valie="3"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '3') echo ' selected'; ?>>Disabled Veteran</option>
                                        <option value="4"<?php if (isset($this->preassignedFields['veteran']) && $this->preassignedFields['veteran'] == '4') echo ' selected'; ?>>Eligible and Disabled</option>
                                    </select>
                                </div>
                             </div>
                         <?php endif; ?>
                         <?php if ($this->EEOSettingsRS['disabilityTracking'] == 1): ?>
                             <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="disabilityLabel" for="disability" class="form-label small mb-1">Disability Status:</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="disability" name="disability" class="form-select form-select-sm" tabindex="<?php echo($tabIndex++); ?>">
                                        <option selected="selected" value="">----</option>
                                        <option value="No"<?php if (isset($this->preassignedFields['disability']) && $this->preassignedFields['disability'] == 'No') echo ' selected'; ?>>No</option>
                                        <option value="Yes"<?php if (isset($this->preassignedFields['disability']) && $this->preassignedFields['disability'] == 'Yes') echo ' selected'; ?>>Yes</option>
                                    </select>
                                </div>
                             </div>
                         <?php endif; ?>
                    </div>
                    <br>
                <?php endif; ?>

                <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Other</h2>
                <div class="card card-body p-2 mb-2">

                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                        <div class="row g-2 align-items-start mb-2">
                            <div class="col-sm-4" id="extraFieldTd<?php echo($i); ?>">
                                <label id="extraFieldLbl<?php echo($i); ?>" class="form-label small mb-1">
                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                </label>
                            </div>
                            <div class="col-12 col-sm" id="extraFieldData<?php echo($i); ?>">
                                <?php echo($this->extraFieldRS[$i]['addHTML']); ?>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="canRelocateLabel" for="canRelocate" class="form-label small mb-1">Can Relocate:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="checkbox" tabindex="<?php echo($tabIndex++); ?>" id="canRelocate" name="canRelocate" value="1"<?php if (isset($this->preassignedFields['canRelocate']) && $this->preassignedFields['canRelocate'] == '1') echo ' checked'; ?> class="form-check-input">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="dateAvailableLabel" for="dateAvailable" class="form-label small mb-1">Date Available:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <script>DateInput('dateAvailable', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', <?php echo($tabIndex++); ?>);</script>

                            <?php /* DateInput()s take up 3 tabindexes. */ ?>
                            <?php $tabIndex += 2; ?>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="currentEmployerLabel" for="currentEmployer" class="form-label small mb-1">Current Employer:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="currentEmployer" id="currentEmployer" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['currentEmployer'])) $this->_($this->preassignedFields['currentEmployer']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="currentPayLabel" for="currentPay" class="form-label small mb-1">Current Pay:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="currentPay" id="currentPay" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['currentPay'])) $this->_($this->preassignedFields['currentPay']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="desiredPayLabel" for="desiredPay" class="form-label small mb-1">Desired Pay:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" tabindex="<?php echo($tabIndex++); ?>" name="desiredPay" id="desiredPay" class="form-control form-control-sm" value="<?php if (isset($this->preassignedFields['desiredPay'])) $this->_($this->preassignedFields['desiredPay']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="sourceLabel" for="sourceSelect" class="form-label small mb-1">Source:</label>
                        </div>
                        <div class="col-12 col-sm">
<?php if ($this->isModal): ?>
                            <select id="sourceSelect" tabindex="<?php echo($tabIndex++); ?>" name="source" class="form-select form-select-sm">
<?php else: ?>
                            <select id="sourceSelect" tabindex="<?php echo($tabIndex++); ?>" name="source" class="form-select form-select-sm" onchange="if (this.value == 'edit') { listEditor('Sources', 'sourceSelect', 'sourceCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <option value="edit">(Edit Sources)</option>
                                <option value="nullline">-------------------------------</option>
<?php endif; ?>
                                    <option value="(none)" <?php if (!isset($this->preassignedFields['source'])): ?>selected="selected"<?php endif; ?>>(None)</option>
                                    <?php if (isset($this->preassignedFields['source'])): ?>
                                        <option value="<?php $this->_($this->_($this->preassignedFields['source'])); ?>" selected="selected"><?php $this->_($this->_($this->preassignedFields['source'])); ?></option>
                                    <?php endif; ?>
                                <?php foreach ($this->sourcesRS AS $index => $source): ?>
                                    <option value="<?php $this->_($source['name']); ?>"><?php $this->_($source['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="sourceCSV" name="sourceCSV" value="<?php $this->_($this->sourcesString); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="keySkillsLabel" for="keySkills" class="form-label small mb-1">Key Skills:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" tabindex="<?php echo($tabIndex++); ?>" name="keySkills" id="keySkills" value="<?php if (isset($this->preassignedFields['keySkills'])) $this->_($this->preassignedFields['keySkills']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="notesLabel" for="notes" class="form-label small mb-1">Misc. Notes:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <textarea class="form-control form-control-sm" tabindex="<?php echo($tabIndex++); ?>" name="notes" id="notes" rows="5" cols="40"><?php if (isset($this->preassignedFields['notes'])) $this->_($this->preassignedFields['notes']); ?></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-primary" value="Add Candidate">Add Candidate</button>&nbsp;
                <button type="reset"  tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-outline-secondary" value="Reset">Reset</button>&nbsp;
                <?php if ($this->isModal): ?>
                    <button type="button" tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-outline-secondary" value="Back to Search" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=considerCandidateSearch&amp;jobOrderID=<?php echo($this->jobOrderID); ?>');">Back to Search</button>
                <?php else: ?>
                    <button type="button" tabindex="<?php echo($tabIndex++); ?>" class="btn btn-sm btn-outline-secondary" value="Back to Candidates" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates');">Back to Candidates</button>
                <?php endif; ?>
            </form>

<script>
    document.addCandidateForm.firstName.focus();
    <?php if(isset($this->preassignedFields['email']) || isset($this->preassignedFields['email1'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['email'])) echo(urlencode($this->preassignedFields['email'])); else if(isset($this->preassignedFields['email1'])) echo(urlencode($this->preassignedFields['email1'])); ?>"));
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['email2']) || isset($this->preassignedFields['email2'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['email2'])) echo(urlencode($this->preassignedFields['email2'])); else if(isset($this->preassignedFields['email2'])) echo(urlencode($this->preassignedFields['email2'])); ?>"));
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneCell']) || isset($this->preassignedFields['phoneCell'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneCell'])) echo(urlencode($this->preassignedFields['phoneCell'])); else if(isset($this->preassignedFields['phoneCell'])) echo(urlencode($this->preassignedFields['phoneCell'])); ?>"));
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneWork']) || isset($this->preassignedFields['phoneWork'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneWork'])) echo(urlencode($this->preassignedFields['phoneWork'])); else if(isset($this->preassignedFields['phoneWork'])) echo(urlencode($this->preassignedFields['phoneWork'])); ?>"));
    <?php endif; ?>
    <?php if(isset($this->preassignedFields['phoneHome']) || isset($this->preassignedFields['phoneHome'])): ?>
        checkEmailAlreadyInSystem(urlDecode("<?php if(isset($this->preassignedFields['phoneHome'])) echo(urlencode($this->preassignedFields['phoneHome'])); else if(isset($this->preassignedFields['phoneHome'])) echo(urlencode($this->preassignedFields['phoneHome'])); ?>"));
    <?php endif; ?>
</script>

<?php if ($this->isModal): ?>
    </main>
    </body>
</html>
<?php else: ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
