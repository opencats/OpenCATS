<?php TemplateUtility::printHeader('Settings', array('js/questionnaire.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>

<style>
/* Initial visibility is controlled by the existing questionnaire JavaScript. */
.actionContainerContents, .answerContainerContents,
#newAnswerContainer, #newQuestionContainer { display: none; }
</style>

    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <div>
            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Careers Website Questionnaire</p>
            A questionnaire provides questions to candidates before they apply through your careers website.
            You can specify actions to perform based on the responses they give.
            </div>
            <br /><br />

            <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire" name="questionnaireForm" onsubmit="return validateFields();">
            <input type="hidden" name="questionnaireID" value="<?php echo $this->questionnaireID; ?>" />
            <input type="hidden" name="postback" value="1" />
            <input type="hidden" id="restrictAction" name="restrictAction" value="none" />
            <input type="hidden" id="restrictActionQuestionID" name="restrictActionQuestionID" value="" />
            <input type="hidden" id="restrictActionAnswerID" name="restrictActionAnswerID" value="" />
            <input type="hidden" id="saveChanges" name="saveChanges" value="no" />
            <input type="hidden" id="startOver" name="startOver" value="no" />
            <input type="hidden" id="scrollX" name="scrollX" value="<?php echo isset($this->scrollX) ? $this->scrollX : 0; ?>" />
            <input type="hidden" id="scrollY" name="scrollY" value="<?php echo isset($this->scrollY) ? $this->scrollY : 0; ?>" />

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2" id="fromTitleRow">
                    <div class="col-sm-4 col-lg-3">
                        <label for="title" id="titleLabel" class="form-label small mb-0">Title (Internal):</label>
                    </div>
                    <div class="col-12 col-sm">
                        <input type="text" tabindex="1" name="title" id="title" value="<?php echo isset($this->title) ? $this->title : ''; ?>" maxlength="200"  class="form-control form-control-sm" />
                    </div>
                    <div class="col-12 col-sm">
                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm"><input type="button" name="cancelButton" id="cancelButton" value="Cancel, Go Back" onclick="onCancel();"  class="btn btn-sm btn-outline-secondary" /></div>
                                <div class="col-12 col-sm"><input type="button" name="saveButton" id="saveButton" value="<?php echo isset($this->questionnaireID) && $this->questionnaireID != '' ? 'Save Changes' : 'Add Questionnaire'; ?>" onclick="onSave();"  class="btn btn-sm btn-outline-secondary" /></div>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-12"><input type="button" name="update" id="update" value="Update" onclick="onUpdate();"  class="btn btn-sm btn-outline-secondary" /></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row g-2 mb-2" id="fromDescriptionRow">
                    <div class="col-sm-4 col-lg-3">
                        <label for="description" id="descriptionLabel" class="form-label small mb-0">Description (Public):</label>
                    </div>
                    <div class="col-12 col-sm">
                        <input type="text" tabindex="2" name="description" id="description" value="<?php echo isset($this->description) ? $this->description : ''; ?>" maxlength="200"  class="form-control form-control-sm" />
                    </div>
                    <div class="col-12 col-sm">
                        &nbsp;
                    </div>
                </div>
                <div class="row g-2 mb-2" id="fromActiveRow">
                    <div class="col-sm-4 col-lg-3">
                        <label for="isActive" id="activeLabel" class="form-label small mb-0">Status:</label>
                    </div>
                    <div class="col-12 col-sm">
                        <select name="isActive" id="isActive" tabindex="3" class="form-select form-select-sm">
                            <option value="yes"<?php echo isset($this->isActive) && $this->isActive ? ' selected' : ''; ?>>Active</option>
                            <option value="no"<?php echo isset($this->isActive) && !$this->isActive ? ' selected' : ''; ?>>In-active</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm">
                        &nbsp;
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-12">&nbsp;</div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-sm-4 col-lg-3">
                        <label for="titleLabel" id="titleLabel" class="fw-semibold" class="form-label small mb-0">Questions:</label>
                    </div>
                    <div class="col-12">&nbsp;</div>
                </div>
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <div id="question1" class="questionContainer table-responsive">
                            <table class="table table-sm align-middle">
                                <!-- Begin Title Bar for the Questions -->
                                <tr>
                                    <td class="questionTitleText fw-semibold border-bottom" colspan="2">Position</td>
                                    <td class="questionTitleText fw-semibold border-bottom">Question</td>
                                    <td class="questionTitleText fw-semibold border-bottom">Question Type</td>
                                    <td class="questionTitleText fw-semibold border-bottom">Remove</td>
                                </tr>
                                <!-- End Title Bar for the Questions -->

                                <?php if (isset($this->questions)) for ($questionIndex = 0; $questionIndex < count($this->questions); $questionIndex++ ): ?>
                                    <?php $question = $this->questions[$questionIndex]; ?>
                                    <!-- Begin Question -->
                                    <tr><td colspan="5" style="border-top: 1px solid black; font-size: 4px; padding-bottom: 5px;">&nbsp;</td></tr>
                                    <tr>
                                        <td class="questionColumnText py-1"><input type="text" size="1" name="question<?php echo $questionIndex; ?>Position" id="question<?php echo $questionIndex; ?>Position" value="<?php echo $question['questionPosition']; ?>" maxlength="3" onclick="clickPositionBox(this);" style="text-align: center;"  class="form-control form-control-sm" /></td>
                                        <td class="questionColumnText py-1"><img src="images/moveUp.gif" id="question<?php echo $questionIndex; ?>MoveUp" onmouseover="mouseHoverMoveUp(this, true);" onmouseout="mouseHoverMoveUp(this, false);" onclick="moveUp(this);" /></td>
                                        <td class="questionColumnText py-1" style="padding-right: 10px;">
                                            <div id="question<?php echo $questionIndex; ?>TextContainer" class="questionText fw-semibold">
                                                <div id="question<?php echo $questionIndex; ?>TextLabel">
                                                    <span id="question<?php echo $questionIndex; ?>TextLabelValue"><?php echo $question['questionText']; ?></span>
                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>TextChange" onclick="promptChangeQuestionText(this);">(edit)</a>
                                                </div>
                                                <div id="question<?php echo $questionIndex; ?>TextInput" style="display: none;">
                                                    <input type="text" id="question<?php echo $questionIndex; ?>TextValue" name="question<?php echo $questionIndex; ?>TextValue" value="<?php echo $question['questionText']; ?>" maxlength="255"  class="form-control form-control-sm" />
                                                    <input type="button" id="question<?php echo $questionIndex; ?>SaveTextValue" value="Save" onclick="saveChangeQuestionText(this, true);"  class="btn btn-sm btn-outline-secondary" />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="questionColumnText py-1">
                                            <div id="question<?php echo $questionIndex; ?>TypeContainer">
                                                <div id="question<?php echo $questionIndex; ?>TypeLabel">
                                                    <span id="question<?php echo $questionIndex; ?>TypeLabelValue"><?php echo $question['questionTypeLabel']; ?></span>
                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>TypeChange" onclick="promptChangeQuestionType(this);">(edit)</a>
                                                </div>
                                                <div id="question<?php echo $questionIndex; ?>TypeInput" style="display: none;">
                                                    <select id="question<?php echo $questionIndex; ?>TypeValue" name="question<?php echo $questionIndex; ?>TypeValue" onchange="saveChangeQuestionType(this, true);" class="form-select form-select-sm">
                                                        <option value="select"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_SELECT) echo ' selected'; ?>>Drop-down List</option>
                                                        <option value="checkbox"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX) echo ' selected'; ?>>Checkboxes</option>
                                                        <option value="radio"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_RADIO) echo ' selected'; ?>>Radio Buttons</option>
                                                        <option value="text"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT) echo ' selected'; ?>>Text</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="questionColumnText py-1"><input type="checkbox" name="question<?php echo $questionIndex; ?>Remove" id="question<?php echo $questionIndex; ?>Remove" value="yes"  class="form-check-input" /></td>
                                    </tr>
                                    <!-- End Question -->

                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td colspan="2">
                                            <table class="table table-sm align-middle">
                                                <!-- Begin Title Bar for the Answers -->
                                                <tr>
                                                    <td class="questionTitleText fw-semibold border-bottom" colspan="2">Position</td>
                                                    <td class="questionTitleText fw-semibold border-bottom">Answer</td>
                                                    <td class="questionTitleText fw-semibold border-bottom">Remove</td>
                                                </tr>
                                                <!-- End Title Bar for the Answers -->

                                                <?php if (isset($question['answers'])) for ($answerIndex = 0; $answerIndex < count($question['answers']); $answerIndex++ ): ?>
                                                    <?php $answer = $question['answers'][$answerIndex]; ?>
                                                    <?php $actionTaken = false; ?>

                                                    <?php if ($answerIndex): ?>
                                                    <tr>
                                                        <td colspan="3" style="border-bottom: 1px dotted #000000;">&nbsp;</td>
                                                    </tr>
                                                    <?php endif; ?>

                                                    <!-- Begin Answer -->
                                                    <tr>
                                                        <td><input type="text" size="1" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Position" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Position" value="<?php echo $answer['answerPosition']; ?>" maxlength="3" onclick="clickPositionBox(this);" style="text-align: center;"  class="form-control form-control-sm" /></td>
                                                        <td class="questionColumnText py-1"><img src="images/moveUp.gif" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>MoveUp" onmouseover="mouseHoverMoveUp(this, true);" onmouseout="mouseHoverMoveUp(this, false);" onclick="moveUp(this);" /></td>
                                                        <td class="questionColumnText py-1">
                                                            <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextContainer" class="questionText fw-semibold">
                                                                <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextLabel">
                                                                    <span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextLabelValue"><?php echo $answer['answerText']; ?></span>
                                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextChange" onclick="promptChangeQuestionText(this);">(edit)</a>
                                                                </div>
                                                                <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextInput" style="display: none;">
                                                                    <input type="text" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextValue" value="<?php echo $answer['answerText']; ?>" maxlength="255"  class="form-control form-control-sm" />
                                                                    <input type="button" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>SaveTextValue" value="Save" onclick="saveChangeQuestionText(this, true);"  class="btn btn-sm btn-outline-secondary" />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="questionColumnText py-1"><input type="checkbox" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Remove" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Remove" value="yes"  class="form-check-input" /></td>
                                                    </tr>
                                                    <!-- End Answer -->

                                                    <tr>
                                                        <td colspan="2">&nbsp;</td>
                                                        <td>
                                                            <table style="padding-top: 5px;">
                                                                <!-- Begin Actions -->
                                                                <?php if (($actionSource = $answer['actionSource']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSource">Add to <b>source</b>, &quot;<?php echo $actionSource; ?>&quot;.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (($actionNotes = $answer['actionNotes']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotes">Add to <b>notes</b>, &quot;<?php echo $actionNotes; ?>&quot;.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if ($actionIsHot = $answer['actionIsHot']): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHot">Mark the candidate as <span style="color: #800000"><b>hot</b></span>.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (!($actionIsActive = $answer['actionIsActive'])): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActive">Mark the candidate as <b>inactive</b> and eliminate from searches.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if ($actionCanRelocate = $answer['actionCanRelocate']): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocate">Mark that the candidate is able to relocate.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (($actionKeySkills = $answer['actionKeySkills']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText py-1"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkills">Add to <b>key skills</b>, &quot;<?php echo $actionKeySkills; ?>&quot;.</span></td>
                                                                        <td class="questionColumnText py-1"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>

                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceValue" value="<?php echo htmlentities($actionSource); ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesValue" value="<?php echo htmlentities($actionNotes); ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotValue" value="<?php echo $actionIsHot; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveValue" value="<?php echo $actionIsActive ? '1' : '0'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateValue" value="<?php echo $actionCanRelocate; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsValue" value="<?php echo htmlentities($actionKeySkills); ?>" />

                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceActive" value="<?php echo strlen($actionSource) ? 'yes' : 'no'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesActive" value="<?php echo strlen($actionNotes) ? 'yes' : 'no'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotActive" value="<?php echo $actionIsHot ? 'yes' : 'no'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveActive" value="<?php echo !$actionIsActive ? 'yes' : 'no'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateActive" value="<?php echo $actionCanRelocate ? 'yes' : 'no'; ?>" />
                                                                <input type="hidden" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsActive" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsActive" value="<?php echo strlen($actionKeySkills) ? 'yes' : 'no'; ?>" />

                                                                <tr>
                                                                    <td colspan="3" class="questionColumnText py-1" style="padding-top: 0px;">
                                                                        <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>AddAction" class="actionContainerPlainJane">
                                                                            <a href="javascript:void(0);" onclick="addAction(this);" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>AddActionLink">(add action)</a>
                                                                        </div>
                                                                        <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>New" class="actionContainerContents border rounded bg-body-tertiary p-2">
                                                                            <table class="table table-sm align-middle">
                                                                                <tr>
                                                                                    <td class="questionColumnText py-1">
                                                                                        Add Action:
                                                                                        <br />
                                                                                        <select id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewAction" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewAction" onchange="changeNewAction(this);" class="form-select form-select-sm">
                                                                                            <option value="actionSource">Add text to the candidate source</option>
                                                                                            <option value="actionNotes">Add text to the candidate's notes</option>
                                                                                            <option value="actionIsHot">Mark the candidate as hot</option>
                                                                                            <option value="actionIsActive">Mark the candidate as inactive and eliminate from searches</option>
                                                                                            <option value="actionCanRelocate">Mark that the candidate is able to relocate</option>
                                                                                            <option value="actionKeySkills">Add text to the candidate's key skills</option>
                                                                                        </select>
                                                                                        <p />
                                                                                        <input type="text" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewActionText" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewActionText" maxlength="255"  class="form-control form-control-sm" />
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                            <br />
                                                                            <input type="button" value="Add Action" onclick="submitAction();"  class="btn btn-sm btn-outline-secondary" />
                                                                            <input type="button" value="Cancel" onclick="cancelAddAction();"  class="btn btn-sm btn-outline-secondary" />
                                                                        </div>
                                                                    </td>
                                                                </tr>

                                                                <!-- End Actions -->
                                                            </table>
                                                        </td>
                                                    </tr>
                                                <?php endfor; ?>
                                                <tr>
                                                    <td colspan="3" class="questionColumnText py-1" style="padding-top: 15px;">
                                                        <div id="question<?php echo $questionIndex; ?>AddAnswer" class="answerContainerPlainJane">
                                                            <a href="javascript:void(0);" onclick="addAnswer(this);" id="question<?php echo $questionIndex; ?>AddAnswerLink">(add answer)</a>
                                                        </div>
                                                        <div id="question<?php echo $questionIndex; ?>New" class="answerContainerContents border rounded bg-body-tertiary p-2">
                                                            <table class="table table-sm align-middle">
                                                                <tr>
                                                                    <td class="questionColumnText py-1">
                                                                        Add Answer:
                                                                        <br />
                                                                        <input type="text" id="question<?php echo $questionIndex; ?>AnswerText" name="question<?php echo $questionIndex; ?>AnswerText" maxlength="255"  class="form-control form-control-sm" />
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <br />
                                                            <input type="button" value="Add Answer" onclick="submitAnswer();"  class="btn btn-sm btn-outline-secondary" />
                                                            <input type="button" value="Cancel" onclick="cancelAddAnswer();"  class="btn btn-sm btn-outline-secondary" />
                                                        </div>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                <?php endfor; ?>

                                <tr>
                                    <td colspan="5" style="padding-top: 20px;">
                                        <div id="addQuestion" class="questionContainerPlainJane">
                                            <a href="javascript:void(0);" onclick="addQuestion();" id="AddQuestionLink">(add question)</a>
                                        </div>
                                        <div id="newQuestionContainer" class="questionContainerContents border rounded bg-body-tertiary p-2">
                                            <table class="table table-sm align-middle">
                                                <tr>
                                                    <td class="questionColumnText py-1">
                                                        Add Question:
                                                        <br />
                                                        <input type="text" id="questionText" name="questionText" maxlength="255"  class="form-control form-control-sm" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="button" name="addQuestionButton" id="addQuestionButton" value="Add Question" onclick="submitQuestion();"  class="btn btn-sm btn-outline-secondary" />
                                            <input type="button" name="cancelAddQuestionButton" id="cancelAddQuestionButton" value="Cancel" onclick="cancelAddQuestion();"  class="btn btn-sm btn-outline-secondary" />
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="5">
                                        <br /><br />
                                        <table class="table table-sm align-middle">
                                            <tr>
                                                <td>
                                                    <input type="button" name="startOverButton" id="startOverButton" value="Start Over" onclick="onStartOver();"  class="btn btn-sm btn-outline-secondary" />
                                                </td>
                                                <td>
                                                    <input type="button" name="update" id="update" value="Update" onclick="onUpdate();"  class="btn btn-sm btn-outline-secondary" />
                                                </td>
                                                <td>
                                                    <input type="button" name="cancelButton" id="cancelButton" value="Cancel, Go Back" onclick="onCancel();"  class="btn btn-sm btn-outline-secondary" />
                                                    <input type="button" name="saveButton" id="saveButton" value="<?php echo isset($this->questionnaireID) && $this->questionnaireID != '' ? 'Save Changes' : 'Add Questionnaire'; ?>" onclick="onSave();"  class="btn btn-sm btn-outline-secondary" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            </form>
        </div>
    </main>

    <script type="text/javascript">
    restoreScrollPosition();
    </script>
<?php TemplateUtility::printFooter(); ?>
