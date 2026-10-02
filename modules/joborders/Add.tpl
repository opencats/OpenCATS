<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js',  'js/company.js', 'js/sweetTitles.js', 'js/suggest.js', 'js/joborder.js', 'js/lib.js', 'js/listEditor.js', 'vendor/ckeditor/ckeditor/ckeditor.js', 'js/ckeditor-manager.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<script>
        window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
    </script>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-joborder-add-page">

    <div id="contents">
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Add Job Order</h1></section>

        <p class="small text-body-secondary">Add a new job order to the system.</p>

        <?php if ($this->noCompanies): ?>
        <div class="alert alert-info" role="alert">
            <span><span class="fw-semibold">You have not added any companies yet.</span> You can't add a job order until
            you add at least one company. Please go to the <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies">Companies</a>
            module and add a company.</span>
        </div>
        <?php else: ?>
        <form name="addJobOrderForm" id="addJobOrderForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=add" method="post" onsubmit="return checkAddForm(document.addJobOrderForm);" autocomplete="off">
            <input type="hidden" name="postback" id="postback" value="postback">

            <section class="card mb-2 oc-joborder-basic-information">
                <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Basic Information</div>
                <div class="card-body p-2">
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="titleLabel" for="title">Title: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="1" class="form-control form-control-sm" id="title" name="title" <?php if(isset($this->jobOrderSourceRS['title'])): ?>value="<?php $this->_($this->jobOrderSourceRS['title']); ?>"<?php endif; ?>>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="startDateLabel" for="startDate">Start Date:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <script>DateInput('startDate', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', 8);</script>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="companyIDLabel" for="companyName">Company: <span class="text-danger" title="Required">*</span></label>
                        </div>

                        <div class="col-sm-8 col-lg-4">
                            <input type="hidden" name="companyID" id="companyID" value="<?php if ($this->selectedCompanyID === false) { if (isset($this->jobOrderSourceRS['companyID'])) { echo ($this->jobOrderSourceRS['companyID']); } else { echo(0); } } else { echo($this->selectedCompanyID); } ?>">

                            <?php if ($this->defaultCompanyID !== false): ?>
                            <input class="form-check-input" type="radio" name="typeCompany" checked onchange="document.getElementById('companyName').disabled = false; if (oldCompanyID != -1) document.getElementById('companyID').value = oldCompanyID;">
                            <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php if ($this->selectedCompanyID !== false) { $this->_($this->companyRS['name']); } ?><?php if(isset($this->jobOrderSourceRS['companyName']) && $this->selectedCompanyID == false ): ?><?php $this->_($this->jobOrderSourceRS['companyName']); ?><?php endif; ?>" class="form-control form-control-sm d-inline-block w-75" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->selectedCompanyID !== false) { echo('disabled'); } ?>>
                            <?php else: ?>
                            <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php if ($this->selectedCompanyID !== false) { $this->_($this->companyRS['name']); } ?><?php if(isset($this->jobOrderSourceRS['companyName']) && $this->selectedCompanyID == false ): ?><?php $this->_($this->jobOrderSourceRS['companyName']); ?><?php endif; ?>" class="form-control form-control-sm d-inline-block w-75" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->selectedCompanyID !== false) { echo('disabled'); } ?>>
                            <?php endif; ?>
                            <br>
                            <iframe id="helpShim" src="javascript:void(0);" title="Company suggestions compatibility frame" class="position-absolute border-0" style="display:none;"></iframe>
                            <div id="CompanyResults" class="ajaxSearchResults">
                            </div>

                            <?php if ($this->defaultCompanyID !== false): ?>
                            <input class="form-check-input" type="radio" name="typeCompany" id="defaultCompany" onchange="if(document.getElementById('companyName').disabled == false && document.getElementById('companyID').value > 0) {oldCompanyID = document.getElementById('companyID').value; } else if(document.getElementById('companyName').disabled == false) { oldCompanyID = 0; } document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; ">&nbsp;<?php echo($this->defaultCompanyRS['name']); ?><br>
                            <?php endif; ?>

                            <script>oldCompanyID = -1; watchCompanyIDChangeJO('<?php echo($this->sessionCookie); ?>');</script>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="durationLabel" for="duration">Duration:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="11" class="form-control form-control-sm" id="duration" name="duration" <?php if(isset($this->jobOrderSourceRS['duration'])): ?>value="<?php $this->_($this->jobOrderSourceRS['duration']); ?>"<?php endif; ?>>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="departmentLabel" for="departmentSelect">Department:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select id="departmentSelect" name="department" class="form-select form-select-sm" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                            <option value="(none)" selected="selected">None</option>
                            </select>
                            <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php if ($this->selectedCompanyID !== false): $this->_($this->selectedDepartmentsString); endif; ?>">
                            <?php if ($this->selectedCompanyID !== false): ?>
                            <script>listEditorUpdateSelectFromCSV('departmentSelect', 'departmentsCSV', true, false);</script>
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="maxRateLabel" for="maxRate">Maximum Rate:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="12" class="form-control form-control-sm" id="maxRate" name="maxRate" <?php if(isset($this->jobOrderSourceRS['maxRate'])): ?>value="<?php $this->_($this->jobOrderSourceRS['maxRate']); ?>"<?php endif; ?>>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="contactIDLabel" for="contactID">Contact:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="3" id="contactID" name="contactID" class="form-select form-select-sm d-inline-block w-75">
                            <option value="-1">None</option>

                            <?php if ($this->selectedCompanyID !== false): ?>
                            <?php foreach ($this->selectedCompanyContacts as $rowNumber => $contactsData): ?>
                            <option value="<?php $this->_($contactsData['contactID']) ?>"><?php $this->_($contactsData['lastName']) ?>, <?php $this->_($contactsData['firstName']) ?></option>
                            <?php endforeach; ?>
                            <?php endif; ?>
                            </select>&nbsp;
                            <img src="images/indicator2.gif" id="contactsIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16">
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="typeLabel" for="type">Type: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="7" id="type" name="type" class="form-select form-select-sm">
                            <?php foreach($this->jobTypes as $jobTypeShort => $jobTypeLong): ?>
                            <option value="<?php echo $jobTypeShort ?>"
                            <?php if(isset($this->jobOrderSourceRS['type']) && $this->jobOrderSourceRS['type'] == $jobTypeShort) echo('selected'); ?>>
                            <?php echo $jobTypeShort." (".$jobTypeLong.")";?>
                            </option>
                            <?php endforeach; ?>
                            <?php if(count($this->jobTypes) < 1): ?>
                            <option value="N/A" selected>N/A (Not Applicable)</option>
                            <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="cityLabel" for="city">City: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php if ($this->selectedCompanyID !== false): ?>
                            <input type="text" tabindex="4" class="form-control form-control-sm" id="city" name="city" value="<?php $this->_($this->selectedCompanyLocation['city']); ?>">
                            <?php else: ?>
                            <input type="text" tabindex="4" class="form-control form-control-sm" id="city" name="city">
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="salaryLabel" for="salary">Salary:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="13" class="form-control form-control-sm" id="salary" name="salary" <?php if(isset($this->jobOrderSourceRS['salary'])): ?>value="<?php $this->_($this->jobOrderSourceRS['salary']); ?>"<?php endif; ?>>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="stateLabel" for="state">State:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php if ($this->selectedCompanyID !== false): ?>
                            <input type="text" tabindex="5" class="form-control form-control-sm" id="state" name="state" value="<?php $this->_($this->selectedCompanyLocation['state']); ?>">
                            <?php else: ?>
                            <input type="text" tabindex="5" class="form-control form-control-sm" id="state" name="state">
                            <?php endif; ?>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="openingsLabel" for="openings">Openings: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="14" class="form-control form-control-sm" id="openings" name="openings" <?php if(isset($this->jobOrderSourceRS['openings'])): ?>value="<?php $this->_($this->jobOrderSourceRS['openings']); ?>"<?php else: ?>value="1"<?php endif; ?>>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="countryLabel" for="country">Country:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php
                                    $selectedCountry = '';
                                    if ($this->selectedCompanyID !== false && isset($this->selectedCompanyLocation['country']))
                                    {
                                        $selectedCountry = $this->selectedCompanyLocation['country'];
                                    }
                                    else if (isset($this->jobOrderSourceRS['country']))
                                    {
                                        $selectedCountry = $this->jobOrderSourceRS['country'];
                                    }
                                    echo TemplateUtility::getCountrySelectHTML('country', $selectedCountry, true, 'form-select form-select-sm', '');
                                ?>
                        </div>
                        <div class="col-sm-4 col-lg-2 fw-semibold">&nbsp;</div>
                        <div class="col-sm-8 col-lg-4">&nbsp;</div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="recruiterLabel" for="recruiter">Recruiter: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="6" id="recruiter" name="recruiter" class="form-select form-select-sm">
                            <option value="">(Select a User)</option>

                            <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                            <?php if ($usersData['userID'] == $this->userID): ?>
                            <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php else: ?>
                            <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="companyJobIDLabel" for="companyJobID">Company Job ID:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="15" class="form-control form-control-sm" id="companyJobID" name="companyJobID">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="ownerLabel" for="owner">Owner: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="6" id="owner" name="owner" class="form-select form-select-sm">
                            <option value="">(Select a User)</option>

                            <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                            <?php if ($usersData['userID'] == $this->userID): ?>
                            <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php else: ?>
                            <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="isHotLabel" for="isHot">Hot:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input class="form-check-input" type="checkbox" tabindex="16" id="isHot" name="isHot">&nbsp;
                            <img title="Checking this box indicates that the job order is 'hot', and shows up highlighted throughout the system." src="images/information.gif" alt="" width="16" height="16">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">

                        </div>
                        <div class="col-sm-8 col-lg-4">

                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="publicLabel" for="public">Public:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input class="form-check-input" type="checkbox" tabindex="17" id="public" name="public" onchange="checkPublic(this);" onclick="checkPublic(this);" onkeydown="checkPublic(this);">&nbsp;
                            <img title="Checking this box indicates that the job order is public. Job orders flaged as public will be able to be viewed by anonymous users." src="images/information.gif" alt="" width="16" height="16">
                        </div>
                    </div>
                </div>
            </section>

            <section class="card mb-2 oc-joborder-other">
                <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Other</div>
                <div class="card-body p-2">

                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 fw-semibold" id="extraFieldTd<?php echo($i); ?>">
                            <label class="form-label small mb-1" id="extraFieldLbl<?php echo($i); ?>">
                            <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                            </label>
                        </div>
                        <div class="col-sm-8 " id="extraFieldData<?php echo($i); ?>">
                            <?php echo($this->extraFieldRS[$i]['addHTML']); ?>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 fw-semibold">
                            <label class="form-label small mb-1" id="descriptionLabel" for="description">Description:</label>
                        </div>
                        <div class="col-sm-8 ">
                            <textarea tabindex="18" class="form-control form-control-sm ckEditor" name="description" id="description" rows="15"><?php if(isset($this->jobOrderSourceRS['description'])): ?><?php $this->_($this->jobOrderSourceRS['description']); ?><?php endif; ?></textarea>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 fw-semibold">
                            <label class="form-label small mb-1" id="notesLabel" for="notes">Internal Notes:</label>
                        </div>
                        <div class="col-sm-8 ">
                            <textarea tabindex="19" class="form-control form-control-sm ckEditor" name="notes" id="notes" rows="5"><?php if(isset($this->jobOrderSourceRS['notes'])): ?><?php $this->_($this->jobOrderSourceRS['notes']); ?><?php endif; ?></textarea>
                        </div>
                    </div>

                </div>
            </section>
            <div class="table-responsive mb-2">
                <table class="table table-sm mb-0 oc-joborder-questionnaire"><tbody>
                        <tr id="displayQuestionnaires" style="display: none;">
                            <?php if ($this->careerPortalEnabled): ?>
                            <td class="small fw-semibold">
                                <label class="form-label small mb-1" id="questionnaireLabel" for="questionnaire">Questionnaire:</label>
                            </td>
                            <td>
                                <select id="questionnaire" name="questionnaire" class="form-select form-select-sm">
                                <option value="none" selected>None</option>
                                <?php foreach ($this->questionnaires as $questionnaire): ?>
                                <option value="<?php echo $questionnaire['questionnaireID']; ?>"><?php echo $questionnaire['title']; ?></option>
                                <?php endforeach; ?>
                                </select>
                                <?php if ($this->getUserAccessLevel('settings.careerPortalSettings') >= ACCESS_LEVEL_SA): ?>
                                <br>
                                <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalSettings" target="_blank">Add / Edit / Delete Questionnaires</a>
                                <?php endif; ?>
                            </td>
                            <?php endif; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
            <button type="submit" tabindex="20" class="btn btn-sm btn-primary" name="submit" value="Add Job Order">Add Job Order</button>&nbsp;
            <button type="reset"  tabindex="21" class="btn btn-sm btn-outline-secondary" name="reset"  value="Reset">Reset</button>&nbsp;
            <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders&a=listByView'); ?>">Back to Job Orders</a>
        </form>

        <script>
                    placeCkEditorIn('description');
                </script>

        <script>
                    document.addJobOrderForm.title.focus();
                    <?php if (isset($this->jobOrderSourceRS['companyID'])): ?>updateCompanyData('<?php echo($this->sessionCookie); ?>');<?php endif; ?>
                </script>

        <?php endif; ?>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
