<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js',  'js/suggest.js', 'js/joborder.js', 'js/lib.js', 'js/listEditor.js', 'vendor/ckeditor/ckeditor/ckeditor.js', 'js/ckeditor-manager.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<script>
        window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
    </script>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-joborder-edit-page">

    <div id="contents">
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Edit Job Order</h1></section>

        <p class="small text-body-secondary">Edit Job Order</p>

        <form name="editJobOrderForm" id="editJobOrderForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editJobOrderForm);" autocomplete="off">
            <input type="hidden" name="postback" id="postback" value="postback">
            <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>">

            <section class="card mb-2 oc-joborder-basic-information">
                <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Basic Information</div>
                <div class="card-body p-2">
                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="titleLabel" for="title">Title: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="1" class="form-control form-control-sm" id="title" name="title" value="<?php $this->_($this->data['title']); ?>">
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="startDateLabel" for="startDate">Start Date:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php if (!empty($this->data['startDate'])): ?>
                            <script>DateInput('startDate', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), <?php echo json_encode((string) $this->data['startDateUser'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, 9);</script>
                            <?php else: ?>
                            <script>DateInput('startDate', false, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', 9);</script>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="companyIDLabel" for="companyName">Company: <span class="text-danger" title="Required">*</span></label>
                        </div>

                        <div class="col-sm-8 col-lg-4">
                            <input type="hidden" name="companyID" id="companyID" value="<?php echo($this->data['companyID']); ?>">

                            <?php if ($this->defaultCompanyID !== false): ?>
                            <input class="form-check-input" type="radio" name="typeCompany" <?php if ($this->defaultCompanyID != $this->data['companyID']) echo(' checked'); ?> onchange="document.getElementById('companyName').disabled = false; if (oldCompanyID != -1) document.getElementById('companyID').value = oldCompanyID;">
                            <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php $this->_($this->data['companyName']) ?>" class="form-control form-control-sm d-inline-block w-75" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' disabled'); ?>>
                            <?php else: ?>
                            <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php $this->_($this->data['companyName']) ?>" class="form-control form-control-sm d-inline-block w-75" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' disabled'); ?>>
                            <?php endif; ?>
                            <br>
                            <iframe id="helpShim" src="javascript:void(0);" title="Company suggestions compatibility frame" class="position-absolute border-0" style="display:none;"></iframe>
                            <div id="CompanyResults" class="ajaxSearchResults">
                            </div>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="durationLabel" for="duration">Duration:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="12" class="form-control form-control-sm" id="duration" name="duration" value="<?php $this->_($this->data['duration']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php if ($this->defaultCompanyID !== false): ?>
                            <input class="form-check-input" type="radio" name="typeCompany" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' checked'); ?> id="defaultCompany" onchange="if(document.getElementById('companyName').disabled == false && document.getElementById('companyID').value > 0) {oldCompanyID = document.getElementById('companyID').value; } else if(document.getElementById('companyName').disabled == false) { oldCompanyID = 0; } document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; ">&nbsp;<?php echo($this->defaultCompanyRS['name']); ?>
                            <?php endif; ?>
                            <script>oldCompanyID = -1; watchCompanyIDChangeJO('<?php echo($this->sessionCookie); ?>');</script>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="maxRateLabel" for="maxRate">Maximum Rate:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="13" class="form-control form-control-sm" id="maxRate" name="maxRate" value="<?php $this->_($this->data['maxRate']); ?>">
                        </div>

                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="departmentLabel" for="departmentSelect">Department:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select id="departmentSelect" name="department" class="form-select form-select-sm" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                            <?php if ($this->data['departmentID'] == 0): ?>
                            <option value="(none)" selected="selected">None</option>
                            <?php else: ?>
                            <option value="(none)">None</option>
                            <?php endif; ?>
                            <?php foreach ($this->departmentsRS as $index => $department): ?>
                            <option value="<?php $this->_($department['name']); ?>" <?php if ($department['name'] == $this->data['department']): ?>selected<?php endif; ?>><?php $this->_($department['name']); ?></option>
                            <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php $this->_($this->departmentsString); ?>">
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="salaryLabel" for="salary">Salary:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="14" class="form-control form-control-sm" id="salary" name="salary" value="<?php $this->_($this->data['salary']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="contactIDLabel" for="contactID">Contact:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="3" id="contactID" name="contactID" class="form-select form-select-sm d-inline-block w-75">
                            <option value="-1">None</option>

                            <?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
                            <?php if ($this->data['contactID'] == $contactsData['contactID']): ?>
                            <option selected value="<?php $this->_($contactsData['contactID']) ?>"><?php $this->_($contactsData['lastName']) ?>, <?php $this->_($contactsData['firstName']) ?></option>
                            <?php else: ?>
                            <option value="<?php $this->_($contactsData['contactID']) ?>"><?php $this->_($contactsData['lastName']) ?>, <?php $this->_($contactsData['firstName']) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            </select>&nbsp;
                            <img src="images/indicator2.gif" id="contactsIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16">
                        </div>
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="typeLabel" for="type">Type: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="15" id="type" name="type" class="form-select form-select-sm">
                            <?php foreach($this->jobTypes as $jobTypeShort => $jobTypeLong): ?>
                            <option value="<?php echo $jobTypeShort;?>"
                            <?php if($this->data['type'] == $jobTypeShort): ?>
                            selected="selected"
                            <?php endif; ?>
                            ><?php echo $jobTypeShort." (".$jobTypeLong.")";?>
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
                            <input type="text" tabindex="4" class="form-control form-control-sm" id="city" name="city" value="<?php $this->_($this->data['city']); ?>">
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="openingsLabel" for="openings">Total Openings: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="16" class="form-control form-control-sm" id="openings" name="openings" value="<?php $this->_($this->data['openings']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="stateLabel" for="state">State:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="5" class="form-control form-control-sm" id="state" name="state" value="<?php $this->_($this->data['state']); ?>">
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="openingsAvailableLabel" for="openingsAvailable">Remaining Openings: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input type="text" tabindex="16" class="form-control form-control-sm" id="openingsAvailable" name="openingsAvailable" value="<?php $this->_($this->data['openingsAvailable']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="countryLabel" for="country">Country:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <?php echo TemplateUtility::getCountrySelectHTML('country', $this->data['country'], true, 'form-select form-select-sm', ''); ?>
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
                            <?php if ($this->data['recruiter'] == $usersData['userID']): ?>
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
                            <input type="text" tabindex="17" class="form-control form-control-sm" id="companyJobID" name="companyJobID" value="<?php $this->_($this->data['companyJobID']); ?>">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="ownerLabel" for="owner">Owner: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="7" id="owner" name="owner" class="form-select form-select-sm" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                            <option value="-1">None</option>

                            <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                            <?php if ($this->data['owner'] == $usersData['userID']): ?>
                            <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php else: ?>
                            <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                            <?php endif; ?>
                            <?php endforeach; ?>
                            </select>
                            <div style="display:none;" id="divOwnershipChange">
                                <input class="form-check-input" type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?>> E-Mail new owner of change
                            </div>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="isHotLabel" for="isHot">Hot:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input class="form-check-input" type="checkbox" tabindex="18" id="isHot" name="isHot"<?php if ($this->data['isHot'] == 1): ?> checked<?php endif; ?>>&nbsp;
                            <img title="Checking this box indicates that the job order is 'hot', and shows up highlighted throughout the system." src="images/information.gif" alt="" width="16" height="16">
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="statusLabel" for="status">Status: <span class="text-danger" title="Required">*</span></label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <select tabindex="8" id="status" name="status" class="form-select form-select-sm">
                            <?php foreach($this->jobOrderStatuses as $statusTypeName => $statusType){
                                    echo("<optgroup label=".$statusTypeName.">");
                                    foreach($statusType as $status){
                                        $selected = "";
                                        if ($this->data['status'] == $status){
                                            $selected = "selected ";
                                        }
                                        echo('<option '.$selected.'value="'.$status.'">'.$status.'</option>');
                                    }
                                    echo("</optgroup>");
                                }?>
                            </select>
                        </div>

                        <div class="col-sm-4 col-lg-2 fw-semibold">
                            <label class="form-label small mb-1" id="publicLabel" for="public">Public:</label>
                        </div>
                        <div class="col-sm-8 col-lg-4">
                            <input class="form-check-input" type="checkbox" tabindex="19" id="public" name="public" onchange="checkPublic(this);" onclick="checkPublic(this);" onkeydown="checkPublic(this);"<?php if ($this->data['public'] == 1): ?> checked<?php endif; ?>>&nbsp;
                            <img title="Checking this box indicates that the job order is public. Job orders flaged as public will be able to be viewed by anonymous users." src="images/information.gif" alt="" width="16" height="16">
                        </div>
                    </div>

                    <?php eval(Hooks::get('JO_TEMPLATE_BOTTOM_OF_TOP')); ?>

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
                            <?php echo($this->extraFieldRS[$i]['editHTML']); ?>
                        </div>
                    </div>
                    <?php endfor; ?>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 fw-semibold">
                            <label class="form-label small mb-1" id="descriptionLabel" for="description">Description:</label>
                        </div>
                        <div class="col-sm-8 ">
                            <textarea tabindex="20" class="form-control form-control-sm ckEditor" name="description" id="description" rows="15"><?php $this->_($this->data['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="row g-2 align-items-start mb-2">
                        <div class="col-sm-4 fw-semibold">
                            <label class="form-label small mb-1" id="notesLabel" for="notes">Internal Notes:</label>
                        </div>
                        <div class="col-sm-8 ">
                            <textarea tabindex="21" class="form-control form-control-sm ckEditor" name="notes" id="notes" rows="5"><?php $this->_($this->data['notes']); ?></textarea>
                        </div>
                    </div>

                </div>
            </section>
            <div class="table-responsive mb-2">
                <table class="table table-sm mb-0 oc-joborder-questionnaire"><tbody>
                        <tr id="displayQuestionnaires" style="<?php if ($this->isPublic): ?>display: table-row;<?php else: ?>display: none;<?php endif; ?>">
                            <?php if ($this->careerPortalEnabled): ?>
                            <td class="small fw-semibold">
                                <label class="form-label small mb-1" id="questionnaireLabel" for="questionnaire">Questionnaire:</label>
                            </td>
                            <td>
                                <select id="questionnaire" name="questionnaire" class="form-select form-select-sm">
                                <option value="none">None</option>
                                <?php foreach ($this->questionnaires as $questionnaire): ?>
                                <option value="<?php echo $questionnaire['questionnaireID']; ?>"<?php if ($this->questionnaireID == $questionnaire['questionnaireID']) echo ' selected'; ?>><?php echo $questionnaire['title']; ?></option>
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
            <button type="submit" tabindex="22" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save">Save</button>&nbsp;
            <button type="reset"  tabindex="23" class="btn btn-sm btn-outline-secondary" name="reset"  id="reset"  value="Reset">Reset</button>&nbsp;
            <a id="back" class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . $this->jobOrderID); ?>">Back to Details</a>
        </form>

        <script>
                placeCkEditorIn('description');
            </script>

        <script>
                document.editJobOrderForm.title.focus();
            </script>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
