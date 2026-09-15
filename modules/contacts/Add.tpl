<?php TemplateUtility::printHeader('Contacts', array('modules/contacts/validator.js', 'js/company.js', 'js/sweetTitles.js', 'js/listEditor.js',  'js/contact.js', 'js/suggest.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-contact-add-page">

    <div id="contents">
        <section class="oc-page-header mb-2">
            <h1 class="h5 fw-semibold mb-0">Add Contact</h1>
        </section>

        <form name="addContactForm" id="addContactForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=add&amp;v=<?php if ($this->selectedCompanyID === false) { echo('-1'); } else { echo($this->selectedCompanyID); } ?>" method="post" onsubmit="return checkAddForm(document.addContactForm);" autocomplete="off">
            <input type="hidden" name="postback" id="postback" value="postback">
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
                                        <input type="text" name="firstName" id="firstName" class="form-control form-control-sm"><span class="text-danger" title="Required">*</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="lastNameLabel" for="lastName">Last Name:</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="lastName" id="lastName" class="form-control form-control-sm"><span class="text-danger" title="Required">*</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="companyIDLabel" for="companyName">Company:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="hidden" name="companyID" id="companyID" value="<?php if ($this->selectedCompanyID === false) { echo(0); } else { echo($this->selectedCompanyID); } ?>">
                                    <div class="d-flex align-items-center gap-1">
                                        <input type="text" name="companyName" id="companyName" value="<?php if ($this->selectedCompanyID !== false) { $this->_($this->companyRS['name']); } ?>" class="form-control form-control-sm" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->selectedCompanyID !== false) { echo('disabled'); } ?>><span class="text-danger" title="Required">*</span>
                                    </div>
                                    <?php if ($this->defaultCompanyID !== false && $this->selectedCompanyID === false): ?>
                                    <input type="checkbox" class="form-check-input" id="defaultCompany" onchange="if (this.checked){ document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; document.getElementById('companyName').value = &quot;<?php $this->_($this->defaultCompanyRS['name']); ?>&quot;; } else { document.getElementById('companyName').disabled = false; }"> <label class="form-label small mb-0" for="defaultCompany">Internal Contact</label>
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
                                    <input type="text" name="title" id="title" class="form-control form-control-sm">
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
                                        <option value="(none)" selected>(None)</option>
                                    </select>
                                    <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="reportsToLabel" for="reportsTo">Reports to:</label>
                                </div>
                                <div class="col-sm-8">
                                    <select id="reportsTo" name="reportsTo" class="form-select form-select-sm" >
                                        <option value="(none)" selected>(None)</option>
                                        <?php foreach ($this->reportsToRS as $index => $contact): ?>
                                        <option value="<?php $this->_($contact['contactID']); ?>"><?php $this->_($contact['firstName'] . ' ' . $contact['lastName']); ?></option>
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
                                    <input type="checkbox" class="form-check-input" id="isHot" name="isHot">&nbsp;
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
                                    <input type="text" name="email1" id="email1" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="email2Label" for="email2">2nd E-Mail:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="email2" id="email2" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneWorkLabel" for="phoneWork">Work Phone:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="phoneWork" id="phoneWork" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneCellLabel" for="phoneCell">Cell Phone:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="phoneCell" id="phoneCell" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="phoneOtherLabel" for="phoneOther">Other Phone:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="phoneOther" id="phoneOther" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="addressLabel" for="address">Address:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="address" id="address" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="address2Label" for="address2">Address 2:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="address2" id="address2" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="cityLabel" for="city">City:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="city" id="city" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="stateLabel" for="state">State:</label>
                                </div>
                                <div class="col-sm-8">
                                    <input type="text" name="state" id="state" class="form-control form-control-sm">
                                </div>
                            </div>

                            <div class="row g-2 align-items-center mb-2">
                                <div class="col-sm-4">
                                    <label class="form-label small mb-0" id="zipLabel" for="zip">Postal Code:</label>
                                </div>
                                <div class="col-sm-8">
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="zip" id="zip" class="form-control form-control-sm">
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
                                    <?php echo TemplateUtility::getCountrySelectHTML('country', '', true, 'form-select form-select-sm', ''); ?>
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
                            <?php echo($this->extraFieldRS[$i]['addHTML']); ?>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-sm-4">
                            <label class="form-label small mb-0" id="notesLabel" for="notes">Misc. Notes:</label>
                        </div>
                        <div class="col-sm-8">
                            <textarea class="form-control form-control-sm" name="notes" id="notes" rows="5"></textarea>
                        </div>
                    </div>
                </div></section>
            <button type="submit" class="btn btn-sm btn-primary">Add Contact</button>&nbsp;
            <button type="reset"  class="btn btn-sm btn-outline-secondary">Reset</button>&nbsp;
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=listRecent">Back to Contacts</a>
        </form>

        <script>
            document.addContactForm.firstName.focus();
            <?php if ($this->selectedCompanyID !== false): ?>
            ContactDepartments_populate(<?php echo($this->selectedCompanyID); ?>, '<?php echo($this->sessionCookie); ?>');
            <?php endif; ?>
        </script>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
