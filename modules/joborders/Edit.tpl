<?php /* $Id: Edit.tpl 3810 2007-12-05 19:13:25Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Job Orders'), array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js',  'js/suggest.js', 'js/joborder.js', 'js/lib.js', 'js/listEditor.js', 'ckeditor/ckeditor.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); 
$editWidth = 1000;

function tplActions($thisTpl) {
?>
                <input type="submit" tabindex="22" class="button" name="submit" id="submit" value="<?php echo __("Save");?>" />&nbsp;
                <input type="reset"  tabindex="23" class="button" name="reset"  id="reset"  value="<?php echo __("Reset");?>" />&nbsp;
                <input type="button" tabindex="24" class="button" name="back"   id="back"   value="<?php echo __("Back to Details");?>" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>');" />
<?php
}

?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="<?php echo __("Job Orders");?>" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Job Orders");?>: <?php echo __("Edit Job Order");?></h2></td>
                </tr>
            </table>

            <p class="note"><?php echo __("Edit Job Order");?></p>

            <form name="editJobOrderForm" id="editJobOrderForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editJobOrderForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="jobOrderID" name="jobOrderID" value="<?php echo($this->jobOrderID); ?>" />
                
                <?php tplActions($this);?><br/>

                <table class="editTable" width="<?php echo $editWidth;?>">
                    <tr>
                        <td class="tdVertical">
                            <label id="titleLabel" for="title"><?php echo __("Title");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="1" class="inputbox" id="title" name="title" value="<?php $this->_($this->data['title']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="startDateLabel" for="startDate"><?php echo __("Start Date");?>:</label>
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
                            <label id="companyIDLabel" for="companyID"><?php echo __("Company");?>:</label>
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
                            <label id="durationLabel" for="duration"><?php echo __("Duration");?>:</label>
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
                                <input type="radio" name="typeCompany" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' checked'); ?> id="defaultCompany" onchange="if(document.getElementById('companyName').disabled == false && document.getElementById('companyID').value > 0) {oldCompanyID = document.getElementById('companyID').value; } else if(document.getElementById('companyName').disabled == false) { oldCompanyID = 0; } document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; ">&nbsp;<?php echo __("Internal Posting");?>
                            <?php endif; ?>
                            <script type="text/javascript">oldCompanyID = -1; watchCompanyIDChangeJO('<?php echo($this->sessionCookie); ?>');</script>
                         </td>


                        <td class="tdVertical">
                            <label id="maxRateLabel" for="maxRate"><?php echo __("Maximum Rate");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="13" class="inputbox" id="maxRate" name="maxRate" value="<?php $this->_($this->data['maxRate']); ?>" style="width: 150px;" />
                        </td>

                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="departmentLabel" for="department"><?php echo __("Department");?>:</label>
                        </td>
                        <td class="tdData">
                            <select id="departmentSelect" name="department" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                <?php if ($this->data['departmentID'] == 0): ?>
                                    <option value="(none)" selected="selected"><?php echo __("None");?></option>
                                <?php else: ?>
                                    <option value="(none)">None</option>
                                <?php endif; ?>
                                <?php foreach ($this->departmentsRS as $index => $department): ?>
                                    <option value="<?php $this->_($department['name']); ?>" <?php if ($department['name'] == $this->data['department']): ?>selected<?php endif; ?>><?php $this->_($department['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php $this->_($this->departmentsString); ?>" />
                        </td>

                        <td class="tdVertical">
                            <label id="salaryLabel" for="salary"><?php echo __("Salary");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="14" class="inputbox" id="salary" name="salary" value="<?php $this->_($this->data['salary']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="contactIDLabel" for="contactID"><?php echo __("Contact");?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="3" id="contactID" name="contactID" class="inputbox" style="width: 150px;">
                                <option value="-1">None</option>

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
                            <label id="typeLabel" for="type"><?php echo __("Type");?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="15" id="type" name="type" class="inputbox" style="width: 150px;">
                                <?php foreach($this->jobTypes as $jobTypeShort => $jobTypeLong): ?>
                                    <option value="<?php echo $jobTypeShort;?>" 
                                            <?php if($this->data['type'] == $jobTypeShort): ?>
                                                selected="selected"
                                            <?php endif; ?>
                                            ><?php echo $jobTypeShort." (".$jobTypeLong.")";?>
                                    </option>
                                <?php endforeach; ?>
                                <?php if(count($this->jobTypes) < 1): ?>
                                    <option value="N/A" selected>N/A (<?php echo __("Not Applicable");?>)</option>
                                <?php endif; ?>
                            </select>&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="cityLabel" for="city"><?php echo __("City of work");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="4" class="inputbox" id="city" name="city" value="<?php $this->_($this->data['city']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="openingsLabel" for="openings"><?php echo __("Total Openings");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="16" class="inputbox" id="openings" name="openings" value="<?php $this->_($this->data['openings']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="stateLabel" for="state"><?php echo __("State");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="5" class="inputbox" id="state" name="state" value="<?php $this->_($this->data['state']); ?>" style="width: 150px;" />&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="openingsAvailableLabel" for="openingsAvailable"><?php echo __("Remaining Openings");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="16" class="inputbox" id="openingsAvailable" name="openingsAvailable" value="<?php $this->_($this->data['openingsAvailable']); ?>" style="width: 150px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="recruiterLabel" for="recruiter"><?php echo __("Recruiter");?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="6" id="recruiter" name="recruiter" class="inputbox" style="width: 150px;">
                                <option value="">(<?php echo __("Select a User");?>)</option>

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
                            <label id="companyJobIDLabel" for="openings"><?php echo __("Company Job ID");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" tabindex="17" class="inputbox" id="companyJobID" name="companyJobID" value="<?php $this->_($this->data['companyJobID']); ?>" style="width: 150px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="ownerLabel" for="owner"><?php echo __("Owner");?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="7" id="owner" name="owner" class="inputbox" style="width: 150px;" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
                                <option value="-1"><?php echo __("None");?></option>

                                <?php foreach ($this->usersRS as $rowNumber => $usersData): ?>
                                    <?php if ($this->data['owner'] == $usersData['userID']): ?>
                                        <option selected value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php else: ?>
                                        <option value="<?php $this->_($usersData['userID']) ?>"><?php $this->_($usersData['lastName']) ?>, <?php $this->_($usersData['firstName']) ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>&nbsp;*
                            <div style="display:none;" id="divOwnershipChange">
                                <input type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?> /> E-Mail new owner of change
                            </div>
                        </td>

                        <td class="tdVertical">
                            <label id="isHotLabel" for="isHot"><?php echo __("Hot");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" tabindex="18" id="isHot" name="isHot"<?php if ($this->data['isHot'] == 1): ?> checked<?php endif; ?> />&nbsp;
                            <img title="<?php echo __("Checking this box indicates that the job order is 'hot', and shows up highlighted throughout the system.");?>" src="images/information.gif" alt="" width="16" height="16" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="statusLabel" for="status"><?php echo __("Status");?>:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="8" id="status" name="status" class="inputbox" style="width: 150px;">
                        <?php
                        $joStatuses = EnumTypeEnum::jobOrderStatus()->enumValues();
                        $selectedValue = JobOrderStatusEnum::active()->dbValue;
                        foreach($joStatuses as $k =>$jos){
                        	$selected = ($selectedValue==$jos->dbValue);
                        	?>
                        		<option <?php if ($selected): ?>selected<?php endif; ?> value="<?php echo $jos->dbValue;?>"><?php echo $jos->desc;?></option>
                            <?php } //foreach($joStatuses ?>    
                            </select>&nbsp;*
                        </td>

                        <td class="tdVertical">
                            <label id="publicLabel" for="public"><?php echo __("Public");?>:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" tabindex="19" id="public" name="public" onchange="checkPublic(this);" onclick="checkPublic(this);" onkeydown="checkPublic(this);"<?php if ($this->data['public'] == 1): ?> checked<?php endif; ?> />&nbsp;
                            <img title="<?php echo __("Checking this box indicates that the job order is public. Job orders flaged as public will be able to be viewed by anonymous users.");?>" src="images/information.gif" alt="" width="16" height="16" />
                        </td>
                    </tr>

                    <?php eval(Hooks::get('JO_TEMPLATE_BOTTOM_OF_TOP')); ?>

                </table>                  	                                 

                <table class="editTable" width="<?php echo $editWidth;?>">



                    <tr>
                        <td class="tdVertical">
                            <label id="descriptionLabel" for="description"><?php echo __("Description");?>:</label>
                        </td>
                        <td class="tdData">
                            <textarea tabindex="20" class="ckEditor" name="description" id="description" rows="15" style="width: 500px;"><?php $this->_($this->data['description']); ?></textarea>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes"><?php echo __("Internal Notes");?>:</label>
                        </td>
                        <td class="tdData">
                            <textarea tabindex="21" class="ckEditor" name="notes" id="notes" rows="5" style="width: 500px;"><?php $this->_($this->data['notes']); ?></textarea>
                        </td>
                    </tr>

                    <tr id="displayQuestionnaires" style="<?php if ($this->isPublic): ?>display: table-row;<?php else: ?>display: none;<?php endif; ?>">
                        <?php if ($this->careerPortalEnabled): ?>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes"><?php echo __("Questionnaire");?>:</label>
                        </td>
                        <td class="tdData">
                            <select id="questionnaire" name="questionnaire" class="inputbox" style="width: 500px;">
                            <option value="none"><?php echo __("None");?></option>
                            <?php foreach ($this->questionnaires as $questionnaire): ?>
                                <option value="<?php echo $questionnaire['questionnaireID']; ?>"<?php if ($this->questionnaireID == $questionnaire['questionnaireID']) echo ' selected'; ?>><?php echo $questionnaire['title']; ?></option>
                            <?php endforeach; ?>
                            </select>
                            <?php if ($this->getUserAccessLevel('settings.careerPortalSettings') >= ACCESS_LEVEL_SA): ?>
                            <br />
                            <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalSettings" target="_blank">Add / Edit / Delete Questionnaires</a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    
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
                    
                </table>
                
                <p class="note"><?php echo __("Range and conitions of service");?></p>
                    <table class="editTable" width="<?php echo $editWidth;?>">
                        <tr>
                        	<td width="50%" valign="top">                   	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'rangeAndConditions1',
                    			'template'=>'edit',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                        	
                        	</td>
                        	<td width="50%" valign="top">
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'rangeAndConditions2',
                    			'template'=>'edit',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                        	
                        	</td>
                        </tr>
                    </table>  
                    <p class="note"><?php echo __("Requirements");?></p>
                     <table class="detailsOutside" width="<?php echo $editWidth;?>">
                        <tr>
                        	<td width="50%" valign="top"> 
                        	                  	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'requirements1',
                    			'template'=>'edit',
                    			'fl'=>$this->fl,
                    			)); 
                    	?> 
                        	</td>
                        	<td width="50%" valign="top">
                     	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'requirements2',
                    			'template'=>'edit',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                        	
                        	</td>                        	
                        </tr>
                    </table>                  
                
                <?php tplActions($this);?>
                
            </form>

            <script type="text/javascript">
                CKEDITOR.replace( 'description' );
                CKEDITOR.on('instanceReady', function(ev)
                {
                    var tags = ['p', 'ol', 'ul', 'li']; // etc.

                    for (var key in tags) {
                        ev.editor.dataProcessor.writer.setRules(
                            tags[key],
                            {
                                indent : false,
                                breakBeforeOpen : false,
                                breakAfterOpen : false,
                                breakBeforeClose : false,
                                breakAfterClose : false, 
                            });
                    }
                });
            </script>

            <script type="text/javascript">
                document.editJobOrderForm.title.focus();
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
