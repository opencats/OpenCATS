<?php /* $Id: Edit.tpl 3810 2007-12-05 19:13:25Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Job Orders'), array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js',  'js/suggest.js', 'js/joborder.js', 'js/lib.js', 'js/listEditor.js', 'tinymce')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __('Job Orders').': '.__('Edit Job Order')?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Edit Job Order') ?></p>

            <form name="editJobOrderForm" id="editJobOrderForm" action="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editJobOrderForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />

                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical">
                            <label id="titleLabel" for="title"><?php _e('Title') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="1" class="inputbox" id="title" name="title" value="<?php $this->_($this->data['title']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="startDateLabel" for="startDate"><?php _e('Start Date') ?>:</label>
                        </td>
                        <td class="tdData">
                            <?php if (!empty($this->data['startDate'])): ?>
                                <script type="text/javascript">DateInput('startDate', false, 'MM-DD-YY', '<?php echo($this->data['startDateMDY']); ?>', 9);</script>
                            <?php else: ?>
                                <script type="text/javascript">DateInput('startDate', false, 'MM-DD-YY', '', 9);</script>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="companyIDLabel" for="companyID"><?php _e('Company') ?>:</label>
                        </td>

                        <td class="tdData">
                            <input type="hidden" name="companyID" id="companyID" value="<?php echo($this->data['companyID']); ?>" />

                            <?php if ($this->defaultCompanyID !== false): ?>
                                <input type="radio" name="typeCompany" <?php if ($this->defaultCompanyID != $this->data['companyID']) echo(' checked'); ?> onchange="document.getElementById('companyName').disabled = false; if (oldCompanyID != -1) document.getElementById('companyID').value = oldCompanyID;">
                                <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php $this->_($this->data['companyName']) ?>" class="inputbox" style="width: 125px" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' disabled'); ?>/>&nbsp;*
                            <?php else: ?>
                                <input type="text" name="companyName" id="companyName" tabindex="2" value="<?php $this->_($this->data['companyName']) ?>" class="inputbox" style="width: 150px" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' disabled'); ?>/>&nbsp;*
                            <?php endif; ?>
                            <br />
                            <iframe id="helpShim" src="javascript:void(0);" scrolling="no" frameborder="0" style="position:absolute; display:none;"></iframe>
                            <div id="CompanyResults" class="ajaxSearchResults"></div>
                        </td>

                        <td class="tdVertical">
                            <label id="durationLabel" for="duration"><?php _e('Duration') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="12" class="inputbox" id="duration" name="duration" value="<?php $this->_($this->data['duration']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                         <td class="tdVertical">
                            <label id="companyLabel" for=""></label>
                         </td>
                         <td class="tdData">
                            <?php if ($this->defaultCompanyID !== false): ?>
                                <input type="radio" name="typeCompany" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' checked'); ?> id="defaultCompany" onchange="if(document.getElementById('companyName').disabled == false && document.getElementById('companyID').value > 0) {oldCompanyID = document.getElementById('companyID').value; } else if(document.getElementById('companyName').disabled == false) { oldCompanyID = 0; } document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; ">&nbsp;Internal Posting
                            <?php endif; ?>
                            <script type="text/javascript">oldCompanyID = -1; watchCompanyIDChangeJO('<?php echo($this->sessionCookie); ?>');</script>
                         </td>


                        <td class="tdVertical">
                            <label id="maxRateLabel" for="maxRate"><?php _e('Maximum Rate') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="13" class="inputbox" id="maxRate" name="maxRate" value="<?php $this->_($this->data['maxRate']); ?>" style="width: 150px;" />
                        </td>

                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="departmentLabel" for="department"><?php _e('Department') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select id="departmentSelect" name="department" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <?php if ($this->data['departmentID'] == 0): ?>
                                    <option value="(none)" selected="selected"><?php _e('None')?></option>
                                <?php else: ?>
                                    <option value="(none)"><?php _e('_None')?></option>
                                <?php endif; ?>
                                <?php foreach ($this->departmentsRS as $index => $department): ?>
                                    <option value="<?php $this->_($department['name']); ?>" <?php if ($department['name'] == $this->data['department']): ?>selected<?php endif; ?>><?php $this->_($department['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php $this->_($this->departmentsString); ?>" />
                        </td>

                        <td class="tdVertical">
                            <label id="salaryLabel" for="salary"><?php _e('Salary') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="14" class="inputbox" id="salary" name="salary" value="<?php $this->_($this->data['salary']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="contactIDLabel" for="contactID"><?php _e('Contact') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="3" id="contactID" name="contactID" class="inputbox" style="width: 150px;">
                                <option value="-1"><?php _e('_None') ?></option>

                                <?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
                                    <?php if ($this->data['contactID'] == $contactsData['contactID']): ?>
                                        <option selected value="<?php $this->_($contactsData['contactID']) ?>"><?php $this->_($contactsData['lastName']) ?>, <?php $this->_($contactsData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($contactsData['contactID']) ?>"><?php $this->_($contactsData['lastName']) ?>, <?php $this->_($contactsData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;
                            <img src="images/indicator2.gif" id="contactsIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
                        </td>
                        <td class="tdVertical">
                            <label id="typeLabel" for="type"><?php _e('Type') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="15" id="type" name="type" class="inputbox" style="width: 150px;">
                                <?php if ($this->data['type'] == 'H'): ?>
                                    <option value="H" selected="selected"><?php _e('Hire') ?></option>
                                    <option value="C2H"><?php _e('Contract to Hire') ?></option>
                                    <option value="C"><?php _e('Contract') ?></option>
                                    <option value="FL"><?php _e('Freelance') ?></option>
                                <?php elseif ($this->data['type'] == 'C2H'): ?>
                                    <option value="H"><?php _e('Hire') ?></option>
                                    <option value="C2H" selected="selected"><?php _e('Contract to Hire') ?></option>
                                    <option value="C"><?php _e('Contract') ?></option>
                                    <option value="FL"><?php _e('Freelance') ?></option>
                                <?php elseif ($this->data['type'] == 'FL'): ?>
                                    <option value="H"><?php _e('Hire') ?></option>
                                    <option value="C2H"><?php _e('Contract to Hire') ?></option>
                                    <option value="C"><?php _e('Contract') ?></option>
                                    <option value="FL" selected="selected"><?php _e('Freelance') ?></option>
                                <?php else: ?>
                                    <option value="H"><?php _e('Hire') ?></option>
                                    <option value="C2H"><?php _e('Contract to Hire') ?></option>
                                    <option value="C" selected="selected"><?php _e('Contract') ?></option>
                                    <option value="FL"><?php _e('Freelance') ?></option>
                                <?php endif; ?>
                            </select>&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="cityLabel" for="city"><?php _e('City') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="4" class="inputbox" id="city" name="city" value="<?php $this->_($this->data['city']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="openingsLabel" for="openings"><?php _e('Total Openings') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="16" class="inputbox" id="openings" name="openings" value="<?php $this->_($this->data['openings']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="stateLabel" for="state"><?php _e('State') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" class="inputbox" id="state" name="state" value="<?php $this->_($this->data['state']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="openingsAvailableLabel" for="openingsAvailable"><?php _e('Remaining Openings') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="16" class="inputbox" id="openingsAvailable" name="openingsAvailable" value="<?php $this->_($this->data['openingsAvailable']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="recruiterLabel" for="recruiter"><?php _e('Recruiter') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="6" id="recruiter" name="recruiter" class="inputbox" style="width: 150px;">
                                <option value="">(<?php _e('Select a User') ?>)</option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                    <?php if ($this->data['recruiter'] == $usersData['userID']): ?>
                                        <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="companyJobIDLabel" for="openings"><?php _e('Company Job ID') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="17" class="inputbox" id="companyJobID" name="companyJobID" value="<?php $this->_($this->data['companyJobID']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="ownerLabel" for="owner"><?php _e('Owner') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="7" id="owner" name="owner" class="inputbox" style="width: 150px;" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                                <option value="-1"><?php _e('_None') ?></option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                    <?php if ($this->data['owner'] == $usersData['userID']): ?>
                                        <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;*
                            <div style="display:none;" id="divOwnershipChange">
                                <input type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?> /> <?php _e('E-Mail new owner of change') ?>
                            </div>
                        </td>

                        <td class="tdVertical">
                            <label id="isHotLabel" for="isHot"><?php _e('Hot') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" tabindex="18" id="isHot" name="isHot"<?php if ($this->data['isHot'] == 1): ?> checked<?php endif; ?> />&nbsp;
                            <img title="Checking this box indicates that the job order is 'hot', and shows up highlighted throughout the system." src="images/information.gif" alt="" width="16" height="16" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="statusLabel" for="status"><?php _e('Status') ?>:</label>
                        </td>
                        <td class="tdData">
                            <?php if(isset($this->overOpenJOQuota) && ($this->data['status'] == 'OnHold' || $this->data['status'] == 'Full' || $this->data['status'] == 'Closed' || $this->data['status'] == 'Canceled')): ?>
                                <select tabindex="8" id="status" name="status" class="inputbox" style="width: 150px;">
                                    <option <?php if ($this->data['status'] == 'OnHold'): ?>selected<?php endif; ?> value="OnHold"><?php _e('On Hold') ?></option>
                                    <option <?php if ($this->data['status'] == 'Full'): ?>selected<?php endif; ?> value="Full"><?php _e('Full') ?></option>
                                    <option <?php if ($this->data['status'] == 'Closed'): ?>selected<?php endif; ?> value="Closed"><?php _e('Closed') ?></option>
                                    <option <?php if ($this->data['status'] == 'Canceled'): ?>selected<?php endif; ?> value="Canceled"><?php _e('Canceled') ?></option>
                                </select>&nbsp;*<br />
                                <span style="font-size:10px;"><?php echo __('You have already reached your limit of %s open Job Orders, and cannot make this Job Order Active.', (FREE_ACCOUNT_JOBORDERS)) ?><br /></font>

                            <?php else: ?>
                                <select tabindex="8" id="status" name="status" class="inputbox" style="width: 150px;">
                                    <option <?php if ($this->data['status'] == 'Active'): ?>selected<?php endif; ?> value="Active"><?php _e('Active') ?></option>
                                    <option <?php if ($this->data['status'] == 'Upcoming'): ?>selected<?php endif; ?> value="Upcoming"><?php _e('Upcoming') ?></option>
                                    <option <?php if ($this->data['status'] == 'Lead'): ?>selected<?php endif; ?> value="Lead"><?php _e('Prospective') ?> / <?php _e('Lead') ?></option>
                                    <option <?php if ($this->data['status'] == 'OnHold'): ?>selected<?php endif; ?> value="OnHold"><?php _e('On Hold') ?></option>
                                    <option <?php if ($this->data['status'] == 'Full'): ?>selected<?php endif; ?> value="Full"><?php _e('Full') ?></option>
                                    <option <?php if ($this->data['status'] == 'Closed'): ?>selected<?php endif; ?> value="Closed"><?php _e('Closed') ?></option>
                                    <option <?php if ($this->data['status'] == 'Canceled'): ?>selected<?php endif; ?> value="Canceled"><?php _e('Canceled') ?></option>
                                </select>&nbsp;*
                            <?php endif; ?>
                        </td>

                        <td class="tdVertical">
                            <label id="publicLabel" for="public"><?php _e('Public') ?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" tabindex="19" id="public" name="public" onchange="checkPublic(this);" onclick="checkPublic(this);" onkeydown="checkPublic(this);"<?php if ($this->data['public'] == 1): ?> checked<?php endif; ?> />&nbsp;
                            <img title="Checking this box indicates that the job order is public. Job orders flaged as public will be able to be viewed by anonymous users." src="images/information.gif" alt="" width="16" height="16" />
                        </td>
                    </tr>

                    <?php eval(Hooks::get('JO_TEMPLATE_BOTTOM_OF_TOP')); ?>

                </table>

                <table class="editTable" width="700">

                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                        <tr>
                            <td class="tdVertical" id="extraFieldTd<?php echo($i); ?>">
                                <label id="extraFieldLbl<?php echo($i); ?>">
                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                </label>
                            </td>
                            <td class="tdData" id="extraFieldData<?php echo($i); ?>">
                                <?php echo($this->extraFieldRS[$i]['editHTML']); ?>
                            </td>
                        </tr>
                    <?php endfor; ?>

                    <tr>
                        <td class="tdVertical">
                            <label id="descriptionLabel" for="description"><?php _e('Description') ?>:</label>
                        </td>
                        <td class="tdData">
                            <textarea tabindex="20" class="mceEditor" name="description" id="description" rows="15" style="width: 500px;"><?php $this->_($this->data['description']); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes"><?php _e('Internal Notes') ?>:</label>
                        </td>
                        <td class="tdData">
                            <textarea tabindex="21" class="mceEditor" name="notes" id="notes" rows="5" style="width: 500px;"><?php $this->_($this->data['notes']); ?></textarea>
                        </td>
                    </tr>

                    <tr id="displayQuestionnaires" style="<?php if ($this->isPublic): ?>display: table-row;<?php else: ?>display: none;<?php endif; ?>">
                        <?php if ($this->careerPortalEnabled): ?>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes"><?php _e('Questionnaire') ?>:</label>
                        </td>
                        <td class="tdData">
                            <select id="questionnaire" name="questionnaire" class="inputbox" style="width: 500px;">
                            <option value="none"><?php _e('_None') ?></option>
                            <?php foreach ($this->questionnaires as $questionnaire): ?>
                                <option value="<?php echo $questionnaire['questionnaireID']; ?>"<?php if ($this->questionnaireID == $questionnaire['questionnaireID']) echo ' selected'; ?>><?php echo $questionnaire['title']; ?></option>
                            <?php endforeach; ?>
                            </select>
                            <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
                            <br />
                            <a href="<?php echo osatutil::getIndexName(); ?>?m=settings&a=careerPortalSettings" target="_blank"><?php _e('Questionnaires') ?>: <?php _e('Add') ?> / <?php _e('Edit') ?> / <?php _e('Delete') ?></a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                </table>
                <input type="submit" tabindex="22" class="button" name="submit" id="submit" value="<?php _e('Save') ?>" />&nbsp;
                <input type="reset"  tabindex="23" class="button" name="reset"  id="reset"  value="<?php _e('Reset') ?>" />&nbsp;
                <input type="button" tabindex="24" class="button" name="back"   id="back"   value="<?php _e('Back to Details') ?>" onclick="javascript:goToURL('<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->jobOrderID); ?>');" />
            </form>

            <script type="text/javascript">
                document.editJobOrderForm.title.focus();
            </script>
        </div>
<?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>