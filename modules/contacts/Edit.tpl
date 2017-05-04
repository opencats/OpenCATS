<?php /* $Id: Edit.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Contacts'), array('modules/contacts/validator.js', 'js/sweetTitles.js', 'js/suggest.js', 'js/listEditor.js',  'js/contact.js', 'js/company.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Contacts");?>: <?php echo __("Edit Contact");?></h2></td>
                </tr>
            </table>

            <form name="editContactForm" id="editContactForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=edit" method="post" onsubmit="return checkEditForm(document.editContactForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" name="contactID" id="contactID" value="<?php echo($this->contactID); ?>" />

                <table>
                    <tr>
                        <td width="50%" height="100%" valign="top">
                            <p class="noteUnsized"><?php echo __("Basic Information");?></p>

                            <table class="editTable" width="100%" height="285">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="firstNameLabel" for="firstName"><?php echo __("First Name");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="firstName" id="firstName" value="<?php $this->_($this->data['firstName']); ?>" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="lastNameLabel" for="lastName"><?php echo __("Last Name");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="lastName" id="lastName" value="<?php $this->_($this->data['lastName']); ?>" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="companyIDLabel" for="companyID"><span id="companyAssociatedLabel" <?php if ($this->data['leftCompany'] != 1): ?> style="display:none;" <?php endif; ?> >Previous </span>Company:</label>
                                    </td>

                                    <td class="tdData">
                                        <input type="hidden" name="companyID" id="companyID" value="<?php $this->_($this->data['companyID']); ?>" />
                                        <input type="text" name="companyName" id="companyName" value="<?php $this->_($this->data['companyName']); ?>" class="inputbox" style="width: 150px" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->defaultCompanyID == $this->data['companyID']) echo('disabled'); ?> />&nbsp;*
                                        <?php if ($this->defaultCompanyID !== false): ?>
                                            <input type="checkbox" id="defaultCompany" onchange="if (this.checked) { document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; document.getElementById('companyName').value = &quot;<?php $this->_($this->defaultCompanyRS['name']); ?>&quot;; } else { document.getElementById('companyName').disabled = false; }"<?php if ($this->defaultCompanyID == $this->data['companyID']) echo(' checked'); ?> />&nbsp;<?php echo __("Internal Contact");?>
                                        <?php endif; ?>
                                        <script type="text/javascript">watchCompanyIDChange('<?php echo($this->sessionCookie); ?>');</script>
                                        <br />
                                        <iframe id="helpShim" src="javascript:void(0);" scrolling="no" frameborder="0" style="position:absolute; display:none;"></iframe>
                                        <div id="CompanyResults" class="ajaxSearchResults"></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="titleLabel" for="title"><?php echo __("Title");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="title" id="title" value="<?php $this->_($this->data['title']); ?>" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="departmentLabel" for="department"><?php echo __("Department");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <select id="departmentSelect" name="department" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                            <option value="edit">(<?php echo __("Edit Departments");?>)</option>
                                            <option value="nullline">-------------------------------</option>
                                            <?php if ($this->data['departmentID'] == 0): ?>
                                                <option value="(none)" selected="selected">(<?php echo __("None");?>)</option>
                                            <?php else: ?>
                                                <option value="(none)">(<?php echo __("None");?>)</option>
                                            <?php endif; ?>
                                            <?php foreach ($this->departmentsRS as $index => $department): ?>
                                                <option value="<?php $this->_($department['name']); ?>" <?php if ($department['name'] == $this->data['department']): ?>selected<?php endif; ?>><?php $this->_($department['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="<?php $this->_($this->departmentsString); ?>" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="departmentLabel" for="department"><?php echo __("Reports to");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <select id="reportsTo" name="reportsTo" class="inputbox" style="width: 150px;" >
                                            <?php if ($this->data['reportsTo'] == -1): ?>
                                                <option value="(none)" selected="selected">(<?php echo __("None");?>)</option>
                                            <?php else: ?>
                                                <option value="(none)">(<?php echo __("None");?>)</option>
                                            <?php endif; ?>
                                            <?php foreach ($this->reportsToRS as $index => $contact): ?>
                                                <?php if ($contact['contactID'] != $this->contactID): ?>
                                                    <option value="<?php $this->_($contact['contactID']); ?>" <?php if ($contact['contactID'] == $this->data['reportsTo']): ?>selected<?php endif; ?>><?php $this->_($contact['firstName'] . ' ' . $contact['lastName']); ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                        &nbsp; <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicatorReportsTo" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="isHotLabel" for="isHot"><?php echo __("Hot Contact");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" id="isHot" name="isHot"<?php if ($this->data['isHotContact'] == 1): ?> checked<?php endif; ?> />&nbsp;
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical"><?php echo __("Left Company");?>:</td>
                                    <td class="tdData">
                                        <input type="checkbox" id="leftCompany" name="leftCompany"<?php if ($this->data['leftCompany'] == 1): ?> checked<?php endif; ?> onclick="if (document.getElementById('leftCompany').checked) document.getElementById('companyAssociatedLabel').style.display=''; else document.getElementById('companyAssociatedLabel').style.display='none';" />&nbsp;
                                    </td>
                                </tr>

                                <?php /* These empty rows force the other rows to group at the top and align with the right-side table. */ ?>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                            </table>
                        </td>

                        <td width="50%" height="100%" valign="top">
                            <p class="noteUnsized"><?php echo __("Contact Information");?></p>

                            <table class="editTable" width="100%" height="285">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="email1Label" for="email1"><?php echo __("E-Mail");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="email1" id="email1" value="<?php $this->_($this->data['email1']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="email2Label" for="email2"><?php echo __("2nd E-Mail");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="email2" id="email2" value="<?php $this->_($this->data['email2']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneWorkLabel" for="phoneWork"><?php echo __("Work Phone");?>:</label>
                                    </td>
                                    <td class="tdData"><input type="text" name="phoneWork" id="phoneWork" value="<?php $this->_($this->data['phoneWork']); ?>" class="inputbox" style="width: 150px" /></td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneCellLabel" for="phoneCell"><?php echo __("Cell Phone");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="phoneCell" id="phoneCell" value="<?php $this->_($this->data['phoneCell']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneOtherLabel" for="phoneOther"><?php echo __("Other Phone");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="phoneOther" id="phoneOther" value="<?php $this->_($this->data['phoneOther']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="addressLabel" for="address"><?php echo __("Address");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <textarea name="address" id="address" class="inputbox" style="width: 150px"><?php $this->_($this->data['address']); ?></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="cityLabel" for="city"><?php echo __("City");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="city" id="city" value="<?php $this->_($this->data['city']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="stateLabel" for="state"><?php echo __("State");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="state" id="state" value="<?php $this->_($this->data['state']); ?>" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="zipLabel" for="zip"><?php echo __("Postal Code");?>:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="zip" id="zip" value="<?php $this->_($this->data['zip']); ?>" class="inputbox" style="width: 150px" />
                                        <input type="button" class="button" onclick="CityState_populate('zip', 'ajaxIndicator');" value="<?php echo __("Lookup");?>" />
                                        <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p class="note"><?php echo __("Other");?></p>

                <table class="editTable">
                    
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
                            <label id="ownerLabel" for="owner"><?php echo __("Owner");?>:</label>
                        </td>
                        <td class="tdData">
                            <select id="owner" name="owner" class="inputbox" style="width: 150px;" <?php if (!$this->emailTemplateDisabled): ?>onchange="document.getElementById('divOwnershipChange').style.display=''; <?php if ($this->canEmail): ?>document.getElementById('checkboxOwnershipChange').checked=true;<?php endif; ?>"<?php endif; ?>>
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
                                <input type="checkbox" name="ownershipChange" id="checkboxOwnershipChange" <?php if (!$this->canEmail): ?>disabled<?php endif; ?>> <?php echo __("E-Mail new owner of change");?>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes"><?php echo __("Misc. Notes");?>:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" name="notes" id="notes" rows="5" style="width: 400px;"><?php $this->_($this->data['notes']); ?></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button" name="submit" id="submit" value="<?php echo __("Save");?>" />&nbsp;
                <input type="reset"  class="button" name="reset"  id="reset"  value="<?php echo __("Reset");?>" />&nbsp;
                <input type="button" class="button" name="back"   id="back"   value="<?php echo __("Back to Details");?>" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->contactID); ?>');" />
            </form>

            <script type="text/javascript">
                document.editContactForm.firstName.focus();
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
