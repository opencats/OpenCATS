<?php /* $Id: Add.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printHeader('Contacts', array('modules/contacts/validator.js', 'js/company.js', 'js/sweetTitles.js', 'js/listEditor.js',  'js/contact.js', 'js/suggest.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Contacts: Add Contact</h2></td>
                </tr>
            </table>

            <form name="addContactForm" id="addContactForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=add&amp;v=<?php if ($this->selectedCompanyID === false) { echo('-1'); } else { echo($this->selectedCompanyID); } ?>" method="post" onsubmit="return checkAddForm(document.addContactForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />
                <table>
                    <tr>
                        <td width="50%" height="100%" valign="top">
                            <p class="noteUnsized">Basic Information</p>

                            <table class="editTable" width="100%" height="285">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="firstNameLabel" for="firstName">First Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="firstName" id="firstName" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="lastNameLabel" for="lastName">Last Name:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="lastName" id="lastName" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="companyIDLabel" for="companyID">Company:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="hidden" name="companyID" id="companyID" value="<?php if ($this->selectedCompanyID === false) { echo(0); } else { echo($this->selectedCompanyID); } ?>" />
                                        <input type="text" name="companyName" id="companyName" value="<?php if ($this->selectedCompanyID !== false) { $this->_($this->companyRS['name']); } ?>" class="inputbox" style="width: 150px" onFocus="suggestListActivate('getCompanyNames', 'companyName', 'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0, '<?php echo($this->sessionCookie); ?>', 'helpShim');" <?php if ($this->selectedCompanyID !== false) { echo('disabled'); } ?>/>&nbsp;*
                                        <?php if ($this->defaultCompanyID !== false && $this->selectedCompanyID === false): ?>
                                            <input type="checkbox" id="defaultCompany" onchange="if (this.checked){ document.getElementById('companyName').disabled = true; document.getElementById('companyID').value = '<?php echo($this->defaultCompanyID); ?>'; document.getElementById('companyName').value = &quot;<?php $this->_($this->defaultCompanyRS['name']); ?>&quot;; } else { document.getElementById('companyName').disabled = false; }">&nbsp;Internal Contact
                                        <?php endif; ?>
                                        <script type="text/javascript">watchCompanyIDChange('<?php echo($this->sessionCookie); ?>');</script>
                                        <br />
                                        <iframe id="helpShim" src="javascript:void(0);" scrolling="no" frameborder="0" style="position:absolute; display:none;"></iframe>
                                        <div id="CompanyResults" class="ajaxSearchResults"></div>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="titleLabel" for="title">Title:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="title" id="title" class="inputbox" style="width: 150px" />&nbsp;*
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="departmentLabel" for="department">Department:</label>
                                    </td>
                                    <td class="tdData">
                                        <select id="departmentSelect" name="department" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentSelect', 'departmentsCSV', false); this.value = '(none)'; } if (this.value == 'nullline') { this.value = '(none)'; }">
                                            <option value="edit">(Edit Departments)</option>
                                            <option value="nullline">-------------------------------</option>
                                            <option value="(none)" selected="selected">(None)</option>
                                        </select>
                                        <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="" />
                                    </td>
                                </tr>

                                 <tr>
                                    <td class="tdVertical">
                                        <label id="departmentLabel" for="department">Reports to:</label>
                                    </td>
                                    <td class="tdData">
                                        <select id="reportsTo" name="reportsTo" class="inputbox" style="width: 150px;" >
                                            <option value="(none)" selected="selected">(None)</option>
                                            <?php foreach ($this->reportsToRS as $index => $contact): ?>
                                                <option value="<?php $this->_($contact['contactID']); ?>"><?php $this->_($contact['firstName'] . ' ' . $contact['lastName']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        &nbsp; <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicatorReportsTo" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                                    </td>
                                </tr>
                                
                               <tr>
                                    <td class="tdVertical">
                                        <label id="isHotLabel" for="isHot">Hot Contact:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" id="isHot" name="isHot" />&nbsp;
                                    </td>
                                </tr>

                                <?php /* These empty rows force the other rows to group at the top and align with the right-side table. */ ?>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                                <tr><td>&nbsp;</td></tr>
                            </table>
                        </td>

                        <td width="50%" height="100%" valign="top">
                            <p class="noteUnsized">Contact Information</p>

                            <table class="editTable" width="100%" height="285">
                                <tr>
                                    <td class="tdVertical">
                                        <label id="email1Label" for="email1">E-Mail:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="email1" id="email1" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="email2Label" for="email2">2nd E-Mail:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="email2" id="email2" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneWorkLabel" for="phoneWork">Work Phone:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="phoneWork" id="phoneWork" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneCellLabel" for="phoneCell">Cell Phone:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="phoneCell" id="phoneCell" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="phoneOtherLabel" for="phoneOther">Other Phone:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="phoneOther" id="phoneOther" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="addressLabel" for="address">Address:</label>
                                    </td>
                                    <td class="tdData">
                                        <textarea name="address" id="address" class="inputbox" style="width: 150px"></textarea>
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="cityLabel" for="city">City:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="city" id="city" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="stateLabel" for="state">State:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="state" id="state" class="inputbox" style="width: 150px" />
                                    </td>
                                </tr>

                                <tr>
                                    <td class="tdVertical">
                                        <label id="zipLabel" for="zip">Postal Code:</label>
                                    </td>
                                    <td class="tdData">
                                        <input type="text" name="zip" id="zip" class="inputbox" style="width: 150px" />&nbsp;
                                        <input type="button" class="button" onclick="CityState_populate('zip', 'ajaxIndicator');" value="Lookup" />
                                        <img src="images/indicator2.gif" alt="AJAX" id="ajaxIndicator" style="vertical-align: middle; visibility: hidden; margin-left: 5px;" />
                                    </td>
                                </tr>

                            </table>
                        </td>
                    </tr>
                </table>

                <p class="note">Other</p>

                <table class="editTable">
                    
                    <?php for ($i = 0; $i < count($this->extraFieldRS); $i++): ?>
                        <tr>
                            <td class="tdVertical" id="extraFieldTd<?php echo($i); ?>">
                                <label id="extraFieldLbl<?php echo($i); ?>">
                                    <?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:
                                </label>
                            </td>
                            <td class="tdData" id="extraFieldData<?php echo($i); ?>">
                                <?php echo($this->extraFieldRS[$i]['addHTML']); ?>
                            </td>
                        </tr>
                    <?php endfor; ?>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" name="notes" id="notes" rows="5" style="width: 400px;"></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button" value="Add Contact" />&nbsp;
                <input type="reset"  class="button" value="Reset" />&nbsp;
                <input type="button" class="button" value="Back to Contacts" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=listRecent');" />
            </form>

            <script type="text/javascript">
                document.addContactForm.firstName.focus();
                <?php if ($this->selectedCompanyID !== false): ?>
                    ContactDepartments_populate(<?php echo($this->selectedCompanyID); ?>, '<?php echo($this->sessionCookie); ?>');
                <?php endif; ?>
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
