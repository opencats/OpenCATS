<?php TemplateUtility::printHeader('Candidates', array('modules/candidates/validator.js', 'js/sweetTitles.js', 'js/listEditor.js', 'js/doubleListEditor.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <script>
        window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
    </script>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-candidate-edit-page">

        <div id="contents">
            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Edit</h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Edit Candidate</h2>

            <form name="editCandidateForm" id="editCandidateForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editCandidateForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback">
                <input type="hidden" id="candidateID" name="candidateID" value="<?php $this->_($this->data['candidateID']); ?>">

                <div class="row g-3"><div class="col-12 col-lg-6"><div class="card card-body p-2 mb-2">
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="isHotLabel" for="isActive" class="form-label small mb-1">Active:</label>
                        </div>
                        <div class="col-12 col-sm" >
                            <input type="checkbox" id="isActive" name="isActive"<?php if ($this->data['isActive'] == 1): ?> checked<?php endif; ?> class="form-check-input">
                            <img title="Unchecking this box indicates the candidate is inactive, and will no longer display on the resume search results." src="images/information.gif" alt="" width="16" height="16">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="firstNameLabel" for="firstName" class="form-label small mb-1">First Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" class="form-control form-control-sm" id="firstName" name="firstName" value="<?php $this->_($this->data['firstName']); ?>"><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="middleNameLabel" for="middleName" class="form-label small mb-1">Middle Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="middleName" name="middleName" value="<?php $this->_($this->data['middleName']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="lastNameLabel" for="lastName" class="form-label small mb-1">Last Name:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <div class="d-flex align-items-center gap-1"><input type="text" class="form-control form-control-sm" id="lastName" name="lastName" value="<?php $this->_($this->data['lastName']); ?>"><span class="text-danger" title="Required">*</span></div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="email1Label" for="email1" class="form-label small mb-1">E-Mail:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="email1" name="email1" value="<?php $this->_($this->data['email1']); ?>">
                        </div>
                    </div>
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="email2Label" for="email2" class="form-label small mb-1">2nd E-Mail:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="email2" name="email2" value="<?php $this->_($this->data['email2']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneHomeLabel" for="phoneHome" class="form-label small mb-1">Home Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="phoneHome" name="phoneHome" value="<?php $this->_($this->data['phoneHome']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneCellLabel" for="phoneCell" class="form-label small mb-1">Cell Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="phoneCell" name="phoneCell" value="<?php $this->_($this->data['phoneCell']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="phoneWorkLabel" for="phoneWork" class="form-label small mb-1">Work Phone:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="phoneWork" name="phoneWork" value="<?php $this->_($this->data['phoneWork']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="webSiteLabel" for="webSite" class="form-label small mb-1">Web Site:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="webSite" name="webSite" value="<?php $this->_($this->data['webSite']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="addressLabel" for="address" class="form-label small mb-1">Address:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="address" name="address" value="<?php $this->_($this->data['address']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="address2Label" for="address2" class="form-label small mb-1">Address 2:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="address2" name="address2" value="<?php $this->_($this->data['address2']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="cityLabel" for="city" class="form-label small mb-1">City:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="city" name="city" value="<?php $this->_($this->data['city']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="stateLabel" for="state" class="form-label small mb-1">State:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="state" name="state" value="<?php $this->_($this->data['state']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="zipLabel" for="zip" class="form-label small mb-1">Postal Code:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="zip" name="zip" value="<?php $this->_($this->data['zip']); ?>">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="CityState_populate('zip', 'ajaxIndicator');" value="Lookup">Lookup</button>
                            <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style=" visibility: hidden;">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="countryLabel" for="country" class="form-label small mb-1">Country:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <?php TemplateUtility::printCountrySelect('country', $this->data['country'], true); ?>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="bestTimeToCallLabel" for="bestTimeToCall" class="form-label small mb-1">Best Time To Call:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="bestTimeToCall" name="bestTimeToCall" value="<?php $this->_($this->data['bestTimeToCall']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="isHotLabel" for="isHot" class="form-label small mb-1">Hot Candidate:</label>
                        </div>
                        <div class="col-12 col-sm" >
                            <input type="checkbox" id="isHot" name="isHot"<?php if ($this->data['isHot'] == 1): ?> checked<?php endif; ?> class="form-check-input">

                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="sourceLabel" for="sourceSelect" class="form-label small mb-1">Source:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <select id="sourceSelect" name="source" class="form-select form-select-sm" onchange="if (this.value == 'edit') { listEditor('Sources', 'sourceSelect', 'sourceCSV', false, ''); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <option value="edit">(Edit Sources)</option>
                                <option value="nullline">-------------------------------</option>
                                <?php if ($this->sourceInRS == false): ?>
                                    <?php if ($this->data['source'] != '(none)'): ?>
                                        <option value="(none)">(None)</option>
                                    <?php endif; ?>
                                    <option value="<?php $this->_($this->data['source']); ?>" selected="selected"><?php $this->_($this->data['source']); ?></option>
                                <?php else: ?>
                                    <option value="(none)">(None)</option>
                                <?php endif; ?>
                                <?php foreach ($this->sourcesRS AS $index => $source): ?>
                                    <option value="<?php $this->_($source['name']); ?>" <?php if ($source['name'] == $this->data['source']): ?>selected<?php endif; ?>><?php $this->_($source['name']); ?></option>
                                <?php endforeach; ?>
                            </select>

                            <input type="hidden" id="sourceCSV" name="sourceCSV" value="<?php $this->_($this->sourcesString); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="ownerLabel" for="owner" class="form-label small mb-1">Owner:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <select id="owner" name="owner" class="form-select form-select-sm" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                                <option value="-1">None</option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                    <?php if ($this->data['owner'] == $usersData['userID']): ?>
                                        <option selected="selected" value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;*
                            <div style="display:none;" id="divOwnershipChange">
                                <input type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?> class="form-check-input"> E-Mail new owner of change
                            </div>
                        </div>
                    </div>

                     <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="pictureLabel" for="addImage" class="form-label small mb-1">Picture:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="addImage" name="addImage" value="Edit Profile Picture" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addEditImage&amp;candidateID=<?php echo($this->candidateID); ?>', 400, 370, null); return false;">Edit Profile Picture</button>&nbsp;
                        </div>
                    </div>
                </div>

</div><div class="col-12 col-lg-6">
                <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                    <?php if(!$this->EEOSettingsRS['canSeeEEOInfo']): ?>
                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 align-items-start mb-2">
                                <div class="col-12 col-sm">
                                    Editing EEO data is disabled.
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                        <div class="card card-body p-2 mb-2" <?php if (!$this->EEOSettingsRS['canSeeEEOInfo']): ?>style="display:none;"<?php endif; ?>>

                         <?php if ($this->EEOSettingsRS['genderTracking'] == 1): ?>
                             <div class="row g-2 align-items-start mb-2">
                                <div class="col-sm-4">
                                    <label id="genderLabel" for="gender" class="form-label small mb-1">Gender:</label>
                                </div>
                                <div class="col-12 col-sm">
                                    <select id="gender" name="gender" class="form-select form-select-sm">
                                        <option value="">----</option>
                                        <option value="m" <?php if (strtolower($this->data['eeoGender']) == 'm') echo('selected'); ?>>Male</option>
                                        <option value="f" <?php if (strtolower($this->data['eeoGender']) == 'f') echo('selected'); ?>>Female</option>
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
                                    <select id="race" name="race" class="form-select form-select-sm">
                                        <option value="">----</option>
                                        <option value="1" <?php if ($this->data['eeoEthnicTypeID'] == 1) echo('selected'); ?>>American Indian</option>
                                        <option value="2" <?php if ($this->data['eeoEthnicTypeID'] == 2) echo('selected'); ?>>Asian or Pacific Islander</option>
                                        <option value="3" <?php if ($this->data['eeoEthnicTypeID'] == 3) echo('selected'); ?>>Hispanic or Latino</option>
                                        <option value="4" <?php if ($this->data['eeoEthnicTypeID'] == 4) echo('selected'); ?>>Non-Hispanic Black</option>
                                        <option value="5" <?php if ($this->data['eeoEthnicTypeID'] == 5) echo('selected'); ?>>Non-Hispanic White</option>
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
                                    <select id="veteran" name="veteran" class="form-select form-select-sm">
                                        <option value="">----</option>
                                        <option value="1" <?php if ($this->data['eeoVeteranTypeID'] == 1) echo('selected'); ?>>No</option>
                                        <option value="2" <?php if ($this->data['eeoVeteranTypeID'] == 2) echo('selected'); ?>>Eligible Veteran</option>
                                        <option value="3" <?php if ($this->data['eeoVeteranTypeID'] == 3) echo('selected'); ?>>Disabled Veteran</option>
                                        <option value="4" <?php if ($this->data['eeoVeteranTypeID'] == 4) echo('selected'); ?>>Eligible and Disabled</option>
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
                                    <select id="disability" name="disability" class="form-select form-select-sm">
                                        <option value="">----</option>
                                        <option value="No" <?php if ($this->data['eeoDisabilityStatus'] == 'No') echo('selected'); ?>>No</option>
                                        <option value="Yes" <?php if ($this->data['eeoDisabilityStatus'] == 'Yes') echo('selected'); ?>>Yes</option>
                                    </select>
                                </div>
                             </div>
                         <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="card card-body p-2 mb-2">

                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                        <div class="row g-2 align-items-start mb-2">
                            <div class="col-sm-4" id="extraFieldTd<?php echo($i); ?>">
                                <label id="extraFieldLbl<?php echo($i); ?>" class="form-label small mb-1">
                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                </label>
                            </div>
                            <div class="col-12 col-sm" id="extraFieldData<?php echo($i); ?>">
                                <?php echo($this->extraFieldRS[$i]['editHTML']); ?>
                            </div>
                        </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="canRelocateLabel" for="canRelocate" class="form-label small mb-1">Can Relocate:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="checkbox" id="canRelocate" name="canRelocate"<?php if ($this->data['canRelocate'] == 1): ?> checked<?php endif; ?> class="form-check-input">
                        </div>
                    </div>


                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="dateAvailableLabel" for="dateAvailable" class="form-label small mb-1">Date Available:</label>
                        </div>
                        <div class="col-12 col-sm">
<?php if (!empty($this->data['dateAvailable'])): ?>
                                <script>DateInput('dateAvailable', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), <?php echo json_encode((string) $this->data['dateAvailableUser'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, -1);</script>
                            <?php else: ?>
                                <script>DateInput('dateAvailable', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', -1);</script>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="currentEmployerLabel" for="currentEmployer" class="form-label small mb-1">Current Employer:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="currentEmployer" name="currentEmployer" value="<?php $this->_($this->data['currentEmployer']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="currentPayLabel" for="currentPay" class="form-label small mb-1">Current Pay:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" name="currentPay" id="currentPay" value="<?php $this->_($this->data['currentPay']); ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="desiredPayLabel" for="desiredPay" class="form-label small mb-1">Desired Pay:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" name="desiredPay" id="desiredPay" value="<?php $this->_($this->data['desiredPay']); ?>" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="keySkillsLabel" for="keySkills" class="form-label small mb-1">Key Skills:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <input type="text" class="form-control form-control-sm" id="keySkills" name="keySkills" value="<?php $this->_($this->data['keySkills']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4">
                            <label id="notesLabel" for="notes" class="form-label small mb-1">Misc. Notes:</label>
                        </div>
                        <div class="col-12 col-sm">
                            <textarea class="form-control form-control-sm" id="notes" name="notes" rows="5"><?php $this->_($this->data['notes']); ?></textarea>
                        </div>
                    </div>
                </div>
</div></div>
                <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save">Save</button>&nbsp;
                <button type="reset"  class="btn btn-sm btn-outline-secondary" name="reset"  id="reset"  value="Reset" onclick="resetFormForeign();">Reset</button>&nbsp;
                <button type="button" class="btn btn-sm btn-outline-secondary" name="back"   id="back"   value="Back to Details" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');">Back to Details</button>
            </form>

            <script>
                document.editCandidateForm.firstName.focus();
            </script>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
