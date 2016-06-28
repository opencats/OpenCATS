<?php /* $Id: CareerPortalQuestionnaire.tpl 3736 2007-11-28 01:19:24Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('js/questionnaire.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>

<style>
div.questionContainer { }
.questionText { font-size: 14px; color: #000000; font-weight: bold; }
td.questionType { }
td.questionTitleText { font-weight: bold; padding: 3px 0 3px 0; font-size: 11px; border-bottom: 1px solid #666666; }
td.questionColumnText { padding: 3px 0 0 0; margin: 0; }
div.actionContainerContents { border: 1px dotted #666666; padding: 10px; background-color: #e0e0e0; display: none; }
div.actionContainerPlainJane { }
#newAnswerContainer { display: none; }
div.answerContainerContents { border: 1px dotted #666666; padding: 10px; background-color: #e0e0e0; display: none; }
div.answerContainerPlainJane { }
#newQuestionContainer { border: 1px dotted #666666; padding: 10px; background-color: #e0e0e0; display: none; }
div.questionContainerContents { border: 1px dotted #666666; padding: 10px; background-color: #e0e0e0; }
div.questionContainerPlainJane { }
</style>

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

            <div style="width: 700px;">
            <p class="note">Careers Website Questionnaire</p>
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

            <table class="editTable" width="700">
                <tr id="fromTitleRow">
                    <td class="tdVertical" style="width: 175px;">
                        <label for="titleLabel" id="titleLabel">Title (Internal):</label>
                    </td>
                    <td class="tdData">
                        <input type="text" tabindex="1" class="inputbox" name="title" id="title" value="<?php echo isset($this->title) ? $this->title : ''; ?>" maxlength="200" style="width: 250px;" />
                    </td>
                    <td class="tdData" align="right" style="text-align: right;">
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td valign="top" align="right"><input type="button" class="button" name="cancelButton" id="cancelButton" value="Cancel, Go Back" onclick="onCancel();" /></td>
                                <td valign="top" align="right"><input type="button" class="button" name="saveButton" id="saveButton" value="<?php echo isset($this->questionnaireID) && $this->questionnaireID != '' ? 'Save Changes' : 'Add Questionnaire'; ?>" onclick="onSave();" /></td>
                            </tr>
                            <tr>
                                <td valign="top" align="right" colspan="2"><input type="button" class="button" name="update" id="update" value="Update" onclick="onUpdate();" /></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr id="fromDescriptionRow">
                    <td class="tdVertical" style="width: 175px;">
                        <label for="descriptionLabel" id="descriptionLabel">Description (Public):</label>
                    </td>
                    <td class="tdData">
                        <input type="text" tabindex="2" class="inputbox" name="description" id="description" value="<?php echo isset($this->description) ? $this->description : ''; ?>" maxlength="200" style="width: 250px;" />
                    </td>
                    <td class="tdData" align="right" style="text-align: right;">
                        &nbsp;
                    </td>
                </tr>
                <tr id="fromActiveRow">
                    <td class="tdVertical" style="width: 175px;">
                        <label for="activeabel" id="activeLabel">Status:</label>
                    </td>
                    <td class="tdData">
                        <select name="isActive" id="isActive" tabindex="3">
                            <option value="yes"<?php echo isset($this->isActive) && $this->isActive ? ' selected' : ''; ?>>Active</option>
                            <option value="no"<?php echo isset($this->isActive) && !$this->isActive ? ' selected' : ''; ?>>In-active</option>
                        </select>
                    </td>
                    <td class="tdData" align="right" style="text-align: right;">
                        &nbsp;
                    </td>
                </tr>

                <tr>
                    <td colspan="3">&nbsp;</td>
                </tr>
                <tr>
                    <td class="tdVertical" style="width: 175px;">
                        <label for="titleLabel" id="titleLabel" style="font-weight: bold;">Questions:</label>
                    </td>
                    <td class="tdData" colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="3">
                        <div id="question1" class="questionContainer">
                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                <!-- Begin Title Bar for the Questions -->
                                <tr>
                                    <td class="questionTitleText" colspan="2">Position</td>
                                    <td class="questionTitleText">Question</td>
                                    <td class="questionTitleText" align="left">Question Type</td>
                                    <td class="questionTitleText">Remove</td>
                                </tr>
                                <!-- End Title Bar for the Questions -->

                                <?php if (isset($this->questions)) for ($questionIndex = 0; $questionIndex < count($this->questions); $questionIndex++ ): ?>
                                    <?php $question = $this->questions[$questionIndex]; ?>
                                    <!-- Begin Question -->
                                    <tr><td colspan="5" style="border-top: 1px solid black; font-size: 4px; padding-bottom: 5px;">&nbsp;</td></tr>
                                    <tr>
                                        <td class="questionColumnText" align="left" valign="top" width="35"><input type="text" size="1" name="question<?php echo $questionIndex; ?>Position" id="question<?php echo $questionIndex; ?>Position" class="inputbox" value="<?php echo $question['questionPosition']; ?>" maxlength="3" onclick="clickPositionBox(this);" style="text-align: center;" /></td>
                                        <td class="questionColumnText" align="left" valign="top" width="25"><img src="images/moveUp.gif" id="question<?php echo $questionIndex; ?>MoveUp" border="0" onmouseover="mouseHoverMoveUp(this, true);" onmouseout="mouseHoverMoveUp(this, false);" onclick="moveUp(this);" /></td>
                                        <td class="questionColumnText" style="padding-right: 10px;" align="left" valign="top">
                                            <div id="question<?php echo $questionIndex; ?>TextContainer" class="questionText">
                                                <div id="question<?php echo $questionIndex; ?>TextLabel">
                                                    <span id="question<?php echo $questionIndex; ?>TextLabelValue"><?php echo $question['questionText']; ?></span>
                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>TextChange" onclick="promptChangeQuestionText(this);">(edit)</a>
                                                </div>
                                                <div id="question<?php echo $questionIndex; ?>TextInput" style="display: none;">
                                                    <input type="text" class="inputbox" id="question<?php echo $questionIndex; ?>TextValue" name="question<?php echo $questionIndex; ?>TextValue" value="<?php echo $question['questionText']; ?>" maxlength="255" style="width: 350px;" />
                                                    <input type="button" class="button" id="question<?php echo $questionIndex; ?>SaveTextValue" value="Save" onclick="saveChangeQuestionText(this, true);" />
                                                </div>
                                            </div>
                                        </td>
                                        <td class="questionColumnText" align="left" valign="top" width="130" align="left">
                                            <div id="question<?php echo $questionIndex; ?>TypeContainer">
                                                <div id="question<?php echo $questionIndex; ?>TypeLabel">
                                                    <span id="question<?php echo $questionIndex; ?>TypeLabelValue"><?php echo $question['questionTypeLabel']; ?></span>
                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>TypeChange" onclick="promptChangeQuestionType(this);">(edit)</a>
                                                </div>
                                                <div id="question<?php echo $questionIndex; ?>TypeInput" style="display: none;">
                                                    <select id="question<?php echo $questionIndex; ?>TypeValue" name="question<?php echo $questionIndex; ?>TypeValue" onchange="saveChangeQuestionType(this, true);">
                                                        <option value="select"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_SELECT) echo ' selected'; ?>>Drop-down List</option>
                                                        <option value="checkbox"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX) echo ' selected'; ?>>Checkboxes</option>
                                                        <option value="radio"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_RADIO) echo ' selected'; ?>>Radio Buttons</option>
                                                        <option value="text"<?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT) echo ' selected'; ?>>Text</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="questionColumnText" valign="top" align="center" width="40"><input type="checkbox" name="question<?php echo $questionIndex; ?>Remove" id="question<?php echo $questionIndex; ?>Remove" value="yes" /></td>
                                    </tr>
                                    <!-- End Question -->

                                    <tr>
                                        <td colspan="2">&nbsp;</td>
                                        <td colspan="2">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <!-- Begin Title Bar for the Answers -->
                                                <tr>
                                                    <td class="questionTitleText" colspan="2">Position</td>
                                                    <td class="questionTitleText">Answer</td>
                                                    <td class="questionTitleText">Remove</td>
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
                                                        <td width="35"><input type="text" size="1" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Position" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Position" class="inputbox" value="<?php echo $answer['answerPosition']; ?>" maxlength="3" onclick="clickPositionBox(this);" style="text-align: center;" /></td>
                                                        <td class="questionColumnText" width="25"><img src="images/moveUp.gif" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>MoveUp" border="0" onmouseover="mouseHoverMoveUp(this, true);" onmouseout="mouseHoverMoveUp(this, false);" onclick="moveUp(this);" /></td>
                                                        <td class="questionColumnText">
                                                            <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextContainer" class="questionText">
                                                                <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextLabel">
                                                                    <span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextLabelValue"><?php echo $answer['answerText']; ?></span>
                                                                    <a href="javascript:void(0);" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextChange" onclick="promptChangeQuestionText(this);">(edit)</a>
                                                                </div>
                                                                <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextInput" style="display: none;">
                                                                    <input type="text" class="inputbox" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextValue" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>TextValue" value="<?php echo $answer['answerText']; ?>" maxlength="255" style="width: 350px;" />
                                                                    <input type="button" class="button" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>SaveTextValue" value="Save" onclick="saveChangeQuestionText(this, true);" />
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="questionColumnText" valign="top" align="center" width="40"><input type="checkbox" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Remove" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>Remove" value="yes" /></td>
                                                    </tr>
                                                    <!-- End Answer -->

                                                    <tr>
                                                        <td colspan="2">&nbsp;</td>
                                                        <td>
                                                            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding-top: 5px;">
                                                                <!-- Begin Actions -->
                                                                <?php if (($actionSource = $answer['actionSource']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSource">Add to <b>source</b>, &quot;<?php echo $actionSource; ?>&quot;.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionSourceDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (($actionNotes = $answer['actionNotes']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotes">Add to <b>notes</b>, &quot;<?php echo $actionNotes; ?>&quot;.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionNotesDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if ($actionIsHot = $answer['actionIsHot']): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHot">Mark the candidate as <span style="color: #800000"><b>hot</b></span>.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsHotDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (!($actionIsActive = $answer['actionIsActive'])): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActive">Mark the candidate as <b>inactive</b> and eliminate from searches.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionIsActiveDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if ($actionCanRelocate = $answer['actionCanRelocate']): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocate">Mark that the candidate is able to relocate.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionCanRelocateDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                                <?php if (($actionKeySkills = $answer['actionKeySkills']) != ''): ?>
                                                                    <?php $actionTaken = true; ?>
                                                                    <tr>
                                                                        <td class="questionColumnText"><span id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkills">Add to <b>key skills</b>, &quot;<?php echo $actionKeySkills; ?>&quot;.</span></td>
                                                                        <td align="right" class="questionColumnText"><a id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>ActionKeySkillsDelete" href="javascript:void(0);" onclick="toggleDeleteAction(this);">(delete)</a></td>
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
                                                                    <td colspan="3" class="questionColumnText" style="padding-top: 0px;">
                                                                        <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>AddAction" class="actionContainerPlainJane">
                                                                            <a href="javascript:void(0);" onclick="addAction(this);" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>AddActionLink">(add action)</a>
                                                                        </div>
                                                                        <div id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>New" class="actionContainerContents">
                                                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                                <tr>
                                                                                    <td class="questionColumnText">
                                                                                        Add Action:
                                                                                        <br />
                                                                                        <select id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewAction" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewAction" onchange="changeNewAction(this);" style="width: 380px; ">
                                                                                            <option value="actionSource">Add text to the candidate source</option>
                                                                                            <option value="actionNotes">Add text to the candidate's notes</option>
                                                                                            <option value="actionIsHot">Mark the candidate as hot</option>
                                                                                            <option value="actionIsActive">Mark the candidate as inactive and eliminate from searches</option>
                                                                                            <option value="actionCanRelocate">Mark that the candidate is able to relocate</option>
                                                                                            <option value="actionKeySkills">Add text to the candidate's key skills</option>
                                                                                        </select>
                                                                                        <p />
                                                                                        <input type="text" class="inputbox" id="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewActionText" name="question<?php echo $questionIndex; ?>Answer<?php echo $answerIndex; ?>NewActionText" maxlength="255" style="width: 380px;" />
                                                                                    </td>
                                                                                </tr>
                                                                            </table>
                                                                            <br />
                                                                            <input type="button" value="Add Action" onclick="submitAction();" />
                                                                            <input type="button" value="Cancel" onclick="cancelAddAction();" />
                                                                        </div>
                                                                    </td>
                                                                </tr>

                                                                <!-- End Actions -->
                                                            </table>
                                                        </td>
                                                    </tr>
                                                <?php endfor; ?>
                                                <tr>
                                                    <td colspan="3" class="questionColumnText" style="padding-top: 15px;">
                                                        <div id="question<?php echo $questionIndex; ?>AddAnswer" class="answerContainerPlainJane">
                                                            <a href="javascript:void(0);" onclick="addAnswer(this);" id="question<?php echo $questionIndex; ?>AddAnswerLink">(add answer)</a>
                                                        </div>
                                                        <div id="question<?php echo $questionIndex; ?>New" class="answerContainerContents">
                                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                                <tr>
                                                                    <td class="questionColumnText">
                                                                        Add Answer:
                                                                        <br />
                                                                        <input type="text" class="inputbox" id="question<?php echo $questionIndex; ?>AnswerText" name="question<?php echo $questionIndex; ?>AnswerText" maxlength="255" style="width: 380px;" />
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                            <br />
                                                            <input type="button" value="Add Answer" onclick="submitAnswer();" />
                                                            <input type="button" value="Cancel" onclick="cancelAddAnswer();" />
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
                                        <div id="newQuestionContainer" class="questionContainerContents">
                                            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                                <tr>
                                                    <td class="questionColumnText">
                                                        Add Question:
                                                        <br />
                                                        <input type="text" id="questionText" name="questionText" style="width: 650px;" maxlength="255" class="inputbox" />
                                                    </td>
                                                </tr>
                                            </table>
                                            <br />
                                            <input type="button" name="addQuestionButton" id="addQuestionButton" value="Add Question" onclick="submitQuestion();" />
                                            <input type="button" name="cancelAddQuestionButton" id="cancelAddQuestionButton" value="Cancel" onclick="cancelAddQuestion();" />
                                        </div>
                                    </td>
                                </tr>

                                <tr>
                                    <td colspan="5" align="right" valign="bottom">
                                        <br /><br />
                                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td align="left" valign="top">
                                                    <input type="button" class="button" name="startOverButton" id="startOverButton" value="Start Over" onclick="onStartOver();" />
                                                </td>
                                                <td align="center">
                                                    <input type="button" class="button" name="update" id="update" value="Update" onclick="onUpdate();" />
                                                </td>
                                                <td align="right" valign="top">
                                                    <input type="button" class="button" name="cancelButton" id="cancelButton" value="Cancel, Go Back" onclick="onCancel();" />
                                                    <input type="button" class="button" name="saveButton" id="saveButton" value="<?php echo isset($this->questionnaireID) && $this->questionnaireID != '' ? 'Save Changes' : 'Add Questionnaire'; ?>" onclick="onSave();" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
            </table>

            </form>
        </div>
    </div>

    <script type="text/javascript">
    restoreScrollPosition();
    </script>
<?php TemplateUtility::printFooter(); ?>
