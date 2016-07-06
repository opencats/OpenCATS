<?php /* $Id: CareerPortalSettings.tpl 3806 2007-12-05 00:39:15Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/Settings.js', 'js/careerportal.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php $careerPortalEnabledId = 0; ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Career Portal Settings</p>

            <table width="100%">
                <tr>
                    <td>
                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="configured" value="1" />

                            <table class="editTable" width="100%">
                                <tr>
                                    <td class="tdVertical" style="width: 425px;">
                                        Enable Public Career Portal:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="enabled"<?php if ($this->careerPortalSettingsRS['enabled'] == '1'): ?> checked<?php endif; ?> onclick="document.getElementById('careerPortalSettingsForm').submit();">
                                    </td>
                                </tr>

                                <tr id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <td class="tdVertical">
                                        Allow Browsing of All Public Job Orders:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="allowBrowse"<?php if ($this->careerPortalSettingsRS['allowBrowse'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>

                                <tr id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <td class="tdVertical">
                                        Allow candidates to register and update their contact information
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="candidateRegistration"<?php if ($this->careerPortalSettingsRS['candidateRegistration'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>

                                <tr id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <td class="tdVertical">
                                        Show Company Column in Job Order List:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="showCompany"<?php if ($this->careerPortalSettingsRS['showCompany'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>
                                <tr id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <td class="tdVertical">
                                        Show Department Column in Job Order List:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="showDepartment"<?php if ($this->careerPortalSettingsRS['showDepartment'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>
                                <?php eval(Hooks::get('CAREER_PORTAL_SUBMIT_XML_FEEDS')); ?>
                                <tr id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <td class="tdVertical">
                                        Career Portal URL:
                                    </td>
                                    <td class="tdData">
                                        <a href="<?php $this->_($this->careerPortalURL); ?>"><?php $this->_($this->careerPortalURL); ?></a>
                                    </td>
                                </tr>
                            </table>
                            <script type="text/javascript">
                                function setVisibility(visibility)
                                {
                                    for (var i = 1; i < 50; i++)
                                    {
                                        var obj = document.getElementById('careerPortalEnabled'+i);
                                        if (obj)
                                        {
                                            obj.style.display = visibility;
                                        }
                                        else
                                        {
                                            break;
                                        }
                                    }
                                }
                                /* Returns true if a template name is already in use. */
                                function detectInputIsValid(name)
                                {
                                    <?php foreach ($this->careerPortalTemplateNames as $name => $data): ?>
                                        if (name.toLowerCase() == '<?php echo($data['careerPortalName']); ?>'.toLowerCase()) return true;
                                    <?php endforeach; ?>
                                    <?php foreach ($this->careerPortalTemplateCustomNames as $name => $data): ?>
                                        if (name.toLowerCase() == '<?php echo($data['careerPortalName']); ?>'.toLowerCase()) return true;
                                    <?php endforeach; ?>

                                    return false;
                                }
                                indexURL = '<?php echo(CATSUtility::getIndexName()); ?>';
                                usingID = '<?php $this->_($data['careerPortalName']); ?>';
                            </script>
                            <input type="submit" class="button" value="Save Settings" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>" />&nbsp;
                            <br />
                            <br />
                        </form>
                    </td>
                </tr>
            </table>

            <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                <p class="note">Questionnaires</p>

                <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaireUpdate" name="questionnaireUpdateForm">

                <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                    Build a questionnaire to provide to candidates before they apply. You can specify actions
                    to perform based on their responses.
                    <br /><br />

                    <?php if (isset($this->questionnaires) && !empty($this->questionnaires)): ?>
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border: 1px solid #c0c0c0; padding: 2px;">
                        <tr>
                            <td width="30%" style="font-weight: bold; padding-right: 10px; border-bottom: 1px solid black;">Title</td>
                            <td width="50%" style="font-weight: bold; padding-right: 10px; border-bottom: 1px solid black;">Description</td>
                            <td width="10%" style="font-weight: bold; padding-right: 10px; border-bottom: 1px solid black;">Status</td>
                            <td width="10%" style="font-weight: bold; padding-right: 10px; border-bottom: 1px solid black;">Remove</td>
                        </tr>
                        <?php $highlight = 0; ?>
                        <?php for ($i = 0; $i < count($this->questionnaires); $i++): ?>
                            <?php $questionnaire = $this->questionnaires[$i]; ?>
                            <?php $col = ($highlight = !$highlight) ? 'f0f0f0' : 'ffffff'; ?>
                            <tr>
                                <td style="background-color: #<?php echo $col; ?>;">
                                    <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire&questionnaireID=<?php echo $questionnaire['questionnaireID']; ?>">
                                    <?php echo $questionnaire['title']; ?>
                                    </a>
                                </td>
                                <td style="background-color: #<?php echo $col; ?>;"><?php echo $questionnaire['description']; ?></td>
                                <td style="background-color: #<?php echo $col; ?>;"><?php echo $questionnaire['isActive'] ? 'Active' : 'Inactive'; ?></td>
                                <td style="background-color: #<?php echo $col; ?>;" align="center"><input type="checkbox" name="removeQuestionnaire<?php echo $i; ?>" value="yes" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                        <?php endfor; ?>
                        </table>
                    <?php else: ?>
                        <span style="color: ##00008b;">You have no questionnaires. Click <b>Add Questionnaire</b> to create one.</span><br />
                    <?php endif; ?>

                    <br />
                    <table cellpadding="0" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td align="left">
                                <input type="button" class="button" value="Add Questionnaire" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire';" />
                            </td>
                            <td align="right">
                                <input type="submit" class="button" value="Update" />
                            </td>
                        </tr>
                    </table>
                </div>

                </form>

            </div>

            <br /><br />
            <p class="note" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">Templates</p>

            <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>" style="width:700px;">
                You can choose a style for your Career Portal by clicking a template below and pressing "Set as Active".<br />
                <br />
                You may also duplicate an existing template to make a custom template, allowing you make the Career Portal
                closer match the look and feel of your corporate web page.  Customizing a template requires basic knowledge
                of HTML and CSS.<br />
                <br />
            </div>

            <table id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>" width="100%">
                <tr>
                    <td valign="top">
                        <table class="editTable" width="100%">
                            <tr>
                                <td class="tdVertical" style="width: 350px;" height="330">
                                    <table width="100%">
                                        <tr>
                                            <td valign="top" nowrap="nowrap">
                                                Built in Templates:
                                            </td>
                                            <td valign="top">
                                                <?php foreach ($this->careerPortalTemplateNames as $name => $data): ?>
                                                    <a href="javascript:void(0);" onclick="setModifyingJobDefault('<?php echo($data['careerPortalName']); ?>','<?php echo(CATSUtility::getIndexName()); ?>?m=careers&amp;templateName=<?php echo(urlencode($data['careerPortalName'])); ?>');" >
                                                        <?php $this->_($data['careerPortalName']); ?>
                                                        <?php if($data['careerPortalName'] == $this->careerPortalSettingsRS['activeBoard']): ?>&nbsp;(Active)<?php endif; ?>
                                                        <br />
                                                    </a>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td valign="top" nowrap="nowrap">
                                                Custom Templates:<br /><br />
                                                <input type="button" class="button" value="New" onclick="showNewInput();" />
                                            </td>
                                            <td valign="top" nowrap="nowrap">
                                                <div id="confirmNew" style="display: none;">
                                                    <form name="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak&amp;p=new" method="post" onsubmit="if (detectInputIsValid(document.getElementById('templateName').value)) {alert('This template name is already in use, please use another.'); return false;}" >
                                                        New Template Name:<br />
                                                        <input name="newName" id="templateName" value="Unnamed" style="width: 160px;" />&nbsp;
                                                        <input type="submit" class="button" value="OK" />
                                                        <input type="button" class="button" value="Cancel" onclick="hideAllEditingFields();" />
                                                        <br />
                                                        <br />
                                                    </form>
                                                </div>
                                                <?php foreach ($this->careerPortalTemplateCustomNames as $name => $data): ?>
                                                    <a href="javascript:void(0);" onclick="setModifyingJobCustom('<?php echo($data['careerPortalName']); ?>','<?php echo(CATSUtility::getIndexName()); ?>?m=careers&amp;templateName=<?php echo(urlencode($data['careerPortalName'])); ?>');">
                                                        <?php $this->_($data['careerPortalName']); ?>
                                                        <?php if($data['careerPortalName'] == $this->careerPortalSettingsRS['activeBoard']): ?>&nbsp;(Active)<?php endif; ?>
                                                        <br />
                                                    </a>
                                                <?php endforeach; ?>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>

                    <td class="tdVertical" valign="top" width="100%" height="330" nowrap="nowrap">
                        <table class="editTable" width="100%">
                            <tr>
                                <td valign="top" align="center" nowrap="nowrap">
                                    <span id="textTemplateName" style="font-weight: bold; font-size: 18px;"></span>
                                    <br />
                                    <input type="button" class="button" value="Full Screen Preview" onclick="fullScreenPreview();" />
                                    <input type="button" class="button" value="Edit" id="buttonEdit" onclick="window.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalTemplateEdit&amp;templateName='+encodeURI(usingID);" />
                                    <input type="button" class="button" value="Edit" id="buttonEditDefault" onclick="showEditDefaultInput();" style="display: none;" />
                                    <input type="button" class="button" value="Delete" id="buttonDelete" onclick="showDeleteInput();" />
                                    <input type="button" class="button" value="Duplicate" onclick="showDuplicateInput();" />
                                    <input type="button" class="button" value="Set as Active" onclick="setAsActive();" />
                                    <form name="setAsActiveForm" id="setAsActiveForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak&amp;p=setAsActive" method="post">
                                        <input name="activeName" id="activeName" type="hidden" value="" />
                                        <input type="submit" class="button" value="OK" style="display: none;" />
                                    </form>
                                    <br />
                                    <div id="confirmDuplicate" style="display: none; text-align: left;">
                                        <br />
                                        New Name
                                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak&amp;p=duplicate" method="post" onsubmit="if (detectInputIsValid(document.getElementById('duplicateName').value)) {alert('This template name is already in use, please use another.'); return false;}" >
                                            <input name="origName" id="origName" type="hidden" value="" />
                                            <input name="duplicateName" id="duplicateName" style="width: 200px;" />&nbsp;
                                            <input type="submit" class="button" value="OK" />
                                            <input type="button" class="button" value="Cancel" onclick="hideAllEditingFields();" />
                                        </form>
                                        <br />
                                    </div>
                                    <div id="confirmDelete" style="display: none;">
                                        <br />
                                        Are you sure you want to delete this template?
                                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak&amp;p=delete" method="post">
                                            <input name="delName" id="delName" type="hidden" value="">
                                            <input type="submit" class="button" value="OK">
                                            <input type="button" class="button" value="Cancel" onclick="hideAllEditingFields();">
                                        </form>
                                        <br />
                                    </div>
                                    <div id="confirmEditDefault" style="display: none;">
                                        <br />
                                        To edit this template, you must first make a duplication of it.
                                        <input type="button" class="button" value="Duplicate" onclick="showDuplicateInput();">
                                        <br />
                                    </div>
                                    <br />
                                    <iframe id="previewBox" width="500" height="250"></iframe>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script type="text/javascript">
        setVisibility(<?php if ($this->careerPortalSettingsRS['enabled'] == '1'): ?>''<?php else: ?>'none'<?php endif; ?>);
        <?php if(isset($_GET['templateName'])) $this->careerPortalSettingsRS['activeBoard'] = $_GET['templateName']; ?>
        <?php foreach ($this->careerPortalTemplateCustomNames as $name => $data): ?>
            <?php if($data['careerPortalName'] == $this->careerPortalSettingsRS['activeBoard']): ?>
                setModifyingJobCustom('<?php echo($data['careerPortalName']); ?>','<?php echo(CATSUtility::getIndexName()); ?>?m=careers&amp;templateName=<?php echo(urlencode($data['careerPortalName'])); ?>');
            <?php endif; ?>
        <?php endforeach; ?>
        <?php foreach ($this->careerPortalTemplateNames as $name => $data): ?>
            <?php if($data['careerPortalName'] == $this->careerPortalSettingsRS['activeBoard']): ?>
                setModifyingJobDefault('<?php echo($data['careerPortalName']); ?>','<?php echo(CATSUtility::getIndexName()); ?>?m=careers&amp;templateName=<?php echo(urlencode($data['careerPortalName'])); ?>');
            <?php endif; ?>
        <?php endforeach; ?>
    </script>
<?php TemplateUtility::printFooter(); ?>
