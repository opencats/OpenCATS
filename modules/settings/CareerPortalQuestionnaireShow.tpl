<?php /* $Id: CareerPortalQuestionnaireShow.tpl 3816 2007-12-06 18:55:00Z andrew $ */ ?>
<?php if (!$this->isModal): ?>
<?php TemplateUtility::printHeader('Settings', array('js/questionnaire.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration: Questionnaire Preview</h2></td>
                </tr>
            </table>
            <br />

            <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnairePreview&questionnaireID=<?php echo $this->questionnaireID; ?>" name="questionnairePreviewForm">
<?php endif; ?>
    <style>
        td.QAquestionText { padding: 10px; }
        td.QAanswerText { padding: 10px; }
        .QAinput { }
        .QAtextarea { width: 400px; height: 75px; }
        .QAtext { border: 1px solid #888888; padding: 5px; }
        .QAtext:hover { border: 1px solid black; padding: 5px; background-color: #f8f8f8; }
        .QAtext:focus { border: 1px solid black; padding: 5px; background-color: #f0f0f0; }
        .QAselect { border: 1px solid #888888; padding: 5px; }
        .QAselect:hover { border: 1px solid black; padding: 5px; background-color: #f8f8f8; }
        td.QAhighlightOn {  }
        td.QAhighlightOff {  }
    </style>

    <div style="padding: 0 15px 0 15px;">

    <span style="font-size: 18px; font-weight: bold;">
    <?php echo $this->data['description']; ?>
    </span>

    <br /><br />

    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding: 5px;">
    <?php $highlightRow = false; ?>
    <?php foreach ($this->questions as $question): ?>
        <?php $highlightRow = !$highlightRow; ?>

        <tr>
            <td class="QAquestionText QAhighlight<?php echo $highlightRow ? 'On' : 'Off'; ?>" width="50%" align="left" valign="top"><?php echo $question['questionText']; ?></td>
            <td class="QAanswerText QAhighlight<?php echo $highlightRow ? 'On' : 'Off'; ?>" width="50%" align="left" valign="top">
                <?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT || empty($question['answers'])): ?>
                    <textarea name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" maxlength="<?php echo $question['maximumLength']; ?>" class="QAtextarea"></textarea>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_RADIO): ?>
                    <?php $nochecked = true; ?>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <div style="padding: 2px 0 2px 0;" class="QAinput">
                        <input type="radio" name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" value="<?php echo $answer['answerID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>"<?php if ($nochecked) { $nochecked = false; echo ' checked'; } ?> /> <?php echo $answer['answerText']; ?>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX): ?>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <div style="padding: 2px 0 2px 0;" class="QAinput">
                        <input type="checkbox" name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>Answer<?php echo $answer['answerID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>Answer<?php echo $answer['answerID']; ?>" value="yes" /> <?php echo $answer['answerText']; ?>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_SELECT): ?>
                    <select name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" class="QASelect">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <option value="<?php echo $answer['answerID']; ?>"><?php echo $answer['answerText']; ?></option>
                    <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </td>
        </tr>

    <?php endforeach; ?>
    </table>

    </div>

<?php if (!$this->isModal): ?>
            </form>
        </div>


    </div>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
