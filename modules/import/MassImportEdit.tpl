<?php TemplateUtility::printHeader('Settings', array('js/massImport.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<link rel="stylesheet" type="text/css" href="<?php echo TemplateUtility::getVersionedAssetURL('modules/import/MassImport.css'); ?>" />
    <main id="main" class="container-fluid py-2">
        <div id="contents">
            <header class="oc-page-header mb-3"><h1 class="h5 fw-semibold mb-0">Candidate Details</h1></header>
            <div class="card card-body p-3">
                <div id="mainContainer">
                    <form method="post" action="?m=import&a=massImportEdit&postback=1&documentID=<?php echo $this->documentID; ?>" name="verifyForm">

                    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                        <p class="mb-0">* - Fields that are required for this document to be converted into a candidate.</p>
                        <input type="submit" value="Save ->" class="btn btn-sm btn-primary" />
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-lg-5">
                            <div class="parsedData">
                            <div class="mb-3">
                                <label class="form-label" for="firstName">First Name:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="firstName" name="firstName" value="<?php echo isset($this->document['firstName']) ? $this->document['firstName'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="firstNameCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="firstNameCopyBlock" onclick="fieldCopy('firstName');" aria-label="Copy selected resume text to First Name"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="lastName">Last Name: *</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="lastName" name="lastName" value="<?php echo isset($this->document['lastName']) ? $this->document['lastName'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="lastNameCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="lastNameCopyBlock" onclick="fieldCopy('lastName');" aria-label="Copy selected resume text to Last Name"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="address">Address:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="address" name="address" value="<?php echo isset($this->document['address']) ? $this->document['address'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="addressCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="addressCopyBlock" onclick="fieldCopy('address');" aria-label="Copy selected resume text to Address"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="city">City:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="city" name="city" value="<?php echo isset($this->document['city']) ? $this->document['city'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="cityCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="cityCopyBlock" onclick="fieldCopy('city');" aria-label="Copy selected resume text to City"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="state">State:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="state" name="state" value="<?php echo isset($this->document['state']) ? $this->document['state'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="stateCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="stateCopyBlock" onclick="fieldCopy('state');" aria-label="Copy selected resume text to State"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="zipCode">Zip Code:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="zipCode" name="zipCode" value="<?php echo isset($this->document['zipCode']) ? $this->document['zipCode'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="zipCodeCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="zipCodeCopyBlock" onclick="fieldCopy('zipCode');" aria-label="Copy selected resume text to Zip Code"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="email">E-mail: *</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="email" name="email" value="<?php echo isset($this->document['email']) ? $this->document['email'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="emailCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="emailCopyBlock" onclick="fieldCopy('email');" aria-label="Copy selected resume text to E-mail"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="homePhone">Phone:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><input type="text" class="form-control form-control-sm" id="homePhone" name="homePhone" value="<?php echo isset($this->document['phone']) ? $this->document['phone'] : ''; ?>" maxlength="30" onchange="validation();" /></div>
                                    <div id="homePhoneCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="homePhoneCopyBlock" onclick="fieldCopy('homePhone');" aria-label="Copy selected resume text to Phone"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="skills">Skills:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><textarea rows="5" name="skills" id="skills" class="form-control form-control-sm" maxlength="255" onchange="validation();"><?php echo isset($this->document['skills']) ? trim($this->document['skills']) : ''; ?></textarea></div>
                                    <div id="skillsCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="skillsCopyBlock" onclick="fieldCopy('skills');" aria-label="Copy selected resume text to Skills"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="education">Education:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><textarea rows="5" name="education" id="education" class="form-control form-control-sm" maxlength="255" onchange="validation();"><?php echo isset($this->document['education']) ? trim($this->document['education']) : ''; ?></textarea></div>
                                    <div id="educationCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="educationCopyBlock" onclick="fieldCopy('education');" aria-label="Copy selected resume text to Education"></button></div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="experience">Experience:</label>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="flex-grow-1"><textarea rows="5" name="experience" id="experience" class="form-control form-control-sm" maxlength="255" onchange="validation();"><?php echo isset($this->document['experience']) ? trim($this->document['experience']) : ''; ?></textarea></div>
                                    <div id="experienceCopy"><button type="button" class="btn btn-sm btn-outline-secondary" id="experienceCopyBlock" onclick="fieldCopy('experience');" aria-label="Copy selected resume text to Experience"></button></div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-12 col-lg-7">
                            <label for="document" class="form-label text-break">File: <b><?php echo $this->document['realName']; ?></b> (<?php echo number_format(filesize($this->document['name'])/1024,0); ?>k)</label>
                            <textarea name="document" id="document" class="documentViewer form-control form-control-sm" rows="25" cols="40" onmouseup="documentMouseUp(this);" readonly><?php echo $this->document['contents']; ?></textarea>
                        </div>
                    </div>
                    <div class="text-end mt-3"><input type="submit" value="Save Changes" class="btn btn-sm btn-primary" /></div>
                    </form>
                </div>

                <div id="copyBlockGrey" style="display: none;"><span class="text-body-secondary" aria-hidden="true">&larr;</span></div>

                <div id="copyBlockActive" style="display: none;"><span class="text-primary" aria-hidden="true">&larr;</span></div>

                <div id="copyBlockGreyMini" style="display: none;"><span class="text-body-secondary" aria-hidden="true">&larr;</span></div>

                <div id="copyBlockActiveMini" style="display: none;"><span class="text-primary" aria-hidden="true">&larr;</span></div>
            </div>
        </div>
    </main>
    <script>
    validation();

    addCopyBlock('firstName', 0);
    addCopyBlock('lastName', 0);
    addCopyBlock('address', 0);
    addCopyBlock('city', 0);
    addCopyBlock('state', 0);
    addCopyBlock('zipCode', 0);
    addCopyBlock('email', 0);
    addCopyBlock('homePhone', 0);

    addCopyBlock('skills', 1);
    addCopyBlock('education', 1);
    addCopyBlock('experience', 1);
    setTimeout('checkCopyBlocks()', 1);
    </script>
<?php TemplateUtility::printFooter(); ?>
