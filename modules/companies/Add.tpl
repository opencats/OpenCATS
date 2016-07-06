<?php /* $Id: Add.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printHeader('Companies', array('modules/companies/validator.js', 'js/sweetTitles.js', 'js/listEditor.js',  'js/addressParser.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/companies.gif" width="24" height="24" border="0" alt="Companies" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Companies: Add Company</h2></td>
                </tr>
            </table>

            <form name="addCompanyForm" id="addCompanyForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=add" method="post" onsubmit="return checkAddForm(document.addCompanyForm);" autocomplete="off">
                <input type="hidden" name="postback" id="postback" value="postback" />

                <p class="noteUnsized">Basic Information</p>
                <table class="editTable" width="100%">
                    <tr>
                        <td class="tdVertical">
                            <label id="nameLabel" for="name">Company Name:</label>
                        </td>
                        <td class="tdData" width="250">
                            <input type="text" name="name" id="name" class="inputbox" style="width: 150px" />&nbsp;*
                        </td>
                        <td rowspan="5" align="left" valign="top">
                            <?php $freeformTop = '<p class="freeformtop">Cut and paste freeform address here.</p>'; ?>
                            <?php eval(Hooks::get('CANDIDATE_TEMPLATE_ABOVE_FREEFORM')); ?>
                            <?php echo($freeformTop); ?>

                            <textarea class="inputbox" tabindex="90" name="addressBlock" id="addressBlock" rows="5" cols="40" style="width: 300px; height: 100px;"></textarea>

                            <?php $freeformBottom = '<p class="freeformbottom">Cut and paste freeform address here.</p>'; ?>
                            <?php eval(Hooks::get('CANDIDATE_TEMPLATE_BELOW_FREEFORM')); ?>
                            <?php echo($freeformBottom); ?>
                        </td>
                        <td width="200"></td>
                    </tr>
                    
                    <tr>
                        <td class="tdVertical">
                            <label id="phone1Label" for="phone1">Primary Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="phone1" id="phone1" class="inputbox" style="width: 150px" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="phone2Label" for="phone2">Secondary Phone:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="phone2" id="phone2" class="inputbox" style="width: 150px" />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="arrowButton" tabindex="91" align="middle" type="button" value="&lt;--" class="arrowbutton" onclick="AddressParser_parse('addressBlock', 'company', 'addressParserIndicator', 'arrowButton'); document.addCompanyForm.name.focus();" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="faxNumberLabel" for="faxNumber">Fax Number:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="faxNumber" id="faxNumber" class="inputbox" style="width: 150px" />
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<img src="images/indicator2.gif" id="addressParserIndicator" alt="" style="visibility: hidden; margin-left: 10px;" height="16" width="16" />
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

                    <tr>
                        <td class="tdVertical">
                            <label id="urlLabel" for="url">Web Site:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" name="url" id="url" class="inputbox" style="width: 150px" />
                        </td>
                    </tr>
                
                    <tr>
                        <td class="tdVertical">
                            <label id="departmentsLabel" for="departmentsSelect">Departments:</label>
                        </td>
                        <td class="tdData">
                            <select tabindex="3" id="departmentsSelect" name="departmentsSelect" class="inputbox" style="width: 150px;" onchange="if (this.value == 'edit') { listEditor('Departments', 'departmentsSelect', 'departmentsCSV'); } this.value = 'num';">
                                <option value="edit">(Edit Departments)</option>
                                <option value="num" selected="selected">No Departments</option>
                                <option value="nullline">-------------------------------</option>
                            </select>
                            <input type="hidden" id="departmentsCSV" name="departmentsCSV" value="" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="isHotLabel" for="isHot">Hot Company:</label>
                        </td>
                        <td class="tdData">
                            <input type="checkbox" id="isHot" name="isHot" />&nbsp;
                        </td>
                    </tr>
                </table>

                <p class="noteUnsized" style="margin-top: 5px;">Other</p>

                <table class="editTable" width="100%">
                    
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
                            <label id="keyTechnologiesLabel" for="keyTechnologies">Key Technologies:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="keyTechnologies" id="keyTechnologies" style="width: 400px;" />
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td>
                            <textarea class="inputbox" name="notes" id="notes" rows="5" cols="40" style="width: 400px;"></textarea>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button" value="Add Company" />&nbsp;
                <input type="reset"  class="button" value="Reset" />&nbsp;
                <input type="button" class="button" value="Back to Companies" onclick="javascript:goToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show');" />
            </form>

            <script type="text/javascript">
                document.addCompanyForm.name.focus();
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
