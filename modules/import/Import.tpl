<?php /* $Id: Import.tpl 3370 2007-11-01 16:43:07Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Import" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Import Data</h2></td>
                </tr>
            </table>

            <?php if (isset($this->errorMessage)): ?>

                <p class="warning" id="importHide0">Error!</p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php echo($this->errorMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->successMessage)): ?>

                <p class="note" id="importHide0">Success</p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            <?php echo($this->successMessage); ?>
                        </td>
                    </tr>
                </table>

                <br />

            <?php elseif (isset($this->pendingCommits)): ?>

                <p class="warning" id="importHide0">Notice</p>

                <table class="searchTable" id="importHide1">
                    <tr>
                        <td>
                            You have recently imported CSV data.  You can click here to review or delete the imported data.<br />
                            <input type="button" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=viewpending';" value="View Recent Imports" class="button" />
                        </td>
                    </tr>
                </table>

                <br />


            <?php else: ?>
                <p class="note" id="importHide0">Warning!</p>

                <table class="searchTable" id="importTable1" width="100%">
                    <tr>
                        <td>CATS may discard or fail to read some of the submitted data which it does not
                        understand how to use. Do not discard the original data!
                        </td>
                    </tr>

                </table>

                <br />
            <?php endif; ?>

            <p class="warning" id="importShow0" style="display:none;">PLEASE WAIT!</p>

            <table class="searchTable" id="importShow1" style="display:none;">
                <tr>
                    <td>
                        Please wait! Importing data may take a few minutes.<br />
                        <img src="images/loading.gif" />
                    </td>
                </tr>
            </table>

            <p class="note" id="importHide2">Import Data</p>

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=import&amp;#step2" enctype="multipart/form-data" method="post" autocomplete="off" <?php if (isset($this->contactsUploadNotice) && $this->contactsUploadNotice): ?> onsubmit="return checkField(<?php echo(count($this->theFields)); ?>, 'company_id', 'You must have 1 field set as Company.') && showLoading();"<?php else: ?> onsubmit="return showLoading();"<?php endif; ?>>
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="fileName" name="fileName" value="<?php echo($this->fileName); ?>" />
                <input type="hidden" id="dataContaining" name="dataContaining" value="<?php echo($this->dataContaining) ?>" />
                <input type="hidden" name="importInto" id="importInto" value="<?php echo($this->importInto) ?>" />
                <input type="hidden" name="typeOfImport" value="<?php echo($this->typeOfImport); ?>">
                <input type="hidden" id="dataType" name="dataType" value="<?php echo($this->dataType) ?>" />

                <table class="searchTable" width="740" id="importHide3">

                    <tr>
                        <td class="tdVertical">
                            <label id="dataContainingDisabledLabel" for="dataContainingDisabled">File Format:</label>
                        </td>
                        <td class="tdData">
                            <?php if ($this->dataContaining == 'tab'): ?>
                                Tab Delimited Data
                            <?php elseif ($this->dataContaining == 'csv'): ?>
                                Comma Delimited Data (CSV)
                            <?php endif; ?>

                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="importIntoLabel" for="importIntoDisabled">Import Into:</label>
                        </td>
                        <td class="tdData">
                            <?php if ($this->importInto == 'Candidates'): ?>
                                <img src="images/candidate_inline.gif">&nbsp;Candidates
                            <?php elseif ($this->importInto == 'Companies'): ?>
                                <img src="images/mru/company.gif">&nbsp;Companies
                            <?php elseif ($this->importInto == 'Contacts'): ?>
                                <img src="images/mru/contact.gif">&nbsp;Contacts
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                    <input type="reset"  class="button" name="reset" id="importHide4" value="Reset" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=import';" />&nbsp;

            <br />
            <br />


            <p class="note" id="importHide5"><a name="step2">Map Data</a></p>

            <?php if (isset($this->contactsUploadNotice) && $this->contactsUploadNotice): ?>

            <table class="searchTable" id="importHide6">
                <tr>
                    <td>
                        You are importing data into Contacts. Each contact is required to have an associated
                        Company.<br /><br />

                        If you wish, CATS can create the companies for each company who is not in the database. The
                        company's contact information will be identical to the first contacts contact information.
                        <br /><br />

                        For example, if adding John Smith listed as being associated to Fun Industries, and John
                        Smith is listed as having work address '1234 Fun St.', then CATS can create the company Fun
                        Industries with address '1234 Fun St.'.<br /><br />

                        If you choose not to generate company data, then contacts with unknown companies will not be
                        imported.<br /><br />

                        <span class="bold">Should CATS generate the company data automatically?</span><br />
                        <select id="generateCompanies" name="generateCompanies" class ="inputBox" style="width: 260px;" onchange="evaluateUnnamedContacts();">
                            <option value="yes" selected="selected">Yes, generate assocated company data.</option>
                            <option value="no">No, just import the contacts.</option>
                        </select>
                        <br />
                        <span id='unnamedContactsSpan'>
                        <br />
                        <span class="bold">If there is no name for the companies contact, should CATS name the contact 'nobody' and add it to the company?</span><br />
                        <select id="unnamedContacts" name="unnamedContacts" class ="inputBox" style="width: 260px;">
                            <option value="yes" selected="selected">Yes, add a name as necessary.</option>
                            <option value="no">No, throw out these records.</option>
                        </select>
                        </span>
                    </td>
                </tr>
            </table>
            <br />

            <?php endif; ?>

            <table class="searchTable" id="importHide9">
                <tr>
                    <td>The following fields were detected in your data. Please pick where to put the
                    data from each field, then press import at the bottom.</td>
                </tr>
            </table>

            <br />
            <table class="editTable" width="740" id="importHide10">
                <?php foreach ($this->theFields AS $fieldIndex => $theField): ?>
                    <tr>
                        <td class="tdVertical">
                            <?php echo($theField); ?>
                        </td>
                        <td class="tdData">
                            <?php $match = false; ?>
                            <?php foreach ($this->matchingFields as $matchingField): ?>
                                <?php if (trim(strtolower($theField)) == strtolower($matchingField)): ?>
                                    <?php $match = true; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="javascript:void(0);" onclick="showSampleData(<?php echo($fieldIndex); ?>);" onmouseout="hideSampleData(<?php echo($fieldIndex); ?>);" title="Sample Data">
                                <img src="images/mru/contact.gif" alt="" border="0" />
                            </a>
                            &nbsp;
                            <select id="importType<?php echo($fieldIndex); ?>" name="importType<?php echo($fieldIndex); ?>" class="inputbox" style="width: 230px;" onchange="evaluateFieldSelection(<?php echo($fieldIndex); ?>);">
                                <option value="">Do not import.</option>
                                <option value="cats" <?php if ($match): ?>selected<?php endif; ?>>Import as a <?php echo($this->importInto); ?> field.</option>
                                <?php if ($this->isSA): ?><option value="foreign">Add field to Extra Fields and Import.</option><?php endif; ?>
                            </select>
                            <span <?php if (!$match): ?>style="display:none;"<?php endif; ?> id="importIntoSpan<?php echo($fieldIndex); ?>">
                                &nbsp;<?php echo($this->importInto); ?> Field:&nbsp;
                                <select id="importIntoField<?php echo($fieldIndex); ?>" name="importIntoField<?php echo($fieldIndex); ?>" class="inputbox" style="width: 180px;">
                                    <?php for ($i = 0; $i < count($this->importTypes); $i += 2): ?>
                                        <option value='<?php echo($this->importTypes[$i+1]); ?>' <?php if ($match && strtolower($theField) == strtolower($this->importTypes[$i])): ?>selected<?php endif; ?>><?php echo($this->importTypes[$i]); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </span>
                            <div id="importSample<?php echo($fieldIndex); ?>" style="display:none;">
                                <br /><br />
                                <table class="searchTable">
                                    <tr>
                                        <td>
                                            <span class="bold">Sample Data:</span>

                                            <?php $fieldsDisplayed = 0; ?>
                                            <?php for ($i = 0; $i < 20; $i++): ?>
                                                <?php if (isset($this->arrayOfData[$i][$fieldIndex]) && $this->arrayOfData[$i][$fieldIndex] != '' && $fieldsDisplayed < 5): ?>
                                                    <br />&#39;<?php $this->_($this->arrayOfData[$i][$fieldIndex]); $fieldsDisplayed++;  ?>&#39;
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <?php if ($fieldsDisplayed == 0): ?>
                                                <br /><i>(none)</i>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                                <br />
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <input type="reset"  class="button" name="reset"  id="importHide8"  value="Reset" onclick="for (var i = 0; i < <?php echo(count($this->theFields)); ?>; i++) evaluateFieldSelection(i); " />&nbsp;
            <input type="submit" class="button" name="submit" id="importHide7" value="Next - Import data" />&nbsp;
            </form>
        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
