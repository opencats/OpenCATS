<?php TemplateUtility::printHeader('Import', array('modules/import/import.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
    <main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Import Data</h1></header>

            <?php if (isset($this->errorMessage)): ?>

                <div id="importHide1" class="alert alert-danger" role="alert">
                    <h2 id="importHide0" class="h6">Error!</h2>
                    <?php echo($this->errorMessage); ?>
                </div>

            <?php elseif (isset($this->successMessage)): ?>

                <div id="importHide1" class="alert alert-success" role="status">
                    <h2 id="importHide0" class="h6">Success</h2>
                    <?php echo($this->successMessage); ?>
                </div>

            <?php elseif (isset($this->pendingCommits)): ?>

                <p class="alert alert-warning" role="alert" id="importHide0">Notice</p>

                <div id="importHide1" class="alert alert-info">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md text-break">
                            You have recently imported CSV data.  You can click here to review or delete the imported data.<br />
                            <input type="button" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=viewpending';" value="View Recent Imports" class="btn btn-sm btn-outline-secondary" />
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="importHide0">Warning!</p>

                <div id="importTable1" class="alert alert-warning">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md text-break">CATS may discard or fail to read some of the submitted data which it does not
                        understand how to use. Do not discard the original data!
                        </div>
                    </div>

                </div>

            <?php endif; ?>

            <p class="alert alert-warning" role="alert" id="importShow0" style="display:none;">PLEASE WAIT!</p>

            <div id="importShow1" style="display:none;" class="alert alert-info" role="status">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">
                        Please wait! Importing data may take a few minutes.<br />
                        <span class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden">Loading</span></span>
                    </div>
                </div>
            </div>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="importHide2">Import Data</p>

            <form name="importDataForm" id="importDataForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=import&amp;#step2" enctype="multipart/form-data" method="post" autocomplete="off" <?php if (isset($this->contactsUploadNotice) && $this->contactsUploadNotice): ?> onsubmit="return checkField(<?php echo(count($this->theFields)); ?>, 'company_id', 'You must have 1 field set as Company.') && showLoading();"<?php else: ?> onsubmit="return showLoading();"<?php endif; ?>>
                <input type="hidden" name="postback" id="postback" value="postback" />
                <input type="hidden" id="fileName" name="fileName" value="<?php echo($this->fileName); ?>" />
                <input type="hidden" id="dataContaining" name="dataContaining" value="<?php echo($this->dataContaining) ?>" />
                <input type="hidden" name="importInto" id="importInto" value="<?php echo($this->importInto) ?>" />
                <input type="hidden" name="typeOfImport" value="<?php echo($this->typeOfImport); ?>">
                <input type="hidden" id="dataType" name="dataType" value="<?php echo($this->dataType) ?>" />

                <div id="importHide3" class="card card-body p-2 mb-3">

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span id="dataContainingDisabledLabel">File Format:</span>
                        </div>
                        <div class="col-12 col-md text-break">
                            <?php if ($this->dataContaining == 'tab'): ?>
                                Tab Delimited Data
                            <?php elseif ($this->dataContaining == 'csv'): ?>
                                Comma Delimited Data (CSV)
                            <?php endif; ?>

                        </div>
                    </div>

                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <span id="importIntoLabel">Import Into:</span>
                        </div>
                        <div class="col-12 col-md text-break">
                            <?php if ($this->importInto == 'Candidates'): ?>
                                <img src="images/candidate_inline.gif">&nbsp;Candidates
                            <?php elseif ($this->importInto == 'Companies'): ?>
                                <img src="images/mru/company.gif">&nbsp;Companies
                            <?php elseif ($this->importInto == 'Contacts'): ?>
                                <img src="images/mru/contact.gif">&nbsp;Contacts
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                    <input type="reset"  class="btn btn-sm btn-outline-secondary" name="reset" id="importHide4" value="Reset" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=import&amp;a=import';" />&nbsp;
                <br />

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="importHide5"><span id="step2">Map Data</span></p>

            <?php if (isset($this->contactsUploadNotice) && $this->contactsUploadNotice): ?>

            <div id="importHide6" class="card card-body p-2 mb-3">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">
                        You are importing data into Contacts. Each contact is required to have an associated
                        Company.
                <br />

                        If you wish, CATS can create the companies for each company who is not in the database. The
                        company's contact information will be identical to the first contacts contact information.
                <br />

                        For example, if adding John Smith listed as being associated to Fun Industries, and John
                        Smith is listed as having work address '1234 Fun St.', then CATS can create the company Fun
                        Industries with address '1234 Fun St.'.
                <br />

                        If you choose not to generate company data, then contacts with unknown companies will not be
                        imported.
                <br />

                        <label class="form-label fw-semibold" for="generateCompanies">Should CATS generate the company data automatically?</label><br />
                        <select id="generateCompanies" name="generateCompanies" class="form-select form-select-sm" onchange="evaluateUnnamedContacts();">
                            <option value="yes" selected="selected">Yes, generate assocated company data.</option>
                            <option value="no">No, just import the contacts.</option>
                        </select>
                        <br />
                        <span id='unnamedContactsSpan'>
                        <br />
                        <label class="form-label fw-semibold" for="unnamedContacts">If there is no name for the companies contact, should CATS name the contact 'nobody' and add it to the company?</label><br />
                        <select id="unnamedContacts" name="unnamedContacts" class="form-select form-select-sm">
                            <option value="yes" selected="selected">Yes, add a name as necessary.</option>
                            <option value="no">No, throw out these records.</option>
                        </select>
                        </span>
                    </div>
                </div>
            </div>

            <?php endif; ?>

            <div id="importHide9" class="card card-body p-2 mb-3">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-md text-break">The following fields were detected in your data. Please pick where to put the
                    data from each field, then press import at the bottom.</div>
                </div>
            </div>

            <div id="importHide10" class="card card-body p-2 mb-3">
                <?php foreach ($this->theFields AS $fieldIndex => $theField): ?>
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-md-3 fw-semibold">
                            <label class="form-label" for="importType<?php echo($fieldIndex); ?>"><?php echo($theField); ?></label>
                        </div>
                        <div class="col-12 col-md text-break">
                            <?php $match = false; ?>
                            <?php foreach ($this->matchingFields as $matchingField): ?>
                                <?php if (trim(strtolower($theField)) == strtolower($matchingField)): ?>
                                    <?php $match = true; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <a href="javascript:void(0);" onclick="showSampleData(<?php echo($fieldIndex); ?>);" onmouseout="hideSampleData(<?php echo($fieldIndex); ?>);" title="Sample Data">
                                Show sample data
                            </a>
                            &nbsp;
                            <select id="importType<?php echo($fieldIndex); ?>" name="importType<?php echo($fieldIndex); ?>" class="form-select form-select-sm" onchange="evaluateFieldSelection(<?php echo($fieldIndex); ?>);">
                                <option value="">Do not import.</option>
                                <option value="cats" <?php if ($match): ?>selected<?php endif; ?>>Import as a <?php echo($this->importInto); ?> field.</option>
                                <?php if ($this->isSA): ?><option value="foreign">Add field to Extra Fields and Import.</option><?php endif; ?>
                            </select>
                            <span <?php if (!$match): ?>style="display:none;"<?php endif; ?> id="importIntoSpan<?php echo($fieldIndex); ?>">
                                <label class="form-label mt-2" for="importIntoField<?php echo($fieldIndex); ?>"><?php echo($this->importInto); ?> Field:</label>
                                <select id="importIntoField<?php echo($fieldIndex); ?>" name="importIntoField<?php echo($fieldIndex); ?>" class="form-select form-select-sm">
                                    <?php for ($i = 0; $i < count($this->importTypes); $i += 2): ?>
                                        <option value='<?php echo($this->importTypes[$i+1]); ?>' <?php if ($match && strtolower($theField) == strtolower($this->importTypes[$i])): ?>selected<?php endif; ?>><?php echo($this->importTypes[$i]); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </span>
                            <div id="importSample<?php echo($fieldIndex); ?>" style="display:none;">
                <br />
                                <div class="card card-body p-2 mb-3">
                                    <div class="row g-2 mb-2">
                                        <div class="col-12 col-md text-break">
                                            <span class="fw-semibold">Sample Data:</span>

                                            <?php $fieldsDisplayed = 0; ?>
                                            <?php for ($i = 0; $i < 20; $i++): ?>
                                                <?php if (isset($this->arrayOfData[$i][$fieldIndex]) && $this->arrayOfData[$i][$fieldIndex] != '' && $fieldsDisplayed < 5): ?>
                                                    <br />&#39;<?php $this->_($this->arrayOfData[$i][$fieldIndex]); $fieldsDisplayed++;  ?>&#39;
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                            <?php if ($fieldsDisplayed == 0): ?>
                                                <br /><i>(none)</i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="reset"  class="btn btn-sm btn-outline-secondary" name="reset"  id="importHide8"  value="Reset" onclick="for (var i = 0; i < <?php echo(count($this->theFields)); ?>; i++) evaluateFieldSelection(i); " />&nbsp;
            <input type="submit" class="btn btn-sm btn-primary" name="submit" id="importHide7" value="Next - Import data" />&nbsp;
            </form>
        </div>
    </main>

<?php TemplateUtility::printFooter(); ?>
