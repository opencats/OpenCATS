<?php TemplateUtility::printHeader('Contacts', array('modules/contacts/validator.js', 'js/sweetTitles.js', 'js/suggest.js', 'js/listEditor.js',  'js/contact.js', 'js/company.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-contact-edit-page">

    <div id="contents">
        <section class="oc-page-header mb-2">
            <h1 class="h5 fw-semibold mb-0">Edit Contact</h1>
        </section>

        <form name="editContactForm" id="editContactForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editContactForm);" autocomplete="off">
            <input type="hidden" name="postback" id="postback" value="postback">
            <input type="hidden" name="contactID" id="contactID" value="<?php echo($this->contactID); ?>">

            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    <section class="card mb-2">
                        <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Basic Information</div>
                        <div class="card-body p-2">
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="firstNameLabel" for="firstName">First Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="firstName" id="firstName" value="<?php $this->_($this->data['firstName']); ?>" class="form-control form-control-sm"><span class="text-danger" title="Required">*</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="lastNameLabel" for="lastName">Last Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="lastName" id="lastName" value="<?php $this->_($this->data['lastName']); ?>" class="form-control form-control-sm"><span class="text-danger" title="Required">*</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="companyIDLabel" for="companyName"><span id="companyAssociatedLabel" <?php if ($this->data['leftCompany'] != 1): ?> style="display:none;" <?php endif; ?> >Previous </span>Company:</label>
                                </div>

                                <div class="col-sm-8">
                                    <input type="hidden" name="companyID" id="companyID" value="<?php $this->_($this->data['companyID']); ?>">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="companyName" id="companyName" value="<?php $this->_($this->data['companyName']); ?>" class="form-control form-control-sm" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo('disabled'); ?>><span class="text-danger" title="Required">*</span>
                                    </div>
                                    <?php if ($this->defaultCompanyID !== false): ?>
                                    <input type="checkbox" class="form-check-input" id="defaultCompany" onchange="if (this.checked) { document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; document.getElementById('companyName').value = &quot;<?php $this->_($this->defaultCompanyRS['name']); ?>&quot;; } else { document.getElementById('companyName').disabled = false; }"<?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' checked'); ?>> <label class="form-label small mb-0" for="defaultCompany">Internal Contact</label>
                                    <?php endif; ?>
                                    <script>watchCompanyIDChange('<?php echo($this->sessionCookie); ?>');</script>
                                    <br>
                                    <iframe id="helpShim" src="javascript:void(0);" title="Company suggestions compatibility frame" class="position-absolute border-0" style="display:none;"></iframe>
                                    <div id="CompanyResults" class="ajaxSearchResults"></div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="titleLabel" for="title">Title:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="title" id="title" value="<?php $this->_($this->data['title']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="departmentLabel" for="departmentSelect">Department:</label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="departmentSelect" name="department" class="form-select form-select-sm" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                        <option value="edit">(Edit Departments)</option>
                                        <option value="nullline">-------------------------------</option>
                                        <?php if ($this->data['departmentID'] == 0): ?>
                                        <option value="(none)" selected>(None)</option>
                                        <?php else: ?>
                                        <option value="(none)">(None)</option>
                                        <?php endif; ?>
                                        <?php foreach ($this->departmentsRS as $index => $department): ?>
                                        <option value="<?php $this->_($department['name']); ?>" <?php if ($department['name'] == $this->data['department']): ?>selected<?php endif; ?>><?php $this->_($department['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php $this->_($this->departmentsString); ?>">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="reportsToLabel" for="reportsTo">Reports to:</label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="reportsTo" name="reportsTo" class="form-select form-select-sm" >
                                        <?php if ($this->data['reportsTo'] == -1): ?>
                                        <option value="(none)" selected>(None)</option>
                                        <?php else: ?>
                                        <option value="(none)">(None)</option>
                                        <?php endif; ?>
                                        <?php foreach ($this->reportsToRS as $index => $contact): ?>
                                        <?php if ($contact['contactID'] != $this->contactID): ?>
                                        <option value="<?php $this->_($contact['contactID']); ?>" <?php if ($contact['contactID'] == $this->data['reportsTo']): ?>selected<?php endif; ?>><?php $this->_($contact['firstName'] . ' ' . $contact['lastName']); ?></option>
                                        <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    &nbsp; <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicatorReportsTo" class="align-middle ms-1" style="visibility: hidden;">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="isHotLabel" for="isHot">Hot Contact:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input" id="isHot" name="isHot"<?php if ($this->data['isHotContact'] == 1): ?> checked<?php endif; ?>>&nbsp;
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4"><label class="form-label small mb-0" for="leftCompany">Left Company:</label></div>
                                <div class="col-sm-8">
                                    <input type="checkbox" class="form-check-input" id="leftCompany" name="leftCompany"<?php if ($this->data['leftCompany'] == 1): ?> checked<?php endif; ?> onclick="if (document.getElementById('leftCompany').checked) document.getElementById('companyAssociatedLabel').style.display=''; else document.getElementById('companyAssociatedLabel').style.display='none';">&nbsp;
                                </div>
                            </div>

                        </div></section>
                </div>
                <div class="col-12 col-lg-6">
                    <section class="card mb-2">
                        <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Contact Information</div>
                        <div class="card-body p-2">
                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="email1Label" for="email1">E-Mail:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="email1" id="email1" value="<?php $this->_($this->data['email1']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="email2Label" for="email2">2nd E-Mail:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="email2" id="email2" value="<?php $this->_($this->data['email2']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneWorkLabel" for="phoneWork">Work Phone:</label>
                                </div>
                                <div class="col-sm-8"><input type="text" name="phoneWork" id="phoneWork" value="<?php $this->_($this->data['phoneWork']); ?>" class="form-control form-control-sm"></div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneCellLabel" for="phoneCell">Cell Phone:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="phoneCell" id="phoneCell" value="<?php $this->_($this->data['phoneCell']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneOtherLabel" for="phoneOther">Other Phone:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="phoneOther" id="phoneOther" value="<?php $this->_($this->data['phoneOther']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="addressLabel" for="address">Address:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="address" id="address" class="form-control form-control-sm" value="<?php $this->_($this->data['address']); ?>">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="address2Label" for="address2">Address 2:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="address2" id="address2" class="form-control form-control-sm" value="<?php $this->_($this->data['address2']); ?>">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="cityLabel" for="city">City:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="city" id="city" value="<?php $this->_($this->data['city']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="stateLabel" for="state">State:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="state" id="state" value="<?php $this->_($this->data['state']); ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="zipLabel" for="zip">Postal Code:</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="zip" id="zip" value="<?php $this->_($this->data['zip']); ?>" class="form-control form-control-sm">
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="CityState_populate('zip', 'ajaxIndicator');">Lookup</button>
                                    </div>
                                    <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" class="align-middle ms-1" style="visibility: hidden;">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="countryLabel" for="country">Country:</label>
                                </div>
                                <div class="col-sm-8">
                                    <?php echo TemplateUtility::getCountrySelectHTML('country', $this->data['country'], true, 'form-select form-select-sm', ''); ?>
                                </div>
                            </div>
                        </div></section>
                </div>
            </div>

            <section class="card mb-2">
                <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Other</div>
                <div class="card-body p-2">

                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4" id="extraFieldTd<?php echo($i); ?>">
                            <label class="form-label small mb-0" id="extraFieldLbl<?php echo($i); ?>">
                                <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                            </label>
                        </div>
                        <div class="col-sm-8" id="extraFieldData<?php echo($i); ?>">
                            <?php echo($this->extraFieldRS[$i]['editHTML']); ?>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4">
                            <label class="form-label small mb-0" id="ownerLabel" for="owner">Owner:</label>
                        </div>
                        <div class="col-sm-8">
                            <select id="owner" name="owner" class="form-select form-select-sm" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                                <option value="-1">None</option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                <?php if ($this->data['owner'] == $usersData['userID']): ?>
                                <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                <?php else: ?>
                                <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </select><span class="text-danger" title="Required">*</span>
                            <div style="display:none;" id="divOwnershipChange">
                                <input type="checkbox" class="form-check-input" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?>> <label class="form-label small mb-0" for="checkboxOwnershipChange">E-Mail new owner of change</label>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4">
                            <label class="form-label small mb-0" id="notesLabel" for="notes">Misc. Notes:</label>
                        </div>
                        <div class="col-sm-8">
                            <textarea class="form-control form-control-sm" name="notes" id="notes" rows="5"><?php $this->_($this->data['notes']); ?></textarea>
                        </div>
                    </div>
                </div></section>
            <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save">Save</button>&nbsp;
            <button type="reset"  class="btn btn-sm btn-outline-secondary" name="reset"  id="reset"  value="Reset">Reset</button>&nbsp;
            <a class="btn btn-sm btn-outline-secondary"   id="back"   href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->contactID); ?>">Back to Details</a>
        </form>

        <script>
            document.editContactForm.firstName.focus();
        </script>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
