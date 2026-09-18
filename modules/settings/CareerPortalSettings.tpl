<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/Settings.js', 'js/careerportal.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<?php $careerPortalEnabledId = 0; ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Career Portal Settings</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalSettings" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="configured" value="1" />

                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        Enable Public Career Portal:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="enabled"<?php if ($this->careerPortalSettingsRS['enabled'] == '1'): ?> checked<?php endif; ?> onclick="document.getElementById('careerPortalSettingsForm').submit();" class="form-check-input">
                                    </div>
                                </div>

                                <div class="row g-2 mb-2" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <div class="col-sm-4 col-lg-3">
                                        Allow Browsing of All Public Job Orders:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="allowBrowse"<?php if ($this->careerPortalSettingsRS['allowBrowse'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>

                                <div class="row g-2 mb-2" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <div class="col-sm-4 col-lg-3">
                                        Allow candidates to register and update their contact information
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="candidateRegistration"<?php if ($this->careerPortalSettingsRS['candidateRegistration'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>

                                <div class="row g-2 mb-2" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <div class="col-sm-4 col-lg-3">
                                        Show Company Column in Job Order List:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="showCompany"<?php if ($this->careerPortalSettingsRS['showCompany'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <div class="col-sm-4 col-lg-3">
                                        Show Department Column in Job Order List:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="showDepartment"<?php if ($this->careerPortalSettingsRS['showDepartment'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>
                                <?php eval(Hooks::get('CAREER_PORTAL_SUBMIT_XML_FEEDS')); ?>
                                <div class="row g-2 mb-2" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                                    <div class="col-sm-4 col-lg-3">
                                        Career Portal URL:
                                    </div>
                                    <div class="col-12 col-sm">
                                        <a href="<?php $this->_($this->careerPortalURL); ?>"><?php $this->_($this->careerPortalURL); ?></a>
                                    </div>
                                </div>
                            </div>
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
                            <input type="submit" value="Save Settings" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>"  class="btn btn-sm btn-primary" />&nbsp;
                            <br />
                            <br />
                        </form>
                    </div>
                </div>
            </div>

            <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Questionnaires</p>

                <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaireUpdate" name="questionnaireUpdateForm">

                <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                    Build a questionnaire to provide to candidates before they apply. You can specify actions
                    to perform based on their responses.
                    <br /><br />

                    <?php if (isset($this->questionnaires) && !empty($this->questionnaires)): ?>
                        <div class="table-responsive"><table class="table table-sm table-striped align-middle">
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Description</th>
                            <th scope="col">Status</th>
                            <th scope="col">Remove</th>
                        </tr>
                        <?php $highlight = 0; ?>
                        <?php for ($i = 0; $i < count($this->questionnaires); $i++): ?>
                            <?php $questionnaire = $this->questionnaires[$i]; ?>
                            <?php $col = ($highlight = !$highlight) ? 'f0f0f0' : 'ffffff'; ?>
                            <tr>
                                <td>
                                    <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire&questionnaireID=<?php echo $questionnaire['questionnaireID']; ?>">
                                    <?php echo $questionnaire['title']; ?>
                                    </a>
                                </td>
                                <td><?php echo $questionnaire['description']; ?></td>
                                <td><?php echo $questionnaire['isActive'] ? 'Active' : 'Inactive'; ?></td>
                                <td><input type="checkbox" name="removeQuestionnaire<?php echo $i; ?>" value="yes"  class="form-check-input" /> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                            </tr>
                        <?php endfor; ?>
                        </table></div>
                    <?php else: ?>
                        <span style="color: ##00008b;">You have no questionnaires. Click <b>Add Questionnaire</b> to create one.</span><br />
                    <?php endif; ?>

                    <br />
                    <div class="card card-body p-2 mb-2">
                        <div class="row g-2 mb-2">
                            <div class="col-12 col-sm">
                                <input type="button" value="Add Questionnaire" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire';"  class="btn btn-sm btn-outline-secondary" />
                            </div>
                            <div class="col-12 col-sm">
                                <input type="submit" value="Update"  class="btn btn-sm btn-primary" />
                            </div>
                        </div>
                    </div>
                </div>

                </form>

            </div>

            <br /><br />
            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold" id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">Templates</p>

            <div id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>">
                You can choose a style for your Career Portal by clicking a template below and pressing "Set as Active".<br />
                <br />
                You may also duplicate an existing template to make a custom template, allowing you make the Career Portal
                closer match the look and feel of your corporate web page.  Customizing a template requires basic knowledge
                of HTML and CSS.<br />
                <br />
            </div>

            <section id="careerPortalEnabled<?php echo ++$careerPortalEnabledId; ?>" class="mb-2">
                <div class="row g-2">
                    <div class="col-12 col-lg-4">
                        <div class="card card-body p-2"><div class="row g-2 mb-2">
                                            <div class="col-12 col-sm">
                                                Built in Templates:
                                            </div>
                                            <div class="col-12 col-sm">
                                                <?php foreach ($this->careerPortalTemplateNames as $name => $data): ?>
                                                    <a href="javascript:void(0);" onclick="setModifyingJobDefault('<?php echo($data['careerPortalName']); ?>','<?php echo(CATSUtility::getIndexName()); ?>?m=careers&amp;templateName=<?php echo(urlencode($data['careerPortalName'])); ?>');" >
                                                        <?php $this->_($data['careerPortalName']); ?>
                                                        <?php if($data['careerPortalName'] == $this->careerPortalSettingsRS['activeBoard']): ?>&nbsp;(Active)<?php endif; ?>
                                                        <br />
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-12 col-sm">
                                                Custom Templates:<br /><br />
                                                <input type="button" value="New" onclick="showNewInput();"  class="btn btn-sm btn-outline-secondary" />
                                            </div>
                                            <div class="col-12 col-sm">
                                                <div id="confirmNew" style="display: none;">
                                                    <form name="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak" method="post" onsubmit="if (detectInputIsValid(document.getElementById('templateName').value)) {alert('This template name is already in use, please use another.'); return false;}" >
                                                        <input name="p" type="hidden" value="new" />
                                                        New Template Name:<br />
                                                        <input name="newName" id="templateName" value="Unnamed"  class="form-control form-control-sm" />&nbsp;
                                                        <input type="submit" value="OK"  class="btn btn-sm btn-primary" />
                                                        <input type="button" value="Cancel" onclick="hideAllEditingFields();"  class="btn btn-sm btn-outline-secondary" />
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
                                            </div>
                                        </div>

                        </div>
                    </div>
                    <div class="col-12 col-lg-8">
                        <div class="card card-body p-2"><span id="textTemplateName" class="h6 fw-semibold"></span>
                                    <div class="d-flex flex-wrap gap-1 mb-2">
                                    <input type="button" value="Full Screen Preview" onclick="fullScreenPreview();"  class="btn btn-sm btn-outline-secondary" />
                                    <input type="button" value="Edit" id="buttonEdit" onclick="window.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=careerPortalTemplateEdit&amp;templateName='+encodeURI(usingID);"  class="btn btn-sm btn-outline-secondary" />
                                    <input type="button" value="Edit" id="buttonEditDefault" onclick="showEditDefaultInput();" style="display: none;"  class="btn btn-sm btn-outline-secondary" />
                                    <input type="button" value="Delete" id="buttonDelete" onclick="showDeleteInput();"  class="btn btn-sm btn-outline-danger" />
                                    <input type="button" value="Duplicate" onclick="showDuplicateInput();"  class="btn btn-sm btn-outline-secondary" />
                                    <input type="button" value="Set as Active" onclick="setAsActive();"  class="btn btn-sm btn-outline-secondary" />
                                    </div>
                                    <form name="setAsActiveForm" id="setAsActiveForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak" method="post">
                                        <input name="p" type="hidden" value="setAsActive" />
                                        <input name="activeName" id="activeName" type="hidden" value="" />
                                        <input type="submit" value="OK" style="display: none;"  class="btn btn-sm btn-primary" />
                                    </form>
                                    <br />
                                    <div id="confirmDuplicate" style="display: none; text-align: left;">
                                        <br />
                                        New Name
                                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak" method="post" onsubmit="if (detectInputIsValid(document.getElementById('duplicateName').value)) {alert('This template name is already in use, please use another.'); return false;}" >
                                            <input name="p" type="hidden" value="duplicate" />
                                            <input name="origName" id="origName" type="hidden" value="" />
                                            <input name="duplicateName" id="duplicateName"  class="form-control form-control-sm" />&nbsp;
                                            <input type="submit" value="OK"  class="btn btn-sm btn-primary" />
                                            <input type="button" value="Cancel" onclick="hideAllEditingFields();"  class="btn btn-sm btn-outline-secondary" />
                                        </form>
                                        <br />
                                    </div>
                                    <div id="confirmDelete" style="display: none;">
                                        <br />
                                        Are you sure you want to delete this template?
                                        <form name="careerPortalSettingsForm" id="careerPortalSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=onCareerPortalTweak" method="post">
                                            <input name="p" type="hidden" value="delete" />
                                            <input name="delName" id="delName" type="hidden" value="">
                                            <input type="submit" value="OK" class="btn btn-sm btn-primary">
                                            <input type="button" value="Cancel" onclick="hideAllEditingFields();" class="btn btn-sm btn-outline-secondary">
                                        </form>
                                        <br />
                                    </div>
                                    <div id="confirmEditDefault" style="display: none;">
                                        <br />
                                        To edit this template, you must first make a duplication of it.
                                        <input type="button" value="Duplicate" onclick="showDuplicateInput();" class="btn btn-sm btn-outline-secondary">
                                        <br />
                                    </div>
                                    <br />
                                    <iframe id="previewBox" class="w-100 border rounded" height="250" title="Career Portal template preview"></iframe>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>

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
