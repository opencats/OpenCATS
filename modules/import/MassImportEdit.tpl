<?php /* $Id: MassImportEdit.tpl 3781 2007-12-03 21:30:23Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('js/massImport.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<script src='http://resfly.com/js/resumeParserValidation.js' type='text/javascript' language='javascript'></script>
<link rel="stylesheet" type="text/css" href="modules/import/MassImport.css" />
    <div id="main">
        <div id="contents">
            <div style="width: 910px; padding: 20px 5px 0 5px;">
                <div id="mainContainer" style="padding-left: 15px; padding-right: 15px;">
                    <div class="infoBar">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td align="left" valign="middle" class="infoBarText">
                                    Candidate Details
                                </td>
                                <td align="right" valign="middle" class="infoFileText">
                                    &nbsp;
                                </td>
                            </tr>
                        </table>
                    </div>

                    <form method="post" action="?m=import&a=massImportEdit&postback=1&documentID=<?php echo $this->documentID; ?>" name="verifyForm">

                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-top: 10px;">
                        <tr>
                            <td style="font-size: 14px; padding-top: 10px;">
                                <b>* - Fields that are required for this document to be converted into a candidate.</b>
                            </td>
                            <td align="right" valign="bottom">
                                <input type="submit" value="Save ->" style="cursor: pointer;" />
                            </td>
                        </tr>
                    </table>

                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-top: 15px;">
                        <tr>
                            <td align="left" valign="top">
                                <div class="parsedData">
                                    <table cellpadding="0" cellpadding="0">
                                        <tr>
                                            <td class="fieldCell fieldTitle">First Name:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField firstName" id="firstName" name="firstName" value="<?php echo isset($this->document['firstName']) ? $this->document['firstName'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="firstNameCopy" valign="middle" align="left">
                                                            <div id="firstNameCopyBlock" onclick="fieldCopy('firstName');">
                                                                &nbsp;&lt;&lt;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">Last Name: *</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField lastName" id="lastName" name="lastName" value="<?php echo isset($this->document['lastName']) ? $this->document['lastName'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="lastNameCopy" valign="middle" align="left">
                                                            <div id="lastNameCopyBlock" onclick="fieldCopy('lastName');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">Address:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField address" id="address" name="address" value="<?php echo isset($this->document['address']) ? $this->document['address'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="addressCopy" valign="middle" align="left">
                                                            <div id="addressCopyBlock" onclick="fieldCopy('address');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">City:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField city" id="city" name="city" value="<?php echo isset($this->document['city']) ? $this->document['city'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="cityCopy" valign="middle" align="left">
                                                            <div id="cityCopyBlock" onclick="fieldCopy('city');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">State:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField state" id="state" name="state" value="<?php echo isset($this->document['state']) ? $this->document['state'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="stateCopy" valign="middle" align="left">
                                                            <div id="stateCopyBlock" onclick="fieldCopy('state');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">Zip Code:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField zipCode" id="zipCode" name="zipCode" value="<?php echo isset($this->document['zipCode']) ? $this->document['zipCode'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="zipCodeCopy" valign="middle" align="left">
                                                            <div id="zipCodeCopyBlock" onclick="fieldCopy('zipCode');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">E-mail: *</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField email" id="email" name="email" value="<?php echo isset($this->document['email']) ? $this->document['email'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="emailCopy" valign="middle" align="left">
                                                            <div id="emailCopyBlock" onclick="fieldCopy('email');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle">Phone:</td>
                                            <td class="fieldCell">
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <input type="text" class="inputField homePhone" id="homePhone" name="homePhone" value="<?php echo isset($this->document['phone']) ? $this->document['phone'] : ''; ?>" maxlength="30" onchange="validation();" />
                                                        </td>
                                                        <td id="homePhoneCopy" valign="middle" align="left">
                                                            <div id="homePhoneCopyBlock" onclick="fieldCopy('homePhone');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle" colspan="2">
                                                Skills:
                                                <br />
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <textarea name="skills" id="skills" class="largeInputField" maxlength="255" onchange="validation();"><?php echo isset($this->document['skills']) ? trim($this->document['skills']) : ''; ?></textarea>
                                                        </td>
                                                        <td id="skillsCopy" valign="middle" align="left">
                                                            <div id="skillsCopyBlock" onclick="fieldCopy('skills');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle" colspan="2">
                                                Education:
                                                <br />
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <textarea name="education" id="education" class="largeInputField" maxlength="255" onchange="validation();"><?php echo isset($this->document['education']) ? trim($this->document['education']) : ''; ?></textarea>
                                                        </td>
                                                        <td id="educationCopy" valign="middle" align="left">
                                                            <div id="educationCopyBlock" onclick="fieldCopy('education');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fieldCell fieldTitle" colspan="2">
                                                Experience:
                                                <br />
                                                <table cellpadding="0" cellspacing="0" border="0">
                                                    <tr>
                                                        <td valign="middle" align="left" style="padding-right: 5px;">
                                                            <textarea name="experience" id="experience" class="largeInputField" maxlength="255" onchange="validation();"><?php echo isset($this->document['experience']) ? trim($this->document['experience']) : ''; ?></textarea>
                                                        </td>
                                                        <td id="experienceCopy" valign="middle" align="left">
                                                            <div id="experienceCopyBlock" onclick="fieldCopy('experience');">
                                                                &nbsp;
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </td>
                            <td align="left" valign="top" width="600">
                                File: <b><?php echo $this->document['realName']; ?></b> (<?php echo number_format(filesize($this->document['name'])/1024,0); ?>k)
                                <textarea name="document" id="document" class="documentViewer" rows="25" cols="40" onmouseup="documentMouseUp(this);" readonly><?php echo $this->document['contents']; ?></textarea>
                            </td>
                        </tr>
                    </table>

                    <div style="text-align: center; padding: 20px 0 20px 0">
                        <input type="submit" value="Save Changes" style="cursor: pointer;" />
                    </div>

                    </form>
                </div>

                <div id="copyBlockGrey" style="display: none;">
                    <table id="copyBlockGreyTable" cellpadding="0" cellspacing="0" border="0" style="height: 150px; cursor: pointer;">
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyTop-d.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" valign="bottom" style="background: #ffffff url(images/copyBg-d.jpg);">&nbsp;</td></tr>
                        <tr><td align="left" width="17" height="11" valign="bottom"><img src="images/copyArrow-d.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" valign="bottom" style="background: #ffffff url(images/copyBg-d.jpg);">&nbsp;</td></tr>
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyBottom-d.jpg" border="0" /></td></tr>
                    </table>
                </div>

                <div id="copyBlockActive" style="display: none;">
                    <table id="copyBlockActiveTable" cellpadding="0" cellspacing="0" border="0" style="height: 150px; cursor: pointer;">
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyTop.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" valign="bottom" style="background: #ffffff url(images/copyBg.jpg);">&nbsp;</td></tr>
                        <tr><td align="left" width="17" height="11" valign="bottom"><img src="images/copyArrow.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" valign="bottom" style="background: #ffffff url(images/copyBg.jpg);">&nbsp;</td></tr>
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyBottom.jpg" border="0" /></td></tr>
                    </table>
                </div>

                <div id="copyBlockGreyMini" style="display: none;">
                    <table id="copyBlockGreyTableMini" cellpadding="0" cellspacing="0" border="0" style="height: 15px; cursor: pointer;">
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyTop-d.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" height="11" valign="bottom"><img src="images/copyArrow-d.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyBottom-d.jpg" border="0" /></td></tr>
                    </table>
                </div>

                <div id="copyBlockActiveMini" style="display: none;">
                    <table id="copyBlockActiveTableMini" cellpadding="0" cellspacing="0" border="0" style="height: 15px; cursor: pointer;">
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyTop.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" height="11" valign="bottom"><img src="images/copyArrow.jpg" border="0" /></td></tr>
                        <tr><td align="left" width="17" height="2" valign="bottom"><img src="images/copyBottom.jpg" border="0" /></td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
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
